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
from time import monotonic as time_monotonic  # noqa: E402


def quiet_logger():
    import logging

    logger = logging.getLogger("test-network")
    logger.handlers.clear()
    logger.addHandler(logging.NullHandler())
    logger.propagate = False
    return logger


def tlv(kind: int, value: bytes) -> bytes:
    return struct.pack(">H", (kind << 9) | len(value)) + value


def lldp_frame(port_description: bytes = b"15", vlan: bool = False, caps: int | None = None) -> bytes:
    chassis = bytes.fromhex("14ABEC22C9EC")
    port_mac = bytes.fromhex("14ABEC22C9FB")
    payload = (
        tlv(1, b"\x04" + chassis)
        + tlv(2, b"\x03" + port_mac)
        + tlv(3, struct.pack(">H", 120))
        + tlv(4, port_description)
        + tlv(5, b"TW46LNT1GH")
        + tlv(6, b"HPE Networking Instant On Switch 24p Gigabit 4p SFP+ 1930 JL682A")
        + (tlv(7, struct.pack(">HH", caps | 0x0004, caps)) if caps is not None else b"")
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

    def test_own_windows_announcement_is_ignored(self):
        # O Windows tambem anuncia LLDP pela placa (chassis = MAC do PC). Visto
        # em campo: o PC D0:C1:B5:7D:F3:EB virou um "switch" falso.
        pc = bytes.fromhex("D0C1B57DF3EB")
        windows = (bytes.fromhex("0180C200000E") + pc + struct.pack(">H", 0x88CC)
                   + tlv(1, b"\x04" + pc) + tlv(2, b"\x03" + pc) + tlv(3, struct.pack(">H", 120)) + tlv(0, b""))
        frames = [windows, lldp_frame()]
        # Mesmo sem a lista de MACs locais, o anuncio do Windows (sem nome,
        # modelo nem IP de gerencia) nao passa por switch.
        self.assertEqual(nr.first_lldp(frames)["chassis_id"], "14:AB:EC:22:C9:EC")
        found = nr.first_lldp(frames, frozenset({"D0-C1-B5-7D-F3-EB"}))
        self.assertEqual(found["chassis_id"], "14:AB:EC:22:C9:EC")
        self.assertIsNone(nr.first_lldp([windows], frozenset({"D0:C1:B5:7D:F3:EB"})))

    def test_other_pc_announcement_is_not_a_switch(self):
        # Visto em campo: atras de um switchzinho de mesa, o LLDP do Windows de
        # OUTRO PC (chassis = nome "DESKTOP-1IRFUQ2") virou um switch falso.
        other = bytes.fromhex("AABBCCDDEEFF")
        pc = (bytes.fromhex("0180C200000E") + other + struct.pack(">H", 0x88CC)
              + tlv(1, b"\x07DESKTOP-1IRFUQ2") + tlv(2, b"\x03" + other) + tlv(3, struct.pack(">H", 120)) + tlv(0, b""))
        self.assertIsNone(nr.first_lldp([pc]))
        self.assertEqual(nr.first_lldp([pc, lldp_frame()])["chassis_id"], "14:AB:EC:22:C9:EC")

    def test_capabilities_decide_switch_or_station(self):
        station = nr.parse_lldp_frame(lldp_frame(caps=0x0080))
        self.assertEqual(station["capabilities"], 0x0080)
        self.assertFalse(nr.is_switch_announcement(station))
        bridge = nr.parse_lldp_frame(lldp_frame(caps=0x0004))
        self.assertTrue(nr.is_switch_announcement(bridge))
        # Switch com nome/modelo mas sem a TLV de capacidades continua valendo.
        self.assertIsNone(nr.is_switch_announcement(nr.parse_lldp_frame(lldp_frame())))
        self.assertIsNone(nr.first_lldp([lldp_frame(caps=0x0080)]))

    def test_switch_announcement_wins_over_unknown(self):
        unknown = lldp_frame(port_description=b"7")
        switch = lldp_frame(port_description=b"15", caps=0x0004)
        self.assertEqual(nr.first_lldp([unknown, switch])["port_description"], "15")

    def test_local_mac_set(self):
        macs = nr.local_mac_set({"adapter": {"mac": "D0-C1-B5-7D-F3-EB"}, "macs": ["00-15-5D-01-02-03", ""]})
        self.assertEqual(macs, frozenset({"D0C1B57DF3EB", "00155D010203"}))

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

    def test_sends_change_then_one_confirmation(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report") as send:
            reporter.cycle()  # mudou: envia
            reporter.cycle()  # igual: reenvia uma vez para o GLPI confirmar
            reporter.cycle()  # igual de novo: so no reenvio de 6 h
        self.assertEqual(send.call_count, 2)
        self.assertEqual(send.call_args[0][0], "https://h/plugins/ativarede/api/v1/report")

    def test_request_now_forces_send_even_unchanged(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report") as send:
            reporter.cycle()
            reporter.cycle()  # confirmacao
            reporter.cycle()  # nada mudou: nao envia
            reporter.request_now()
            self.assertTrue(reporter.wake.is_set())
            reporter.cycle()  # pedido pela planta: envia
        self.assertEqual(send.call_count, 3)

    def test_sleep_wakes_on_request(self):
        import threading

        reporter = self.make()
        threading.Timer(0.2, reporter.request_now).start()
        started = time_monotonic()
        reporter._sleep(30)
        self.assertLess(time_monotonic() - started, 6)
        self.assertFalse(reporter.wake.is_set())

    def test_absent_plugin_backs_off(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "collect_report", return_value=report) as collect, \
                mock.patch.object(nr, "send_report", side_effect=nr.ReportRejected(404, "")):
            reporter.cycle()
            reporter.cycle()
        self.assertEqual(collect.call_count, 1, "com o plugin ausente nem coleta de novo ate o backoff")

    def test_network_timeout_retries_in_same_cycle(self):
        # Visto em campo: WinError 10060 numa maquina, heartbeat chegando.
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "SEND_RETRY_DELAYS_SECONDS", (0, 0, 0)), \
                mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report", side_effect=[nr.ReportRejected(0, "10060"), None]) as send:
            reporter.cycle()
        self.assertEqual(send.call_count, 2)
        self.assertFalse(reporter.retry_soon)

    def test_all_attempts_fail_retry_in_two_minutes(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "SEND_RETRY_DELAYS_SECONDS", (0, 0, 0)), \
                mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report", side_effect=nr.ReportRejected(0, "10060")) as send:
            reporter.cycle()
        self.assertEqual(send.call_count, 4)
        self.assertTrue(reporter.retry_soon)

    def test_validation_error_is_not_retried(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.0", ReportTests.SYSTEM, None)
        with mock.patch.object(nr, "SEND_RETRY_DELAYS_SECONDS", (0, 0, 0)), \
                mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report", side_effect=nr.ReportRejected(422, "x")) as send:
            reporter.cycle()
        self.assertEqual(send.call_count, 1)

    def test_powershell_failure_sends_nothing(self):
        # Um relatorio sem monitores marcaria os monitores da mesa como ausentes.
        reporter = self.make()
        with mock.patch.object(nr, "collect_report", return_value=None), \
                mock.patch.object(nr, "send_report") as send:
            reporter.cycle()
        send.assert_not_called()
        self.assertTrue(reporter.retry_soon)

    def test_collect_system_timeout_returns_none(self):
        def slow(args, **_kwargs):
            raise subprocess.TimeoutExpired(args, 180)
        self.assertIsNone(nr.collect_system(quiet_logger(), runner=slow))
        failed = lambda args, **_k: subprocess.CompletedProcess(args, 1, b"", b"Acesso negado")  # noqa: E731
        self.assertIsNone(nr.collect_system(quiet_logger(), runner=failed))
        ok = lambda args, **_k: subprocess.CompletedProcess(args, 0, b'{"bios":"X","monitors":[]}', b"")  # noqa: E731
        self.assertEqual(nr.collect_system(quiet_logger(), runner=ok)["bios"], "X")

    def test_collect_system_failure_records_reason(self):
        problems = []
        failed = lambda args, **_k: subprocess.CompletedProcess(args, 1, b"", b"Acesso negado")  # noqa: E731
        self.assertIsNone(nr.collect_system(quiet_logger(), runner=failed, problems=problems))
        self.assertEqual(len(problems), 1)
        self.assertIn("PowerShell", problems[0])

    NATIVE = {"adapter": {"name": "Ethernet", "description": "Intel(R) Ethernet", "media": "802.3",
                          "mac": "D0-C1-B5-7D-F3-EB", "ip": "192.168.80.50"}, "macs": ["D0-C1-B5-7D-F3-EB"]}

    def test_powershell_blocked_still_sends_partial_report(self):
        # Visto em campo: maquina com o PowerShell bloqueado/travado nunca
        # aparecia no Ativa Rede. Agora vai a rede (API do Windows) e a porta.
        def blocked(_logger, runner=None, problems=None):
            problems.append("PowerShell nao executou: acesso negado")
            return None
        lldp = nr.parse_lldp_frame(lldp_frame())
        with mock.patch.object(nr, "collect_system", side_effect=blocked), \
                mock.patch.object(nr, "native_network", return_value=self.NATIVE), \
                mock.patch.object(nr, "capture_lldp", return_value=lldp) as capture:
            report = nr.collect_report("abc", "PC", "1.6.9", Path(tempfile.gettempdir()), quiet_logger())
        capture.assert_called_once()
        self.assertTrue(report["monitors_unknown"])
        self.assertEqual(report["monitors"], [])
        self.assertEqual(report["network"]["link"], "wired")
        self.assertEqual(report["network"]["lldp"]["mgmt_ip"], "192.168.80.43")
        self.assertEqual(report["problems"], ["PowerShell nao executou: acesso negado"])

    def test_full_report_has_no_problems_and_known_monitors(self):
        found = {"bios": "BRJ123ABC", "monitors": ReportTests.SYSTEM["monitors"]}
        with mock.patch.object(nr, "collect_system", return_value=found), \
                mock.patch.object(nr, "native_network", return_value=self.NATIVE), \
                mock.patch.object(nr, "capture_lldp", return_value=nr.parse_lldp_frame(lldp_frame(caps=4))):
            report = nr.collect_report("abc", "PC", "1.6.9", Path(tempfile.gettempdir()), quiet_logger())
        self.assertFalse(report["monitors_unknown"])
        self.assertEqual(report["problems"], [])
        self.assertEqual(len(report["monitors"]), 1)
        self.assertEqual(report["bios_serial"], "BRJ123ABC")

    def test_network_always_comes_from_windows_api(self):
        # O PowerShell nao le mais a rede (Get-NetIPConfiguration travava).
        self.assertNotIn("Get-NetIPConfiguration", nr.SYSTEM_PS)
        self.assertNotIn("Get-NetAdapter", nr.SYSTEM_PS)
        with mock.patch.object(nr, "collect_system", return_value={"bios": "X", "monitors": []}), \
                mock.patch.object(nr, "native_network", return_value=self.NATIVE), \
                mock.patch.object(nr, "capture_lldp", return_value=None) as capture:
            report = nr.collect_report("abc", "PC", "1.6.9", Path(tempfile.gettempdir()), quiet_logger())
        self.assertEqual(report["network"]["ip"], "192.168.80.50")
        self.assertEqual(report["network"]["link"], "wired")
        self.assertEqual(capture.call_args.kwargs["local_macs"], frozenset({"D0C1B57DF3EB"}))

    def test_powershell_timeout_tells_where_it_stopped(self):
        def stuck(args, **_kwargs):
            raise subprocess.TimeoutExpired(args, 180, output=b"#etapa inicio\r\n#etapa bios\r\n#etapa monitores\r\n")
        problems = []
        self.assertIsNone(nr.collect_system(quiet_logger(), runner=stuck, problems=problems))
        self.assertIn("travou na etapa 'monitores'", problems[0])

        def never_started(args, **_kwargs):
            raise subprocess.TimeoutExpired(args, 180, output=b"")
        problems = []
        nr.collect_system(quiet_logger(), runner=never_started, problems=problems)
        self.assertIn("nem comecou", problems[0])

    def test_stage_markers_are_ignored_when_parsing(self):
        out = b'#etapa inicio\r\n#etapa bios\r\n#etapa monitores\r\n{"bios":"X","monitors":[]}\r\n'
        ok = lambda args, **_k: subprocess.CompletedProcess(args, 0, out, b"")  # noqa: E731
        self.assertEqual(nr.collect_system(quiet_logger(), runner=ok)["bios"], "X")

    def test_partial_report_retries_full_reading_soon(self):
        reporter = self.make()
        report = nr.build_report("abc", "PC", "1.6.9", {"adapter": self.NATIVE["adapter"]}, None,
                                 ["PowerShell nao respondeu"], monitors_unknown=True)
        with mock.patch.object(nr, "collect_report", return_value=report), \
                mock.patch.object(nr, "send_report") as send:
            reporter.cycle()
        send.assert_called_once()
        self.assertTrue(reporter.retry_soon)

    def test_quick_retries_are_bounded(self):
        reporter = self.make()
        sleeps = []
        cycles = iter(range(6))

        def fail_cycle():
            reporter.retry_soon = True
            if next(cycles, None) is None or len(sleeps) >= 5:
                reporter.stop_event.set()

        with mock.patch.object(reporter, "cycle", side_effect=fail_cycle), \
                mock.patch.object(reporter, "_sleep", side_effect=sleeps.append):
            reporter.run()
        # Primeira espera e a inicial; depois 3 tentativas rapidas e volta aos 15 min.
        self.assertEqual(sleeps[1:5], [nr.CONFIRM_DELAY_SECONDS] * 3 + [nr.REPORT_INTERVAL_SECONDS])

    def test_absent_backoff_is_short(self):
        self.assertLessEqual(nr.ABSENT_BACKOFF_SECONDS, 30 * 60)

    def test_native_network_reads_this_windows(self):
        if sys.platform != "win32":
            self.skipTest("so Windows")
        result = nr.native_network()
        self.assertIsNotNone(result)
        self.assertIsInstance(result["macs"], list)


if __name__ == "__main__":
    unittest.main()
