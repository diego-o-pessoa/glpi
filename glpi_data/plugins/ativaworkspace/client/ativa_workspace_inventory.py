"""
Ativa Workspace - coleta de inventario da maquina.

Os mesmos dados que um inventario de TI ja coleta: programas instalados, discos,
memoria, CPU, uptime e processos. Nada sensivel, nenhuma credencial. Cada bloco
e protegido: se um falhar, os demais continuam.
"""

from __future__ import annotations

import ctypes
import platform
import socket
import subprocess
import time
from ctypes import wintypes

NO_WINDOW = getattr(subprocess, "CREATE_NO_WINDOW", 0)


class _MEMORYSTATUSEX(ctypes.Structure):
    _fields_ = [
        ("dwLength", wintypes.DWORD), ("dwMemoryLoad", wintypes.DWORD),
        ("ullTotalPhys", ctypes.c_ulonglong), ("ullAvailPhys", ctypes.c_ulonglong),
        ("ullTotalPageFile", ctypes.c_ulonglong), ("ullAvailPageFile", ctypes.c_ulonglong),
        ("ullTotalVirtual", ctypes.c_ulonglong), ("ullAvailVirtual", ctypes.c_ulonglong),
        ("ullAvailExtendedVirtual", ctypes.c_ulonglong),
    ]


def _memory() -> dict:
    status = _MEMORYSTATUSEX()
    status.dwLength = ctypes.sizeof(_MEMORYSTATUSEX)
    if not ctypes.windll.kernel32.GlobalMemoryStatusEx(ctypes.byref(status)):
        return {}
    total_mb = status.ullTotalPhys // (1024 * 1024)
    avail_mb = status.ullAvailPhys // (1024 * 1024)
    return {
        "ram_percent": int(status.dwMemoryLoad),
        "ram_total_mb": int(total_mb),
        "ram_used_mb": int(total_mb - avail_mb),
    }


def _system_times() -> tuple[int, int, int]:
    idle = wintypes.FILETIME()
    kernel = wintypes.FILETIME()
    user = wintypes.FILETIME()
    ctypes.windll.kernel32.GetSystemTimes(ctypes.byref(idle), ctypes.byref(kernel), ctypes.byref(user))

    def to_int(ft: wintypes.FILETIME) -> int:
        return (ft.dwHighDateTime << 32) | ft.dwLowDateTime

    return to_int(idle), to_int(kernel), to_int(user)


def _cpu_percent() -> int:
    try:
        idle1, kernel1, user1 = _system_times()
        time.sleep(0.5)
        idle2, kernel2, user2 = _system_times()
        idle = idle2 - idle1
        total = (kernel2 - kernel1) + (user2 - user1)
        return round((1 - idle / total) * 100) if total > 0 else 0
    except Exception:  # noqa: BLE001
        return 0


def _uptime_hours() -> int:
    ctypes.windll.kernel32.GetTickCount64.restype = ctypes.c_ulonglong
    return round(ctypes.windll.kernel32.GetTickCount64() / 3_600_000)


def _disks() -> list[dict]:
    kernel32 = ctypes.windll.kernel32
    drives_mask = kernel32.GetLogicalDrives()
    disks: list[dict] = []
    for i in range(26):
        if not (drives_mask >> i) & 1:
            continue
        root = f"{chr(65 + i)}:\\"
        if kernel32.GetDriveTypeW(root) != 3:  # 3 = DRIVE_FIXED
            continue
        free = ctypes.c_ulonglong(0)
        total = ctypes.c_ulonglong(0)
        if not kernel32.GetDiskFreeSpaceExW(root, None, ctypes.byref(total), ctypes.byref(free)):
            continue
        total_gb = total.value / (1024 ** 3)
        free_gb = free.value / (1024 ** 3)
        used_pct = round((1 - free_gb / total_gb) * 100) if total_gb else 0
        disks.append({
            "drive": f"{chr(65 + i)}:",
            "total_gb": round(total_gb),
            "free_gb": round(free_gb),
            "percent": used_pct,
        })
    return disks


def _programs() -> list[dict]:
    import winreg

    roots = [
        (winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall", winreg.KEY_WOW64_64KEY),
        (winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall", winreg.KEY_WOW64_32KEY),
        (winreg.HKEY_CURRENT_USER, r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall", 0),
    ]
    seen: set[str] = set()
    programs: list[dict] = []
    for hive, path, view in roots:
        try:
            key = winreg.OpenKey(hive, path, 0, winreg.KEY_READ | view)
        except OSError:
            continue
        with key:
            count = winreg.QueryInfoKey(key)[0]
            for index in range(count):
                try:
                    sub = winreg.EnumKey(key, index)
                    with winreg.OpenKey(key, sub) as item:
                        name = _reg_str(item, "DisplayName")
                        if not name or _reg_int(item, "SystemComponent") == 1:
                            continue
                        marker = name.lower()
                        if marker in seen:
                            continue
                        seen.add(marker)
                        programs.append({
                            "name": name[:160],
                            "version": _reg_str(item, "DisplayVersion")[:64],
                            "publisher": _reg_str(item, "Publisher")[:120],
                        })
                except OSError:
                    continue
    programs.sort(key=lambda p: p["name"].lower())
    return programs[:500]


def _reg_str(key, name: str) -> str:
    import winreg
    try:
        value, _ = winreg.QueryValueEx(key, name)
        return str(value).strip()
    except OSError:
        return ""


def _reg_int(key, name: str) -> int:
    import winreg
    try:
        value, _ = winreg.QueryValueEx(key, name)
        return int(value)
    except (OSError, ValueError, TypeError):
        return 0


def _processes(limit: int = 25) -> list[dict]:
    """Top processos por memoria, via tasklist (agrega por nome)."""
    try:
        completed = subprocess.run(
            ["tasklist", "/fo", "csv", "/nh"],
            capture_output=True, text=True, timeout=30, creationflags=NO_WINDOW,
        )
    except (OSError, subprocess.SubprocessError):
        return []
    import csv
    import io

    totals: dict[str, int] = {}
    for row in csv.reader(io.StringIO(completed.stdout or "")):
        if len(row) < 5:
            continue
        name = row[0]
        mem = row[4].replace("\xa0", " ").replace(".", "").replace(",", "")
        kb = int("".join(ch for ch in mem if ch.isdigit()) or 0)
        totals[name] = totals.get(name, 0) + kb
    ordered = sorted(totals.items(), key=lambda kv: kv[1], reverse=True)[:limit]
    return [{"name": name[:80], "ram_mb": round(kb / 1024)} for name, kb in ordered]


def collect(agent_version: str) -> dict:
    """Snapshot completo. Cada bloco falha isolado (retorna vazio, nao derruba)."""
    def safe(fn, default):
        try:
            return fn()
        except Exception:  # noqa: BLE001
            return default

    memory = safe(_memory, {})
    system = {
        "os": platform.platform(),
        "cpu_percent": safe(_cpu_percent, 0),
        "uptime_hours": safe(_uptime_hours, 0),
    }
    system.update(memory)
    return {
        "hostname": socket.gethostname(),
        "agent_version": agent_version,
        "system": system,
        "disks": safe(_disks, []),
        "programs": safe(_programs, []),
        "processes": safe(_processes, []),
    }
