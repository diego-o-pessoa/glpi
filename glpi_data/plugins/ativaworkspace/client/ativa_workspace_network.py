"""
Ativa Workspace - rede da maquina (parte do inventario).

IP local, gateway, DNS, MAC, tipo de conexao (cabo/Wi-Fi com rede e sinal),
IP publico, VPN e latencia ate o servidor do GLPI. Nada sensivel. Coletas
caras ficam em cache (o inventario e enviado a cada minuto).
"""

from __future__ import annotations

import json
import re
import socket
import ssl
import statistics
import subprocess
import time
from urllib.parse import urlparse
from urllib.request import HTTPSHandler, build_opener

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)

ADAPTERS_TTL = 5 * 60      # adaptadores/IP/DNS
PUBLIC_IP_TTL = 30 * 60    # IP publico (servico externo)
PUBLIC_IP_URL = "https://api.ipify.org"

# Adaptadores de VPN conhecidos (pela descricao do driver).
VPN_RE = re.compile(r"TAP-Windows|Wintun|OpenVPN|Data Channel Offload|WireGuard|Fortinet|AnyConnect|PANGP|GlobalProtect", re.I)

# Uma chamada so: adaptadores ativos com IPv4, gateway, DNS, MAC e velocidade.
_PS_ADAPTERS = r"""
$ErrorActionPreference = 'SilentlyContinue'
@(Get-NetIPConfiguration | Where-Object { $_.NetAdapter.Status -eq 'Up' } | ForEach-Object {
  [pscustomobject]@{
    name    = [string]$_.InterfaceAlias
    desc    = [string]$_.InterfaceDescription
    mac     = [string]$_.NetAdapter.MacAddress
    speed   = [string]$_.NetAdapter.LinkSpeed
    media   = [string]$_.NetAdapter.PhysicalMediaType
    ipv4    = @($_.IPv4Address | ForEach-Object { [string]$_.IPAddress })
    gateway = @($_.IPv4DefaultGateway | ForEach-Object { [string]$_.NextHop })
    dns     = @($_.DNSServer | Where-Object { $_.AddressFamily -eq 2 } | ForEach-Object { $_.ServerAddresses })
  }
}) | ConvertTo-Json -Depth 4 -Compress
"""

_cache: dict[str, tuple[float, object]] = {}


def _cached(key: str, ttl: int, fn):
    now = time.time()
    hit = _cache.get(key)
    if hit and now - hit[0] < ttl:
        return hit[1]
    value = fn()
    _cache[key] = (now, value)
    return value


def _adapters() -> list[dict]:
    completed = subprocess.run(
        ["powershell", "-NoProfile", "-NonInteractive", "-ExecutionPolicy", "Bypass", "-Command", _PS_ADAPTERS],
        capture_output=True, text=True, timeout=60, creationflags=NO_WINDOW, errors="replace",
    )
    raw = (completed.stdout or "").strip()
    if not raw:
        return []
    data = json.loads(raw)
    items = data if isinstance(data, list) else [data]
    adapters = []
    for item in items:
        if not isinstance(item, dict):
            continue
        desc = str(item.get("desc") or "")
        media = str(item.get("media") or "")
        kind = "vpn" if VPN_RE.search(desc) else ("wifi" if "802.11" in media or "wi-fi" in desc.lower() or "wireless" in desc.lower() else "ethernet")
        listify = lambda v: [str(x) for x in (v if isinstance(v, list) else [v]) if x]  # noqa: E731
        adapters.append({
            "name": str(item.get("name") or "")[:80],
            "description": desc[:120],
            "type": kind,
            "mac": str(item.get("mac") or "").replace("-", ":")[:17],
            "speed": str(item.get("speed") or "")[:20],
            "ipv4": listify(item.get("ipv4"))[:4],
            "gateway": listify(item.get("gateway"))[:2],
            "dns": listify(item.get("dns"))[:4],
        })
    return adapters


def _wifi() -> dict:
    """Rede (SSID) e sinal do Wi-Fi conectado. Rotulos variam por idioma: usa o SSID e o '%'."""
    completed = subprocess.run(["netsh", "wlan", "show", "interfaces"], capture_output=True, text=True,
                               timeout=20, creationflags=NO_WINDOW, errors="replace")
    ssid, signal = "", None
    for line in (completed.stdout or "").splitlines():
        key, sep, value = line.partition(":")
        if not sep:
            continue
        key, value = key.strip(), value.strip()
        if key == "SSID" and not ssid:
            ssid = value[:64]
        elif signal is None and re.fullmatch(r"\d{1,3}\s*%", value):
            signal = int(value.rstrip("% "))
    return {"ssid": ssid, "signal": signal} if ssid else {}


def _public_ip() -> str:
    """Sem internet o resultado vazio tambem fica em cache (nao tenta a cada minuto)."""
    try:
        opener = build_opener(HTTPSHandler(context=ssl.create_default_context()))
        with opener.open(PUBLIC_IP_URL, timeout=6) as response:
            value = response.read(64).decode("ascii", errors="ignore").strip()
    except OSError:
        return ""
    return value if re.fullmatch(r"[0-9a-fA-F:.]{3,45}", value) else ""


def _latency_ms(server_url: str) -> int | None:
    """Tempo (mediana de 3) para abrir conexao TCP com o servidor do GLPI."""
    parsed = urlparse(server_url)
    host = parsed.hostname
    if not host:
        return None
    port = parsed.port or (443 if parsed.scheme == "https" else 80)
    samples = []
    for _ in range(3):
        start = time.perf_counter()
        try:
            with socket.create_connection((host, port), timeout=3):
                samples.append((time.perf_counter() - start) * 1000)
        except OSError:
            continue
    return round(statistics.median(samples)) if samples else None


def collect(server_url: str = "", vpn_profiles=None) -> dict:
    """Bloco "network" do inventario. Cada parte falha isolada."""
    def safe(fn, default):
        try:
            return fn()
        except Exception:  # noqa: BLE001
            return default

    adapters = safe(lambda: _cached("adapters", ADAPTERS_TTL, _adapters), [])
    vpn_adapters = [a for a in adapters if a["type"] == "vpn" and a["ipv4"]]
    # Principal: o que tem gateway e nao e VPN (Wi-Fi ou cabo da maquina).
    primary = next((a for a in adapters if a["type"] != "vpn" and a["gateway"]), None) \
        or next((a for a in adapters if a["type"] != "vpn" and a["ipv4"]), None)

    wifi = {}
    if primary and primary["type"] == "wifi":
        wifi = safe(lambda: _cached("wifi", 60, _wifi), {})

    profiles = safe(vpn_profiles, []) if callable(vpn_profiles) else []
    return {
        "connection": primary["type"] if primary else "",
        "adapter": primary["description"] if primary else "",
        "speed": primary["speed"] if primary else "",
        "ip": primary["ipv4"][0] if primary and primary["ipv4"] else "",
        "gateway": primary["gateway"][0] if primary and primary["gateway"] else "",
        "dns": primary["dns"] if primary else [],
        "mac": primary["mac"] if primary else "",
        "wifi": wifi,
        "public_ip": safe(lambda: _cached("public_ip", PUBLIC_IP_TTL, _public_ip), ""),
        "vpn": {
            "connected": bool(vpn_adapters),
            "ip": vpn_adapters[0]["ipv4"][0] if vpn_adapters else "",
            "profile": ", ".join(profiles)[:120] if vpn_adapters else "",
        },
        "latency_ms": safe(lambda: _latency_ms(server_url), None) if server_url else None,
        "adapters": adapters[:8],
    }
