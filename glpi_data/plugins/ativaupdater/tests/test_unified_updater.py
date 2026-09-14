from __future__ import annotations

import codecs
import importlib.util
import json
import logging
from pathlib import Path
import subprocess
import tempfile
import time
import unittest
from unittest import mock


MODULE_PATH = Path(__file__).parents[1] / "client" / "unified_updater_service.py"
SPEC = importlib.util.spec_from_file_location("unified_updater_service", MODULE_PATH)
assert SPEC is not None and SPEC.loader is not None
updater = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(updater)

API_URL = "https://chamados.ativalocacao.com.br:8443/plugins/ativaupdater/api/v1"
SHA = "b" * 64


def quiet_logger(name: str) -> logging.Logger:
    logger = logging.getLogger(name)
    logger.addHandler(logging.NullHandler())
    logger.propagate = False
    return logger


class VersionTests(unittest.TestCase):
    def test_updater_version_is_valid(self) -> None:
        self.assertEqual(updater.UPDATER_VERSION, "1.4.0")
        self.assertEqual(updater.version_tuple(updater.UPDATER_VERSION), (1, 4, 0))
        self.assertEqual(updater.COMMAND_POLL_SECONDS, 15)

    def test_semantic_version_comparison(self) -> None:
        self.assertLess(updater.version_tuple("1.4.3"), updater.version_tuple("1.4.4"))
        self.assertGreater(updater.version_tuple("2.0.0"), updater.version_tuple("1.99.99"))

    def test_rejects_non_strict_version(self) -> None:
        for value in ("1.4", "v1.4.4", "1.4.4-beta", "1.4.4.1"):
            with self.subTest(value=value), self.assertRaises(updater.UpdaterError):
                updater.version_tuple(value)


class DecideActionTests(unittest.TestCase):
    def test_action_matrix(self) -> None:
        cases = [
            ("1.6.0", "1.6.1", False, updater.ACTION_UPGRADE),
            ("1.6.1", "1.6.1", True, updater.ACTION_CURRENT),
            ("1.6.1", "1.6.0", False, updater.ACTION_BLOCKED_DOWNGRADE),
            ("1.6.1", "1.6.0", True, updater.ACTION_DOWNGRADE),
            ("1.10.0", "1.9.0", True, updater.ACTION_DOWNGRADE),
            ("1.6.0", "1.5.1", True, updater.ACTION_BLOCKED_DOWNGRADE),
            ("invalid", "1.6.0", False, updater.ACTION_UPGRADE),
        ]
        for installed, available, allow, expected in cases:
            with self.subTest(installed=installed, available=available, allow=allow):
                self.assertEqual(updater.decide_action(installed, available, allow), expected)


class FakeHttpResponse:
    def __init__(self, payload: dict) -> None:
        self.body = json.dumps(payload).encode("utf-8")
        self.headers = {"Content-Type": "application/json"}

    def __enter__(self):
        return self

    def __exit__(self, *_args) -> None:
        return None

    def read(self, _size: int = -1) -> bytes:
        return self.body


class LatestParsingTests(unittest.TestCase):
    def latest_with(self, **extra) -> dict:
        payload = {
            "version": "1.6.0",
            "sha256": SHA,
            "size": 4096,
            "download_url": API_URL + "/download/1.6.0",
            **extra,
        }
        api = updater.ApiClient({"api_url": API_URL, "api_token": "a" * 64})
        with mock.patch.object(api, "_request", return_value=FakeHttpResponse(payload)):
            return api.latest()

    def test_allow_downgrade_requires_a_real_boolean(self) -> None:
        self.assertFalse(self.latest_with()["allow_downgrade"])
        self.assertFalse(self.latest_with(allow_downgrade="true")["allow_downgrade"])
        self.assertFalse(self.latest_with(allow_downgrade=1)["allow_downgrade"])
        self.assertTrue(self.latest_with(allow_downgrade=True)["allow_downgrade"])


class FailureTrackingTests(unittest.TestCase):
    def pending(self, state: dict, version: str = "1.6.0", sha256: str = SHA) -> dict:
        state.update({"pending_version": version, "pending_sha256": sha256, "pending_started_at": 1.0})
        return state

    def test_first_attempt_plus_three_retries_then_daily(self) -> None:
        state = self.pending({})
        results = []
        for _ in range(updater.TOTAL_INSTALL_ATTEMPTS):
            results.append(updater.register_install_failure(state, 1000.0))
            self.assertNotIn("pending_version", state)
            self.pending(state)
        self.assertEqual(results, [
            (1, 60, False),
            (2, 300, False),
            (3, 900, False),
            (4, updater.FAILED_RETRY_SECONDS, True),
        ])

    def test_another_package_starts_a_new_cycle(self) -> None:
        state = {"failed_version": "1.6.0", "failed_sha256": SHA, "failed_attempts": 4}
        self.pending(state, "1.6.1", "c" * 64)
        self.assertEqual(updater.register_install_failure(state, 1000.0), (1, 60, False))

    def test_block_reason_statuses(self) -> None:
        release = {"version": "1.6.0", "sha256": SHA}
        state = {"failed_version": "1.6.0", "failed_sha256": SHA, "failed_attempts": 1, "retry_after": 1060.0}
        status, message = updater.install_block_reason(state, release, 1000.0)
        self.assertEqual(status, updater.STATUS_RETRYING)
        self.assertIn("nova tentativa (1 de 3)", message)
        self.assertIsNone(updater.install_block_reason(state, release, 1060.0))
        self.assertIsNone(updater.install_block_reason(state, {"version": "1.6.0", "sha256": "c" * 64}, 1000.0))

        state.update({"failed_attempts": 4, "retry_after": 1000.0 + updater.FAILED_RETRY_SECONDS})
        status, message = updater.install_block_reason(state, release, 1000.0)
        self.assertEqual(status, updater.STATUS_INSTALL_FAILED)
        self.assertIn("apos 4 tentativas", message)

    def test_next_check_honours_scheduled_retry(self) -> None:
        state = {"check_interval_seconds": 3600}
        self.assertEqual(updater.next_check_delay(0, state, 1000.0), 3600)
        self.assertEqual(updater.next_check_delay(2, state, 1000.0), 300)
        state["retry_after"] = 1060.0
        self.assertEqual(updater.next_check_delay(2, state, 1000.0), 61)
        state["retry_after"] = 1000.0 + updater.FAILED_RETRY_SECONDS
        self.assertEqual(updater.next_check_delay(0, state, 1000.0), 3600)

    def test_outcome_descriptions(self) -> None:
        self.assertIn("erro fatal durante a instalacao", updater.describe_outcome("exited", 4))
        self.assertIn("20 minutos", updater.describe_outcome("timeout", None))
        self.assertIn("sem concluir", updater.describe_outcome("missing", None))


class ProcessAndLogTests(unittest.TestCase):
    def test_parse_setup_processes(self) -> None:
        output = (
            '"System Idle Process","0","Services","0","8 K"\n'
            '"Ativa-Unified-Agent-Setup-1.6.0.exe","4242","Services","0","3.100 K"\n'
            '"Ativa-Unified-Agent-Setup-1.6.0.tmp","4243","Services","0","9.800 K"\n'
            '"AtivaUnifiedUpdater.exe","900","Services","0","12.000 K"\n'
        )
        self.assertEqual(updater.parse_setup_processes(output), [4242, 4243])
        self.assertEqual(updater.parse_setup_processes("INFORMACOES: nenhuma tarefa em execucao.\n"), [])

    def test_read_log_tail_decodes_utf8_and_utf16(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            utf8 = Path(directory) / "installer.log"
            utf8.write_text("linha 1\nInstalação falhou\n", encoding="utf-8")
            self.assertEqual(updater.read_log_tail(utf8, 1), ["Instalação falhou"])

            utf16 = Path(directory) / "msi.log"
            utf16.write_bytes(codecs.BOM_UTF16_LE + "Action ended: Return value 3.\r\nfim\r\n".encode("utf-16-le"))
            self.assertEqual(updater.read_log_tail(utf16, 5), ["Action ended: Return value 3.", "fim"])
            # An odd offset must be moved to a UTF-16 character boundary.
            self.assertEqual(updater.read_log_tail(utf16, 5, max_bytes=11), ["fim"])

    def test_collect_install_log_includes_every_source(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            logs = root / "logs"
            wallpaper_logs = root / "wallpaper"
            logs.mkdir()
            wallpaper_logs.mkdir()
            installer_log = logs / "installer-1.log"
            installer_log.write_text("Instalando o GLPI Agent\nA instalacao do GLPI Agent falhou. Codigo de saida: 1603\n", encoding="utf-8")
            (logs / "glpi-agent-msi.log").write_bytes(
                codecs.BOM_UTF16_LE + "MSI (s) Note: 1: 1708\r\nAction ended: InstallFinalize. Return value 3.\r\n".encode("utf-16-le")
            )
            (wallpaper_logs / "client-abc.log").write_text("REGISTRATION_FAILED invalid secret\n", encoding="utf-8")
            with mock.patch.object(updater, "LOG_DIR", logs), \
                    mock.patch.object(updater, "MSI_LOG_PATH", logs / "glpi-agent-msi.log"), \
                    mock.patch.object(updater, "WALLPAPER_LOG_DIR", wallpaper_logs):
                text = updater.collect_install_log("1.6.0", 2, "exited", 4, time.time() - 30, installer_log)
        self.assertIn("tentativa 2 de 4", text)
        self.assertIn("codigo de saida 4", text)
        self.assertIn("Codigo de saida: 1603", text)
        self.assertIn("Return value 3", text)
        self.assertIn("REGISTRATION_FAILED", text)

    def test_collect_install_log_is_truncated(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            logs = Path(directory)
            installer_log = logs / "installer-1.log"
            installer_log.write_text("\n".join("x" * 400 for _ in range(150)), encoding="utf-8")
            with mock.patch.object(updater, "LOG_DIR", logs), \
                    mock.patch.object(updater, "MSI_LOG_PATH", logs / "missing.log"), \
                    mock.patch.object(updater, "WALLPAPER_LOG_DIR", logs / "missing"):
                text = updater.collect_install_log("1.6.0", 1, "timeout", None, time.time(), installer_log)
        self.assertLessEqual(len(text), updater.INSTALL_LOG_MAX_CHARS)
        self.assertTrue(text.startswith("Ativa Unified Updater"))


class FakeProcess:
    def __init__(self, exit_code: int | None = None, pid: int = 4242) -> None:
        self.exit_code = exit_code
        self.pid = pid
        self.waits = 0

    def poll(self) -> int | None:
        return self.exit_code

    def wait(self, timeout: float | None = None) -> int:
        self.waits += 1
        if self.exit_code is None:
            raise subprocess.TimeoutExpired("setup", timeout)
        return self.exit_code


class SuperviseInstallerTests(unittest.TestCase):
    def test_exit_code_is_returned(self) -> None:
        self.assertEqual(updater.supervise_installer(FakeProcess(4), lambda: False), ("exited", 4))

    def test_service_stop_does_not_kill_the_installer(self) -> None:
        self.assertEqual(updater.supervise_installer(FakeProcess(None), lambda: True), ("stopping", None))

    def test_timeout(self) -> None:
        now = [0.0]

        def clock() -> float:
            now[0] += 400
            return now[0]

        process = FakeProcess(None)
        self.assertEqual(
            updater.supervise_installer(process, lambda: False, timeout_seconds=1200, poll_seconds=0, clock=clock),
            ("timeout", None),
        )
        self.assertGreater(process.waits, 0)


class ComponentDetectionTests(unittest.TestCase):
    def test_glpi_agent_display_name(self) -> None:
        for name in ("GLPI Agent", "GLPI Agent 1.19", "glpi agent 1.19.1 (x64)", " GLPI Agent v1.20-git "):
            with self.subTest(name=name):
                self.assertTrue(updater.is_glpi_agent_display_name(name))
        for name in ("GLPI Agent Monitor", "GLPI Agent Monitor 1.3", "FusionInventory Agent 2.6", "GLPI"):
            with self.subTest(name=name):
                self.assertFalse(updater.is_glpi_agent_display_name(name))


class WallpaperClientStartTests(unittest.TestCase):
    def setUp(self) -> None:
        self.logger = quiet_logger("ativaupdater-session-tests")

    def test_only_user_sessions_are_selected(self) -> None:
        sessions = [(0, updater.WTS_DISCONNECTED), (1, updater.WTS_ACTIVE), (2, updater.WTS_DISCONNECTED), (3, 6)]
        self.assertEqual(updater.select_user_sessions(sessions), [1, 2])

    def test_outside_session_zero_asks_installer_to_fall_back(self) -> None:
        with mock.patch.object(updater, "current_session_id", return_value=1), \
                mock.patch.object(updater, "enumerate_sessions") as enumerate_sessions:
            self.assertEqual(updater.start_wallpaper_clients(self.logger), updater.NOT_SESSION_ZERO_EXIT_CODE)
        enumerate_sessions.assert_not_called()

    def test_system_starts_client_in_each_user_session(self) -> None:
        launched: list[int] = []

        def launch(session_id: int, _executable: Path) -> None:
            if session_id == 2:
                raise OSError("session logging off")
            launched.append(session_id)

        with tempfile.TemporaryDirectory() as directory:
            client = Path(directory) / "AtivaWallpaperClient.exe"
            client.write_bytes(b"MZ")
            with mock.patch.object(updater, "current_session_id", return_value=0), \
                    mock.patch.object(updater, "WALLPAPER_CLIENT_PATH", client), \
                    mock.patch.object(updater, "enumerate_sessions", return_value=[(1, 0), (2, 0), (3, 4)]), \
                    mock.patch.object(updater, "launch_in_session", side_effect=launch):
                self.assertEqual(updater.start_wallpaper_clients(self.logger), 0)
        self.assertEqual(launched, [1, 3])

    @unittest.skipUnless(updater.os.name == "nt", "Windows session APIs")
    def test_windows_session_apis_are_callable(self) -> None:
        current = updater.current_session_id()
        self.assertIsNotNone(current)
        self.assertIn(current, [session_id for session_id, _ in updater.enumerate_sessions()])


class FakeApi:
    release: dict = {}
    instances: list["FakeApi"] = []

    def __init__(self, _config: dict) -> None:
        self.reports: list[dict] = []
        FakeApi.instances.append(self)

    def report(self, status: str, installed: str, available: str = "", message: str = "", install_log: str | None = None) -> None:
        self.reports.append({
            "status": status, "installed": installed, "available": available,
            "message": message, "install_log": install_log,
        })

    def latest(self) -> dict:
        return dict(FakeApi.release)

    def download(self, _release: dict, destination: Path) -> None:
        destination.write_bytes(b"MZ")


class CheckOnceTests(unittest.TestCase):
    def setUp(self) -> None:
        directory = tempfile.TemporaryDirectory()
        self.addCleanup(directory.cleanup)
        root = Path(directory.name)
        self.root = root
        self.state_path = root / "state.json"
        self.log_dir = root / "logs"
        self.log_dir.mkdir()
        config_path = root / "service-config.json"
        config_path.write_text(json.dumps({
            "api_url": API_URL,
            "api_token": "a" * 64,
            "verify_tls": True,
            "check_interval_seconds": 3600,
        }), encoding="utf-8")
        self.launched: list[Path] = []
        self.killed: list[int] = []
        self.setup_processes: list[int] = []
        # What the fake installer does when launched: (exit_code, state written by --configure).
        self.installer_exit_code: int | None = 0
        self.installer_configures: bool = True
        FakeApi.instances = []
        for name, value in {
            "CONFIG_PATH": config_path,
            "STATE_PATH": self.state_path,
            "DOWNLOAD_DIR": root / "downloads",
            "LOG_DIR": self.log_dir,
            "MSI_LOG_PATH": self.log_dir / "glpi-agent-msi.log",
            "WALLPAPER_LOG_DIR": root / "wallpaper-logs",
            "INSTALL_FAILURE_LOG_PATH": self.log_dir / "install-failure.log",
            "ApiClient": FakeApi,
            "launch_installer": self.fake_launch,
            "running_setup_processes": lambda: list(self.setup_processes),
            "kill_process_tree": self.killed.append,
        }.items():
            patcher = mock.patch.object(updater, name, value)
            patcher.start()
            self.addCleanup(patcher.stop)
        self.logger = quiet_logger("ativaupdater-tests")

    def fake_launch(self, path: Path, _logger: logging.Logger):
        self.launched.append(path)
        installer_log = self.log_dir / f"installer-{len(self.launched)}.log"
        installer_log.write_text("Instalando o GLPI Agent\nA instalacao do GLPI Agent falhou. Codigo de saida: 1603\n", encoding="utf-8")
        if self.installer_configures:
            state = self.state()
            state.update({"installed_version": FakeApi.release["version"], "last_result": "installed"})
            for key in ("pending_version", "pending_sha256", "pending_action", "pending_started_at",
                        "failed_version", "failed_sha256", "failed_attempts", "retry_after"):
                state.pop(key, None)
            self.write_state(**state)
        return FakeProcess(self.installer_exit_code), installer_log

    def publish(self, version: str, allow_downgrade: bool = False) -> None:
        FakeApi.release = {
            "version": version,
            "sha256": SHA,
            "size": 2,
            "download_url": API_URL + "/download/" + version,
            "check_interval_seconds": 3600,
            "allow_downgrade": allow_downgrade,
        }

    def write_state(self, **state) -> None:
        self.state_path.write_text(json.dumps(state), encoding="utf-8")

    def state(self) -> dict:
        return json.loads(self.state_path.read_text(encoding="utf-8"))

    def last_report(self) -> dict:
        return FakeApi.instances[-1].reports[-1]

    def fail_installer(self, exit_code: int | None = 4) -> None:
        self.installer_exit_code = exit_code
        self.installer_configures = False

    # Normal flow ---------------------------------------------------------

    def test_successful_supervised_install_reports_updated(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(len(self.launched), 1)
        report = self.last_report()
        self.assertEqual((report["status"], report["installed"]), ("updated", "1.6.0"))
        self.assertIn("tentativa 1 de 4", FakeApi.instances[-1].reports[-2]["message"])

    def test_installer_restarting_the_service_ends_supervision(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.installer_exit_code = None
        self.installer_configures = False
        self.assertEqual(updater.check_once(self.logger, stop_requested=lambda: True), 10)
        self.assertEqual(self.killed, [])
        self.assertEqual(self.state()["pending_version"], "1.6.0")

    def test_newer_machine_stays_when_downgrade_is_not_authorized(self) -> None:
        self.write_state(installed_version="1.6.1")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.launched, [])
        report = self.last_report()
        self.assertEqual(report["status"], "current")
        self.assertIn("downgrade nao autorizado", report["message"])

    def test_authorized_rollback_installs_older_package(self) -> None:
        self.write_state(installed_version="1.6.1")
        self.publish("1.6.0", allow_downgrade=True)
        self.installer_exit_code = None
        self.installer_configures = False
        self.assertEqual(updater.check_once(self.logger, stop_requested=lambda: True), 10)
        self.assertEqual([path.name for path in self.launched], ["Ativa-Unified-Agent-Setup-1.6.0.exe"])
        state = self.state()
        self.assertEqual((state["pending_version"], state["pending_action"]), ("1.6.0", updater.ACTION_DOWNGRADE))
        self.assertIn("Rollback", self.last_report()["message"])

    def test_rollback_below_minimum_package_is_refused(self) -> None:
        self.write_state(installed_version="1.6.0")
        self.publish("1.5.1", allow_downgrade=True)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.launched, [])

    # Failures, retries and "Falha na Instalacao" -------------------------

    def test_failed_attempt_is_retried_with_the_installation_log(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.fail_installer(4)
        before = time.time()
        self.assertEqual(updater.check_once(self.logger), 2)

        report = self.last_report()
        self.assertEqual(report["status"], updater.STATUS_RETRYING)
        self.assertIn("Falha na tentativa 1 de 4", report["message"])
        self.assertIn("Nova tentativa (1 de 3) em 60 segundos", report["message"])
        self.assertIn("codigo de saida 4", report["install_log"])
        self.assertIn("Codigo de saida: 1603", report["install_log"])
        self.assertTrue(updater.INSTALL_FAILURE_LOG_PATH.is_file())
        state = self.state()
        self.assertEqual(state["failed_attempts"], 1)
        self.assertAlmostEqual(state["retry_after"], before + 60, delta=5)
        self.assertNotIn("pending_version", state)

        # Before the retry delay the package is not reinstalled.
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(len(self.launched), 1)
        self.assertEqual(self.last_report()["status"], updater.STATUS_RETRYING)

    def test_after_three_retries_status_is_install_failed(self) -> None:
        self.write_state(installed_version="1.5.1", failed_version="1.6.0", failed_sha256=SHA, failed_attempts=3, retry_after=0)
        self.publish("1.6.0")
        self.fail_installer(1)
        self.assertEqual(updater.check_once(self.logger), 0)
        report = self.last_report()
        self.assertEqual(report["status"], updater.STATUS_INSTALL_FAILED)
        self.assertIn("apos 4 tentativas", report["message"])
        self.assertIn("tentativa 4 de 4", report["install_log"])

        # Stays failed without reinstalling until the daily retry...
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(len(self.launched), 1)
        self.assertEqual(self.last_report()["status"], updater.STATUS_INSTALL_FAILED)

        # ...or until "Verificar agora" starts a new cycle.
        self.installer_exit_code = 0
        self.installer_configures = True
        self.assertEqual(updater.check_once(self.logger, manual=True), 0)
        self.assertEqual(len(self.launched), 2)
        self.assertEqual(self.last_report()["status"], "updated")

    def test_hung_installer_is_killed_and_counted(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.fail_installer(None)
        self.setup_processes = [4243]
        with mock.patch.object(updater, "supervise_installer", return_value=("timeout", None)):
            self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(self.killed, [4242, 4243])
        report = self.last_report()
        self.assertEqual(report["status"], updater.STATUS_RETRYING)
        self.assertIn("excedeu 20 minutos", report["message"])

    def test_exit_zero_without_configuration_is_a_failure(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.fail_installer(0)
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertIn("sem registrar a nova versao", self.last_report()["message"])

    def test_pending_installation_whose_installer_disappeared_fails_immediately(self) -> None:
        # The situation seen in the field: the installer died, the service was
        # restarted and the dashboard kept showing "Instalando".
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_action=updater.ACTION_UPGRADE, pending_started_at=time.time() - 60)
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(self.launched, [])
        report = self.last_report()
        self.assertEqual(report["status"], updater.STATUS_RETRYING)
        self.assertIn("encerrado sem concluir", report["message"])
        self.assertEqual(self.state()["failed_attempts"], 1)

    def test_pending_installation_still_running_is_left_alone(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_started_at=time.time() - 60)
        self.publish("1.6.0")
        self.setup_processes = [4242]
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual((self.killed, self.launched), ([], []))
        self.assertEqual(self.last_report()["status"], "installing")

    def test_pending_installation_running_past_timeout_is_killed(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_started_at=time.time() - updater.INSTALL_TIMEOUT_SECONDS - 5)
        self.publish("1.6.0")
        self.setup_processes = [4242]
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(self.killed, [4242])
        self.assertIn("excedeu", self.last_report()["message"])

    # Other behaviour -----------------------------------------------------

    def test_reaching_the_published_version_clears_failures(self) -> None:
        self.write_state(installed_version="1.6.0", failed_version="1.6.0", failed_sha256=SHA, failed_attempts=2)
        self.publish("1.6.0", allow_downgrade=True)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertNotIn("failed_attempts", self.state())

    def test_first_check_after_installation_reports_updated_once(self) -> None:
        self.write_state(installed_version="1.6.0", last_result="installed")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 0)
        report = self.last_report()
        self.assertEqual((report["status"], report["installed"]), ("updated", "1.6.0"))
        self.assertIn("instalada com sucesso", report["message"])
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.last_report()["status"], "current")

    def test_invalid_installed_version_reinstalls_instead_of_stopping(self) -> None:
        self.write_state(installed_version="corrompido")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(len(self.launched), 1)

    def test_invalid_configuration_is_retried_later(self) -> None:
        updater.CONFIG_PATH.write_text("{not json", encoding="utf-8")
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(FakeApi.instances, [])

    def test_previous_installers_are_removed(self) -> None:
        download_dir = updater.DOWNLOAD_DIR
        download_dir.mkdir(parents=True)
        old = download_dir / "Ativa-Unified-Agent-Setup-1.5.1.exe"
        old.write_bytes(b"MZ")
        self.write_state(installed_version="1.6.0")
        self.publish("1.6.1")
        self.installer_exit_code = None
        self.installer_configures = False
        self.assertEqual(updater.check_once(self.logger, stop_requested=lambda: True), 10)
        self.assertFalse(old.exists())
        self.assertTrue((download_dir / "Ativa-Unified-Agent-Setup-1.6.1.exe").exists())

        self.write_state(installed_version="1.6.1", last_result="installed")
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(list(download_dir.iterdir()), [])


class ServiceRuntimeTests(unittest.TestCase):
    def setUp(self) -> None:
        self.logger = quiet_logger("ativaupdater-runtime-tests")

    def run_once_with(self, check_once) -> tuple[updater.ServiceRuntime, list[float], mock.MagicMock]:
        runtime = updater.ServiceRuntime()
        waits: list[float] = []

        def wait(seconds: float) -> bool:
            waits.append(seconds)
            runtime.stop_event.set()
            return True

        runtime.stop_event.wait = wait  # type: ignore[method-assign]
        with mock.patch.object(updater, "check_once", side_effect=check_once) as patched, \
                mock.patch.object(updater, "cleanup_replaced_binaries"), \
                mock.patch.object(updater, "get_state", return_value={"check_interval_seconds": 3600}):
            runtime.run(self.logger)
        return runtime, waits, patched

    def test_unexpected_error_does_not_end_the_service(self) -> None:
        _, waits, _ = self.run_once_with(RuntimeError("boom"))
        self.assertEqual(len(waits), 1)

    def test_check_receives_the_stop_signal_and_ends_when_stopped(self) -> None:
        runtime_holder: list[updater.ServiceRuntime] = []

        def check(_logger, manual=False, stop_requested=None):
            self.assertIsNotNone(stop_requested)
            runtime_holder[0].stop_event.set()
            self.assertTrue(stop_requested())
            return 10

        runtime = updater.ServiceRuntime()
        runtime_holder.append(runtime)
        waits: list[float] = []
        runtime.stop_event.wait = lambda seconds: waits.append(seconds) or True  # type: ignore[method-assign]
        with mock.patch.object(updater, "check_once", side_effect=check), \
                mock.patch.object(updater, "cleanup_replaced_binaries"):
            runtime.run(self.logger)
        self.assertEqual(waits, [])


class ConfigTests(unittest.TestCase):
    def valid(self) -> dict:
        return {
            "api_url": API_URL,
            "api_token": "a" * 64,
            "verify_tls": True,
            "check_interval_seconds": 3600,
        }

    def test_accepts_production_config(self) -> None:
        self.assertEqual(updater.validate_config(self.valid())["check_interval_seconds"], 3600)

    def test_rejects_http_or_disabled_tls(self) -> None:
        config = self.valid()
        config["api_url"] = config["api_url"].replace("https://", "http://")
        with self.assertRaises(updater.UpdaterError):
            updater.validate_config(config)
        config = self.valid()
        config["verify_tls"] = False
        with self.assertRaises(updater.UpdaterError):
            updater.validate_config(config)

    def test_rejects_invalid_token_and_interval(self) -> None:
        config = self.valid()
        config["api_token"] = "short"
        with self.assertRaises(updater.UpdaterError):
            updater.validate_config(config)
        config = self.valid()
        config["check_interval_seconds"] = 60
        with self.assertRaises(updater.UpdaterError):
            updater.validate_config(config)


class ComponentVersionTests(unittest.TestCase):
    def test_non_windows_glpi_agent_version_is_empty(self) -> None:
        if updater.os.name != "nt":
            self.assertEqual(updater.glpi_agent_version(), "")


if __name__ == "__main__":
    unittest.main()
