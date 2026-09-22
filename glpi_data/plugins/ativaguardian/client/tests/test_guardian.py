"""Testes do servico Ativa Guardian.

Uso: python -m unittest discover -s tests -p "test_guardian.py"

Cobrem o que da para cobrir sem uma maquina de verdade: mapeamento de status de
cada componente (inclusive ausente), isolamento de falhas, deteccao de antivirus,
persistencia do machine_id, validacao de configuracao, resiliencia a API offline
e aderencia do payload ao contrato validado pelo plugin PHP.

Reboot e partida real do servico sao verificados manualmente (ver docs/SERVICE.md).
"""

from __future__ import annotations

import json
import re
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import ativa_guardian_service as guardian  # noqa: E402


def quiet_logger():
    import logging

    logger = logging.getLogger("test-guardian")
    logger.handlers.clear()
    logger.addHandler(logging.NullHandler())
    logger.propagate = False
    return logger


class ComponentStatusTests(unittest.TestCase):
    """Cada componente traduzido para o vocabulario de status da API."""

    def setUp(self) -> None:
        self.tmp = tempfile.TemporaryDirectory()
        self.root = Path(self.tmp.name)
        self.addCleanup(self.tmp.cleanup)

    def touch(self, name: str) -> Path:
        path = self.root / name
        path.write_text("x", encoding="utf-8")
        return path

    # -- Ativa Updater (servico SYSTEM) --------------------------------------
    def test_updater_missing_executable(self) -> None:
        with mock.patch.object(guardian, "UPDATER_EXE", self.root / "ausente.exe"), \
             mock.patch.object(guardian, "UPDATER_HEARTBEAT_PATH", self.root / "nada.json"):
            self.assertEqual(guardian.check_updater()["status"], guardian.STATUS_FILE_MISSING)

    def test_updater_service_stopped(self) -> None:
        exe = self.touch("AtivaUnifiedUpdater.exe")
        with mock.patch.object(guardian, "UPDATER_EXE", exe), \
             mock.patch.object(guardian, "UPDATER_HEARTBEAT_PATH", self.root / "nada.json"), \
             mock.patch.object(guardian, "query_service", return_value=1):
            self.assertEqual(guardian.check_updater()["status"], guardian.STATUS_SERVICE_STOPPED)

    def test_updater_healthy_with_version_from_heartbeat(self) -> None:
        exe = self.touch("AtivaUnifiedUpdater.exe")
        heartbeat = self.root / "heartbeat.json"
        heartbeat.write_text(json.dumps({"at": 1, "version": "1.7.3"}), encoding="utf-8")
        with mock.patch.object(guardian, "UPDATER_EXE", exe), \
             mock.patch.object(guardian, "UPDATER_HEARTBEAT_PATH", heartbeat), \
             mock.patch.object(guardian, "query_service", return_value=guardian.SERVICE_STATE_RUNNING):
            result = guardian.check_updater()
        self.assertEqual(result["status"], guardian.STATUS_HEALTHY)
        self.assertEqual(result["version"], "1.7.3")

    def test_updater_executable_present_but_service_absent(self) -> None:
        """Foi exatamente o que o Defender causou em producao."""
        exe = self.touch("AtivaUnifiedUpdater.exe")
        with mock.patch.object(guardian, "UPDATER_EXE", exe), \
             mock.patch.object(guardian, "UPDATER_HEARTBEAT_PATH", self.root / "nada.json"), \
             mock.patch.object(guardian, "query_service", return_value=None):
            self.assertEqual(guardian.check_updater()["status"], guardian.STATUS_ERROR)

    # -- Ativa Wallpaper (processo por usuario) ------------------------------
    def test_wallpaper_missing_executable(self) -> None:
        with mock.patch.object(guardian, "WALLPAPER_EXE", self.root / "ausente.exe"), \
             mock.patch.object(guardian, "WALLPAPER_VERSION_PATH", self.root / "nada.json"):
            self.assertEqual(guardian.check_wallpaper()["status"], guardian.STATUS_FILE_MISSING)

    def test_wallpaper_process_stopped(self) -> None:
        exe = self.touch("AtivaWallpaperClient.exe")
        with mock.patch.object(guardian, "WALLPAPER_EXE", exe), \
             mock.patch.object(guardian, "WALLPAPER_VERSION_PATH", self.root / "nada.json"), \
             mock.patch.object(guardian, "is_process_running", return_value=False):
            self.assertEqual(guardian.check_wallpaper()["status"], guardian.STATUS_PROCESS_STOPPED)

    def test_wallpaper_healthy_with_version(self) -> None:
        exe = self.touch("AtivaWallpaperClient.exe")
        version_file = self.root / "version.json"
        version_file.write_text(json.dumps({"client_version": "1.6.2"}), encoding="utf-8")
        with mock.patch.object(guardian, "WALLPAPER_EXE", exe), \
             mock.patch.object(guardian, "WALLPAPER_VERSION_PATH", version_file), \
             mock.patch.object(guardian, "is_process_running", return_value=True):
            result = guardian.check_wallpaper()
        self.assertEqual(result["status"], guardian.STATUS_HEALTHY)
        self.assertEqual(result["version"], "1.6.2")

    # -- Ativa Remote (RustDesk) ---------------------------------------------
    def test_remote_missing(self) -> None:
        with mock.patch.object(guardian, "RUSTDESK_INSTALLED_EXE", self.root / "ausente.exe"), \
             mock.patch.object(guardian, "registry_uninstall_version", return_value=""):
            self.assertEqual(guardian.check_remote()["status"], guardian.STATUS_FILE_MISSING)

    def test_remote_service_stopped(self) -> None:
        exe = self.touch("rustdesk.exe")
        with mock.patch.object(guardian, "RUSTDESK_INSTALLED_EXE", exe), \
             mock.patch.object(guardian, "registry_uninstall_version", return_value="1.2.3"), \
             mock.patch.object(guardian, "query_service", return_value=1):
            result = guardian.check_remote()
        self.assertEqual(result["status"], guardian.STATUS_SERVICE_STOPPED)
        self.assertEqual(result["version"], "1.2.3")

    def test_remote_healthy(self) -> None:
        exe = self.touch("rustdesk.exe")
        with mock.patch.object(guardian, "RUSTDESK_INSTALLED_EXE", exe), \
             mock.patch.object(guardian, "registry_uninstall_version", return_value="1.2.3"), \
             mock.patch.object(guardian, "query_service", return_value=guardian.SERVICE_STATE_RUNNING):
            self.assertEqual(guardian.check_remote()["status"], guardian.STATUS_HEALTHY)

    # -- GLPI Agent -----------------------------------------------------------
    def test_glpi_agent_not_installed(self) -> None:
        with mock.patch.object(guardian, "glpi_agent_version", return_value=""), \
             mock.patch.object(guardian, "first_existing_service", return_value=None):
            self.assertEqual(guardian.check_glpi_agent()["status"], guardian.STATUS_FILE_MISSING)

    def test_glpi_agent_registered_but_service_gone(self) -> None:
        with mock.patch.object(guardian, "glpi_agent_version", return_value="1.19"), \
             mock.patch.object(guardian, "first_existing_service", return_value=None):
            self.assertEqual(guardian.check_glpi_agent()["status"], guardian.STATUS_ERROR)

    def test_glpi_agent_service_stopped(self) -> None:
        with mock.patch.object(guardian, "glpi_agent_version", return_value="1.19"), \
             mock.patch.object(guardian, "first_existing_service", return_value=("glpi-agent", 1)):
            result = guardian.check_glpi_agent()
        self.assertEqual(result["status"], guardian.STATUS_SERVICE_STOPPED)
        self.assertEqual(result["version"], "1.19")

    def test_glpi_agent_healthy(self) -> None:
        with mock.patch.object(guardian, "glpi_agent_version", return_value="1.19"), \
             mock.patch.object(
                 guardian, "first_existing_service",
                 return_value=("glpi-agent", guardian.SERVICE_STATE_RUNNING)):
            self.assertEqual(guardian.check_glpi_agent()["status"], guardian.STATUS_HEALTHY)


class IsolationTests(unittest.TestCase):
    """Uma verificacao que explode nao pode contaminar as outras nem o servico."""

    def test_failing_check_becomes_unknown_and_others_survive(self) -> None:
        def boom() -> dict[str, str]:
            raise OSError("disco sumiu")

        checks = dict(guardian.COMPONENT_CHECKS)
        checks["updater"] = boom
        with mock.patch.object(guardian, "COMPONENT_CHECKS", checks), \
             mock.patch.object(guardian, "check_wallpaper", return_value={"status": "healthy", "version": ""}), \
             mock.patch.object(guardian, "check_remote", return_value={"status": "healthy", "version": ""}), \
             mock.patch.object(guardian, "check_glpi_agent", return_value={"status": "healthy", "version": ""}):
            components = guardian.collect_components(quiet_logger())

        self.assertEqual(components["updater"]["status"], guardian.STATUS_UNKNOWN)
        self.assertEqual(len(components), 4)

    def test_component_never_reports_offline(self) -> None:
        """A API rejeita 'offline' num componente: ele e derivado no servidor."""
        self.assertNotIn("offline", guardian.REPORTABLE_STATUSES)
        self.assertEqual(guardian._component("offline")["status"], guardian.STATUS_UNKNOWN)


class AntivirusTests(unittest.TestCase):
    def setUp(self) -> None:
        guardian._antivirus_cache = (0.0, "")

    def run_with_output(self, stdout: str, returncode: int = 0) -> str:
        completed = mock.Mock(returncode=returncode, stdout=stdout)
        with mock.patch.object(guardian.subprocess, "run", return_value=completed):
            return guardian.detect_antivirus(quiet_logger())

    def test_defender_detected(self) -> None:
        self.assertEqual(self.run_with_output("Windows Defender\n"), "Microsoft Defender")

    def test_bitdefender_detected(self) -> None:
        self.assertEqual(self.run_with_output("Bitdefender Endpoint Security Tools\n"), "Bitdefender")

    def test_third_party_preferred_over_defender(self) -> None:
        self.assertEqual(self.run_with_output("Windows Defender|Bitdefender Antivirus\n"), "Bitdefender")

    def test_other_antivirus_kept_by_name(self) -> None:
        self.assertEqual(self.run_with_output("Kaspersky Endpoint Security\n"), "Kaspersky Endpoint Security")

    def test_unknown_when_nothing_answers(self) -> None:
        with mock.patch.object(guardian, "query_service", return_value=None):
            self.assertEqual(self.run_with_output("", returncode=1), "unknown")

    def test_falls_back_to_windefend_service(self) -> None:
        with mock.patch.object(guardian, "query_service", return_value=guardian.SERVICE_STATE_RUNNING):
            self.assertEqual(self.run_with_output("", returncode=1), "Microsoft Defender")

    def test_result_fits_api_field(self) -> None:
        value = self.run_with_output("Algum AV (c) versao 2.0/beta+\n")
        self.assertRegex(value, r"^[\w .,_\-/()+]{1,128}$")


class MachineIdentityTests(unittest.TestCase):
    def test_machine_id_is_generated_then_reused(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "machine.json"
            with mock.patch.object(guardian, "MACHINE_PATH", path):
                first = guardian.machine_identity(quiet_logger())
                second = guardian.machine_identity(quiet_logger())
        self.assertEqual(first, second)
        self.assertRegex(first, r"^[A-Za-z0-9._-]{1,128}$")

    def test_corrupt_machine_file_is_replaced(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "machine.json"
            path.write_text("isto nao e json", encoding="utf-8")
            with mock.patch.object(guardian, "MACHINE_PATH", path):
                machine_id = guardian.machine_identity(quiet_logger())
        self.assertRegex(machine_id, r"^[A-Za-z0-9._-]{1,128}$")


class ConfigurationTests(unittest.TestCase):
    BASE = {
        "api_url": "https://glpi.exemplo.com/plugins/ativaguardian/api/v1",
        "api_token": "a" * 64,
        "verify_tls": True,
        "heartbeat_interval_seconds": 300,
    }

    def test_valid_configuration(self) -> None:
        self.assertEqual(guardian.validate_config(dict(self.BASE))["heartbeat_interval_seconds"], 300)

    def test_http_is_rejected(self) -> None:
        bad = dict(self.BASE, api_url="http://glpi.exemplo.com/plugins/ativaguardian/api/v1")
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(bad)

    def test_wrong_plugin_endpoint_is_rejected(self) -> None:
        bad = dict(self.BASE, api_url="https://glpi.exemplo.com/plugins/ativaupdater/api/v1")
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(bad)

    def test_token_in_query_string_is_rejected(self) -> None:
        bad = dict(self.BASE, api_url="https://glpi.exemplo.com/plugins/ativaguardian/api/v1?token=x")
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(bad)

    def test_bad_token_is_rejected(self) -> None:
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(dict(self.BASE, api_token="curto"))

    def test_disabling_tls_is_rejected(self) -> None:
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(dict(self.BASE, verify_tls=False))

    def test_interval_out_of_range_is_rejected(self) -> None:
        with self.assertRaises(guardian.GuardianError):
            guardian.validate_config(dict(self.BASE, heartbeat_interval_seconds=5))


class HeartbeatTests(unittest.TestCase):
    """O payload precisa passar na mesma validacao que o ApiController faz."""

    def test_payload_matches_plugin_contract(self) -> None:
        components = {
            "glpi_agent": {"status": "healthy", "version": "1.19"},
            "wallpaper": {"status": "healthy", "version": "1.6.2"},
            "updater": {"status": "service_stopped", "version": "1.7.3"},
            "remote": {"status": "file_missing", "version": ""},
        }
        payload = guardian.build_heartbeat("abc123", components, "Microsoft Defender")

        self.assertRegex(payload["machine_id"], r"^[A-Za-z0-9._-]{1,128}$")
        self.assertRegex(payload["guardian_version"], r"^[A-Za-z0-9.+_-]{1,64}$")
        self.assertRegex(payload["antivirus"], r"^[\w .,_\-/()+]{1,128}$")
        if payload["hostname"]:
            self.assertRegex(payload["hostname"], r"^[A-Za-z0-9._-]{1,255}$")
        self.assertLessEqual(len(payload["components"]), 50)
        for name, component in payload["components"].items():
            self.assertRegex(name, r"^[a-z0-9_]{1,64}$")
            self.assertIn(component["status"], guardian.REPORTABLE_STATUSES)
            self.assertNotEqual(component["status"], "offline")
            if component["version"]:
                self.assertRegex(component["version"], r"^[A-Za-z0-9.+_-]{1,64}$")

    def test_payload_fits_body_limit(self) -> None:
        components = {f"comp_{i}": {"status": "healthy", "version": "1.0.0"} for i in range(50)}
        body = json.dumps(guardian.build_heartbeat("m", components, "Microsoft Defender")).encode()
        self.assertLess(len(body), 65536)


class ApiResilienceTests(unittest.TestCase):
    CONFIG = dict(ConfigurationTests.BASE)

    def test_retries_transient_failure_then_succeeds(self) -> None:
        client = guardian.ApiClient(self.CONFIG)
        response = mock.MagicMock()
        response.status = 202
        response.__enter__ = mock.Mock(return_value=response)
        response.__exit__ = mock.Mock(return_value=False)
        attempts = {"n": 0}

        def flaky(_request, timeout=0):
            attempts["n"] += 1
            if attempts["n"] < 3:
                raise guardian.URLError("rede caiu")
            return response

        slept: list[float] = []
        with mock.patch.object(client.opener, "open", side_effect=flaky):
            client.send_heartbeat({"machine_id": "m"}, sleep=slept.append)

        self.assertEqual(attempts["n"], 3)
        self.assertEqual(slept, list(guardian.API_RETRY_DELAYS_SECONDS[:2]))

    def test_api_offline_raises_guardian_error_after_backoff(self) -> None:
        client = guardian.ApiClient(self.CONFIG)
        with mock.patch.object(client.opener, "open", side_effect=guardian.URLError("offline")):
            with self.assertRaises(guardian.GuardianError):
                client.send_heartbeat({"machine_id": "m"}, sleep=lambda _s: None)

    def test_api_offline_does_not_break_the_cycle(self) -> None:
        """Servidor fora do ar: o ciclo registra e segue; o servico nao cai."""
        runtime = guardian.GuardianRuntime()
        failing = mock.Mock()
        failing.send_heartbeat.side_effect = guardian.GuardianError("timeout")
        with mock.patch.object(guardian, "collect_components", return_value={}), \
             mock.patch.object(guardian, "detect_antivirus", return_value="Microsoft Defender"), \
             mock.patch.object(guardian, "load_json", return_value=dict(self.CONFIG)), \
             mock.patch.object(guardian, "ApiClient", return_value=failing):
            runtime.run_cycle(quiet_logger(), "maquina-1")  # nao deve levantar

    def test_invalid_configuration_does_not_break_the_cycle(self) -> None:
        runtime = guardian.GuardianRuntime()
        with mock.patch.object(guardian, "collect_components", return_value={}), \
             mock.patch.object(guardian, "detect_antivirus", return_value="unknown"), \
             mock.patch.object(guardian, "load_json", side_effect=guardian.GuardianError("sem config")):
            runtime.run_cycle(quiet_logger(), "maquina-1")  # nao deve levantar

    def test_token_never_appears_in_url(self) -> None:
        client = guardian.ApiClient(self.CONFIG)
        captured: list[str] = []

        def capture(request, timeout=0):
            captured.append(request.full_url)
            raise guardian.URLError("parar aqui")

        with mock.patch.object(client.opener, "open", side_effect=capture):
            with self.assertRaises(guardian.GuardianError):
                client.send_heartbeat({"machine_id": "m"}, sleep=lambda _s: None)

        self.assertTrue(captured)
        for url in captured:
            self.assertNotIn(self.CONFIG["api_token"], url)
            self.assertNotIn("?", url)


if __name__ == "__main__":
    unittest.main(verbosity=2)


class ActionSecurityTests(unittest.TestCase):
    """O servidor manda so (componente, acao); o mapeamento mora aqui."""

    def test_unknown_action_is_refused(self) -> None:
        ok, message = guardian.execute_action("updater", "RUN_POWERSHELL", quiet_logger())
        self.assertFalse(ok)
        self.assertIn("nao permitida", message)

    def test_unknown_component_is_refused(self) -> None:
        ok, message = guardian.execute_action("qualquer_coisa", guardian.ACTION_START, quiet_logger())
        self.assertFalse(ok)
        self.assertIn("desconhecido", message)

    def test_reinstall_is_not_supported_yet(self) -> None:
        ok, _ = guardian.execute_action("updater", "REINSTALL_COMPONENT", quiet_logger())
        self.assertFalse(ok)

    def test_server_cannot_choose_the_service_name(self) -> None:
        """Nao ha caminho de codigo que aceite nome de servico vindo da API."""
        for component in guardian.COMPONENT_SERVICES:
            self.assertIsInstance(guardian.COMPONENT_SERVICES[component], tuple)
        self.assertNotIn("wallpaper", guardian.COMPONENT_SERVICES)

    def test_wallpaper_accepts_only_check(self) -> None:
        with mock.patch.object(guardian, "check_wallpaper", return_value={"status": "healthy", "version": ""}):
            ok, _ = guardian.execute_action("wallpaper", guardian.ACTION_CHECK, quiet_logger())
        self.assertTrue(ok)
        ok, message = guardian.execute_action("wallpaper", guardian.ACTION_START, quiet_logger())
        self.assertFalse(ok)
        self.assertIn("nao e um servico", message)


class ActionExecutionTests(unittest.TestCase):
    def test_start_when_already_running_is_a_noop_success(self) -> None:
        with mock.patch.object(guardian, "resolve_component_service", return_value="AtivaUnifiedUpdater"), \
             mock.patch.object(guardian, "query_service", return_value=guardian.SERVICE_STATE_RUNNING), \
             mock.patch.object(guardian, "run_sc") as run_sc:
            ok, message = guardian.execute_action("updater", guardian.ACTION_START, quiet_logger())
        self.assertTrue(ok)
        run_sc.assert_not_called()
        self.assertIn("ja estava em execucao", message)

    def test_start_stopped_service_validates_running(self) -> None:
        states = [guardian.SERVICE_STATE_STOPPED, guardian.SERVICE_STATE_RUNNING]
        with mock.patch.object(guardian, "resolve_component_service", return_value="AtivaUnifiedUpdater"), \
             mock.patch.object(guardian, "query_service", side_effect=lambda _n: states.pop(0) if states else guardian.SERVICE_STATE_RUNNING), \
             mock.patch.object(guardian, "run_sc", return_value=0) as run_sc:
            ok, _ = guardian.execute_action("updater", guardian.ACTION_START, quiet_logger(), sleep=lambda _s: None, timeout=1)
        self.assertTrue(ok)
        run_sc.assert_any_call("start", "AtivaUnifiedUpdater")

    def test_restart_stops_before_starting(self) -> None:
        calls: list[tuple] = []
        states = iter([guardian.SERVICE_STATE_STOPPED, guardian.SERVICE_STATE_RUNNING])
        with mock.patch.object(guardian, "resolve_component_service", return_value="RustDesk"), \
             mock.patch.object(guardian, "query_service", side_effect=lambda _n: next(states, guardian.SERVICE_STATE_RUNNING)), \
             mock.patch.object(guardian, "run_sc", side_effect=lambda *a: calls.append(a) or 0):
            ok, _ = guardian.execute_action("remote", guardian.ACTION_RESTART, quiet_logger(), sleep=lambda _s: None, timeout=1)
        self.assertTrue(ok)
        self.assertEqual(calls[0], ("stop", "RustDesk"))
        self.assertEqual(calls[1], ("start", "RustDesk"))

    def test_failure_to_come_back_is_reported_not_raised(self) -> None:
        with mock.patch.object(guardian, "resolve_component_service", return_value="AtivaUnifiedUpdater"), \
             mock.patch.object(guardian, "query_service", return_value=guardian.SERVICE_STATE_STOPPED), \
             mock.patch.object(guardian, "run_sc", return_value=0):
            ok, message = guardian.execute_action("updater", guardian.ACTION_START, quiet_logger(), sleep=lambda _s: None, timeout=1)
        self.assertFalse(ok)
        self.assertIn("nao ficou em execucao", message)

    def test_exception_inside_action_becomes_failure(self) -> None:
        with mock.patch.object(guardian, "resolve_component_service", side_effect=OSError("SCM fora")):
            ok, message = guardian.execute_action("updater", guardian.ACTION_RESTART, quiet_logger())
        self.assertFalse(ok)
        self.assertIn("SCM fora", message)


class ActionPollingTests(unittest.TestCase):
    CONFIG = dict(ConfigurationTests.BASE)

    def test_api_offline_during_action_poll_is_silent(self) -> None:
        runtime = guardian.GuardianRuntime()
        failing = mock.Mock()
        failing.fetch_actions.side_effect = guardian.GuardianError("offline")
        with mock.patch.object(guardian, "load_json", return_value=dict(self.CONFIG)), \
             mock.patch.object(guardian, "ApiClient", return_value=failing):
            runtime.run_actions(quiet_logger(), "maquina-1")  # nao deve levantar

    def test_result_is_reported_back(self) -> None:
        runtime = guardian.GuardianRuntime()
        api = mock.Mock()
        api.fetch_actions.return_value = [{"id": 15, "component": "updater", "action": "START_COMPONENT"}]
        with mock.patch.object(guardian, "load_json", return_value=dict(self.CONFIG)), \
             mock.patch.object(guardian, "ApiClient", return_value=api), \
             mock.patch.object(guardian, "execute_action", return_value=(True, "ok")):
            runtime.run_actions(quiet_logger(), "maquina-1")
        api.report_action.assert_called_once_with(15, "maquina-1", True, "ok")

    def test_malformed_action_is_skipped(self) -> None:
        runtime = guardian.GuardianRuntime()
        api = mock.Mock()
        api.fetch_actions.return_value = ["texto", {"id": 0}, {"component": "updater"}]
        with mock.patch.object(guardian, "load_json", return_value=dict(self.CONFIG)), \
             mock.patch.object(guardian, "ApiClient", return_value=api):
            runtime.run_actions(quiet_logger(), "maquina-1")
        api.report_action.assert_not_called()
