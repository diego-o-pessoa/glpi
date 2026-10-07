"""Testes do relatorio Ativa Rede (network_report.py).

Uso: python -m unittest discover -s tests -p "test_network_report.py"

O quadro LLDP de teste reproduz o que o switch Instant On 1930 (.43) enviou na
porta 15 durante a verificacao em campo. A captura real com o pktmon so roda
em Windows com administrador: aqui o pktmon e simulado.
"""

from __future__ import annotations

import json
import struct
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import network_report as nr  # noqa: E402


def quiet_logger():
    import logging

    logger = logging.getLogger("test-network")
    logger.handlers.clear()
    logger.addHandler(logging.NullHandler())
    logger.propagate = False
    return logger


def tlv(kind: int, value: bytes) -> bytes:
    return struct.pack(">H", (kind << 9) | len(value)) + value


def lldp_frame(port_description: bytes = b"15", vlan: bool = False) -> bytes:
    chassis = bytes.fromhex("14ABEC22C9EC")
    port_mac = bytes.fromhex("14ABEC22C9FB")
    payload = (
        tlv(1, b"\x04" + chassis)
        + tlv(2, b"\x03" + port_mac)
        + tlv(3, struct.pack(">H", 120))
        + tlv(4, port_description)
        + tlv(5, b"TW46LNT1GH")
        + tlv(6, b"HPE Networking Instant On Switch 24p Gigabit 4p SFP+ 1930 JL682A")
        + tlv(8, b"\x05\x01" + bytes([192, 168, 80, 43]) + b"\x02\x00\x00\x00\x01\x00")
        + tlv(0, b"")
    )
    header = bytes.fromhex("0180C200000E") + port_mac
    if vlan:
        header += struct.pack(">HH", 0x8100, 10)
    return header + struct.pack(">H", 0x88CC) + payload


def pcapng(frames: list[bytes]) -> bytes:
    shb_body = struct.pack("<IHHq", 0x1A2B3C4D, 1, 0, -1)
    out = struct.pack("<II", 0x0A0D0D0A, 12 + len(shb_body)) + shb_body + struct.pack("<I", 12 + len(shb_body))
    idb_body = struct.pack("<HHI", 1, 0, 65535)
    out += struct.pack("<II", 1, 12 + len(idb_body)) + idb_body + struct.pack("<I", 12 + len(idb_body))
    for frame in frames:
        padded = frame + b"\x00" * ((4 - len(frame) % 4) % 4)
        body = struct.pack("<IIIII", 0, 0, 0, len(frame), len(frame)) + padded
        out += struct.pack("<II", 6, 12 + len(body)) + body + struct.pack("<I", 12 + len(body))
    return out


class LldpParsingTests(unittest.TestCase):
    def test_real_switch_frame(self):
        parsed = nr.parse_lldp_frame(lldp_frame())
        self.assertEqual(parsed["chassis_id"], "14:AB:EC:22:C9:EC")
        self.assertEqual(parsed["port_id"], "14:AB:EC:22:C9:FB")
        self.assertEqual(parsed["port_description"], "15")
        self.assertEqual(parsed["system_name"], "TW46LNT1GH")
        self.assertIn("1930", parsed["system_description"])
        self.assertEqual(parsed["mgmt_ip"], "192.168.80.43")

    def test_vlan_tagged_frame(self):
        self.assertEqual(nr.parse_lldp_frame(lldp_frame(vlan=True))["port_description"], "15")

    def test_non_lldp_frame_is_ignored(self):
        arp = bytes(12) + struct.pack(">H", 0x0806) + bytes(28)
        self.assertIsNone(nr.parse_lldp_frame(arp))
        self.assertIsNone(nr.parse_lldp_frame(b"\x00" * 6))

    def test_truncated_payload_does_not_crash(self):
        self.assertIsNone(nr.parse_lldp_payload(tlv(1, b"\x04\x14")[:3]))

    def test_pcapng_reader_finds_lldp_among_other_frames(self):
        noise = bytes(12) + struct.pack(">H", 0x0800) + bytes(40)
        frames = nr.read_pcapng_frames(pcapng([noise, lldp_frame(b"7")]))
        self.assertEqual(len(frames), 2)
        self.assertEqual(nr.first_lldp(frames)["port_description"], "7")

    def test_pcapng_garbage(self):
        self.assertEqual(nr.read_pcapng_frames(b"\x00\x01\x02"), [])


class FakePktmon:
    """Simula o pktmon: grava o pcapng no lugar do arquivo convertido."""

    def __init__(self, start_rc: int = 0, frames: list[bytes] | None = None) -> None:
        self.start_rc = start_rc
        self.frames = frames if frames is not None else [lldp_frame()]
        self.calls: list[list[str]] = []

    def __call__(self, args, **_kwargs):
        self.calls.append(list(args))
        rc = 0
        if args[1] == "start":
            rc = self.start_rc
            if rc == 0:
                Path(args[args.index("-f") + 1]).write_bytes(b"etl")
        if args[1] == "etl2pcap":
            Path(args[args.index("-o") + 1]).write_bytes(pcapng(self.frames))
        return subprocess.CompletedProcess(args, rc, b"", b"")


class CaptureTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.work = Path(self.tmp.name)
        patcher = mock.patch.object(nr, "PKTMON", Path(self.tmp.name) / "pktmon.exe")
        patcher.start()
        self.addCleanup(patcher.stop)
        (Path(self.tmp.name) / "pktmon.exe").write_bytes(b"")

    def tearDown(self):
        self.tmp.cleanup()

    def test_capture_returns_switch_and_cleans_up(self):
        fake = FakePktmon()
        waited = []
        result = nr.capture_lldp(self.work / "net", quiet_logger(), wait=waited.append, runner=fake)
        self.assertEqual(result["port_description"], "15")
        self.assertEqual(waited, [nr.LLDP_WAIT_SECONDS])
        verbs = [c[1] for c in fake.calls]
        self.assertIn("stop", verbs)
        self.assertLess(verbs.index("stop"), verbs.index("etl2pcap"))
        self.assertFalse(any((self.work / "net").glob("lldp.*")), "arquivos temporarios devem ser apagados")

    def test_other_capture_running_is_not_stopped(self):
        fake = FakePktmon(start_rc=1)
        self.assertIsNone(nr.capture_lldp(self.work / "net", quiet_logger(), wait=lambda _s: None, runner=fake))
        self.assertNotIn("stop", [c[1] for c in fake.calls])

    def test_no_lldp_heard(self):
        noise = bytes(12) + struct.pack(">H", 0x0800) + bytes(40)
        fake = FakePktmon(frames=[noise])
        self.assertIsNone(nr.capture_lldp(self.work / "net", quiet_logger(), wait=lambda _s: None, runner=fake))

    def test_concurrent_capture_is_skipped(self):
        name = "Local\\AtivaRedeTesteLock"
        with nr.CaptureLock(name) as first:
            self.assertTrue(first.acquired)
            if sys.platform == "win32":
                # Outra thread nao pega o mesmo mutex enquanto o primeiro segura.
                import threading

                result = {}
                def other():
                    with nr.CaptureLock(name) as second:
                        result["acquired"] = second.acquired
                t = threading.Thread(target=other)
                t.start()
                t.join()
                self.assertFalse(result["acquired"])
        with nr.CaptureLock(name) as again:
            self.assertTrue(again.acquired)

    def test_missing_pktmon(self):
        with mock.patch.object(nr, "PKTMON", self.work / "nao-existe.exe"):
            self.assertIsNone(nr.capture_lldp(self.work / "net", quiet_logger(), runner=FakePktmon()))


class ReportTests(unittest.TestCase):
    SYSTEM = {
        "adapter": {"name": "Ethernet", "description": "Intel(R) Ethernet", "media": "802.3",
                    "mac": "D0-C1-B5-7D-F3-EB", "ip": "192.168.80.231"},
        "bios": "BRJ123ABC",
        "monitors": [
            {"active": True, "manufacturer": "DEL", "product": "A0B1", "serial": "CN0ABC123",
             "name": "DELL P2422H", "year": 2023, "week": 14, "output": 5},
            {"active": True, "manufacturer": "BOE", "product": "0812", "serial": "", "name": "",
             "year": 2021, "week": 3, "output": 2147483648},
            {"active": False, "manufacturer": "SAM", "product": "0F00", "serial": "X", "name": "Old", "output": 10},
        ],
    }

    def test_build_report_contract(self):
        lldp = nr.parse_lldp_frame(lldp_frame())
        report = nr.build_report("abc123", "ATV-045", "1.6.0", self.SYSTEM, lldp)
        self.assertEqual(report["network"]["link"], "wired")
        self.assertEqual(report["network"]["mac"], "D0:C1:B5:7D:F3:EB")
        self.assertEqual(report["network"]["lldp"]["mgmt_ip"], "192.168.80.43")
        self.assertEqual(report["bios_serial"], "BRJ123ABC")
        # Tela interna do notebook e monitor inativo ficam de fora.
        self.assertEqual(len(report["monitors"]), 1)
        self.assertEqual(report["monitors"][0]["connection"], "HDMI")
        self.assertEqual(report["monitors"][0]["serial"], "CN0ABC123")
        json.dumps(report)  # serializavel

    def test_wifi_never_sends_lldp(self):
        system = dict(self.SYSTEM, adapter={"description": "Intel(R) Wi-Fi 6 AX201", "media": "Native 802.11"})
        report = nr.build_report("abc", "PC", "1.6.0", system, nr.parse_lldp_frame(lldp_frame()))
        self.assertEqual(report["network"]["link"], "wifi")
        self.assertIsNone(report["network"]["lldp"])

    def test_no_adapter(self):
        report = nr.build_report("abc", "PC", "1.6.0", {}, None)
        self.assertEqual(report["network"]["link"], "none")
        self.assertEqual(report["monitors"], [])

    def test_single_monitor_object_from_powershell(self):
        single = {"active": True, "manufacturer": "AOC", "product": "2402", "serial": "Z1", "name": "24G2", "output": 10}
        self.assertEqual(nr.normalize_monitors(single)[0]["connection"], "DisplayPort")

    def test_rede_url(self):
        self.assertEqual(
            nr.rede_url("https://chamados.exemplo:8443/plugins/ativaguardian/api/v1"),
            "https://chamados.exemplo:8443/plugins/ativarede/api/v1/report",
        )
        with self.assertRaises(ValueError):
            nr.rede_url("https://x/outra/api")


class ReporterTests(unittest.TestCase):
    def make(self):
        import threading

        reporter = nr.NetworkReporter(
            threading.Event(), quiet_logger(), "abc", lambda: "PC", "1.6.0", Path(tempfile.gettempdir()),
            lambda: {"api_url": "https://h/plugins/ativaguardian/api/v1", "api_token": "a" * 64},
        )
        return reporter

    def test_sends_only_when_changed(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report") as send:
            reporter.cycle()
            reporter.cycle()
        self.assertEqual(send.call_count, 1)
        self.assertEqual(send.call_args[0][0], "https://h/plugins/ativarede/api/v1/report")

    def test_absent_plugin_backs_off(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report) as collect, \
                mock.patch.object(nr, "send_report", side_effect=nr.ReportRejected(404, "")):
            reporter.cycle()
            reporter.cycle()
        self.assertEqual(collect.call_count, 1, "com o plugin ausente nem coleta de novo ate o backoff")

    def test_failure_retries_next_cycle(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report", side_effect=[nr.ReportRejected(500, "x"), None]) as send:
            reporter.cycle()
            reporter.cycle()
        self.assertEqual(send.call_count, 2)


if __name__ == "__main__":
    unittest.main()
