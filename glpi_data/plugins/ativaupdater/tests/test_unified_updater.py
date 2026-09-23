from __future__ import annotations

import codecs
import importlib.util
import io
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


class GuardianMaintenanceTests(unittest.TestCase):
    def test_watchdog_respects_bounded_lease(self):
        with mock.patch.object(updater, "load_json", return_value={"until": 1500}), \
             mock.patch.object(updater.time, "time", return_value=1000):
            self.assertTrue(updater.guardian_maintenance_active())
            with mock.patch.object(updater, "query_service") as query:
                self.assertEqual(updater.run_watchdog(quiet_logger("maintenance-watchdog")), 0)
                query.assert_not_called()

    def test_expired_missing_or_invalid_lease_does_not_disable_watchdog(self):
        with mock.patch.object(updater.time, "time", return_value=1000):
            for until in (0, 999, 999999, "invalid"):
                with mock.patch.object(updater, "load_json", return_value={"until": until}):
                    self.assertFalse(updater.guardian_maintenance_active())
            with mock.patch.object(updater, "load_json", side_effect=FileNotFoundError):
                self.assertFalse(updater.guardian_maintenance_active())


class VersionTests(unittest.TestCase):
    def test_updater_version_is_valid(self) -> None:
        self.assertEqual(updater.UPDATER_VERSION, "1.7.8")
        self.assertEqual(updater.version_tuple(updater.UPDATER_VERSION), (1, 7, 8))
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

    def test_pending_installation_is_checked_again_soon(self) -> None:
        # The setup restarts the service a moment before the runner writes the result.
        state = {"check_interval_seconds": 3600, "pending_version": "1.6.3"}
        self.assertEqual(updater.next_check_delay(2, state, 1000.0), updater.PENDING_RECHECK_SECONDS)

    def test_outcome_descriptions(self) -> None:
        self.assertIn("erro fatal durante a instalacao", updater.describe_outcome("exited", 4))
        self.assertIn("20 minutos", updater.describe_outcome("timeout", None))
        self.assertIn("sem concluir", updater.describe_outcome("missing", None))
        self.assertIn("SHA-256", updater.describe_outcome("error", None, "nao confere com o SHA-256"))


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


class LaunchInstallerTests(unittest.TestCase):
    def test_setup_command_matches_the_deploy_script(self) -> None:
        # Seen on TI-01-000013: /CLOSEAPPLICATIONS made Inno try to stop the
        # service for 90 s and abort the installation.
        command = updater.installer_command(Path("C:/x/setup.exe"), Path("C:/x/installer.log"))
        for switch in ("/VERYSILENT", "/SUPPRESSMSGBOXES", "/NORESTART", "/NOCLOSEAPPLICATIONS", "/SP-"):
            self.assertIn(switch, command)
        self.assertNotIn("/CLOSEAPPLICATIONS", command)
        self.assertFalse(any(part.startswith("/SUPERVISED") for part in command))
        self.assertTrue(command[-1].startswith("/LOG="))

    def test_service_hands_the_installation_to_the_runner(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            result = root / "install-result.json"
            result.write_text("{}", encoding="utf-8")
            with mock.patch.object(updater, "LOG_DIR", root / "logs"), \
                    mock.patch.object(updater, "INSTALL_RESULT_PATH", result), \
                    mock.patch.object(updater, "prepare_runner_executable", return_value=["runner.exe"]), \
                    mock.patch.object(updater, "start_detached") as start:
                start.return_value.pid = 7
                process, installer_log = updater.launch_installer(root / "setup.exe", quiet_logger("launch"), "1.6.3", SHA)
            self.assertFalse(result.exists(), "an old result must not be mistaken for this attempt")
        command = start.call_args.args[0]
        self.assertEqual(command[0], "runner.exe")
        self.assertEqual(
            command[1:7], ["--install-package", str(root / "setup.exe"), "--package-version", "1.6.3", "--package-sha256", SHA],
        )
        self.assertEqual(command[-1], str(installer_log))
        self.assertEqual(process.pid, 7)

    def test_service_is_built_as_console_application(self) -> None:
        # Windowed PyInstaller builds show blocking message boxes that nobody
        # can close in session 0; "--configure" hung the installer that way.
        build_script = (MODULE_PATH.parent / "build-service.ps1").read_text(encoding="utf-8")
        self.assertIn("--console", build_script)
        self.assertNotIn("--noconsole `", build_script)


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


class InstallRunnerTests(unittest.TestCase):
    """--install-package: the steps of Deploy-AtivaUnifiedAgent.ps1, run by the service."""

    def setUp(self) -> None:
        directory = tempfile.TemporaryDirectory()
        self.addCleanup(directory.cleanup)
        self.root = Path(directory.name)
        self.package = self.root / "Ativa-Unified-Agent-Setup-1.6.3.exe"
        self.package.write_bytes(b"MZ setup")
        self.sha256 = updater.file_sha256(self.package)
        self.steps: list[str] = []
        self.started: list[list[str]] = []
        self.supervision = ("exited", 0)
        for name, value in {
            "INSTALL_RESULT_PATH": self.root / "install-result.json",
            "SingleInstance": mock.MagicMock(),
            "stop_updater_service": lambda _logger: self.steps.append("stop_service"),
            "kill_leftover_processes": lambda _logger: self.steps.append("kill_leftovers"),
            "start_detached": self.fake_start,
            "supervise_installer": lambda *_args, **_kwargs: self.supervision,
            "running_setup_processes": lambda: [4243],
            "kill_process_tree": lambda pid: self.steps.append(f"kill {pid}"),
            "ensure_service_running": lambda _logger: self.steps.append("ensure_service") or True,
        }.items():
            patcher = mock.patch.object(updater, name, value)
            patcher.start()
            self.addCleanup(patcher.stop)
        self.logger = quiet_logger("ativaupdater-runner-tests")

    def fake_start(self, command: list[str]):
        self.steps.append("setup")
        self.started.append(command)
        return FakeProcess(None, pid=4242)

    def run_package(self, sha256: str | None = None) -> int:
        return updater.run_install_package(
            self.logger, self.package, "1.6.3", sha256 or self.sha256, self.root / "installer-1.log",
        )

    def result(self) -> dict:
        return json.loads(updater.INSTALL_RESULT_PATH.read_text(encoding="utf-8"))

    def test_same_order_as_the_deploy_script(self) -> None:
        self.assertEqual(self.run_package(), 0)
        self.assertEqual(self.steps, ["stop_service", "kill_leftovers", "setup", "ensure_service"])
        self.assertIn("/NOCLOSEAPPLICATIONS", self.started[0])
        self.assertEqual(self.started[0][0], str(self.package))
        result = self.result()
        self.assertEqual((result["version"], result["outcome"], result["exit_code"]), ("1.6.3", "exited", 0))

    def test_setup_error_code_is_recorded_and_service_started(self) -> None:
        self.supervision = ("exited", 5)
        self.assertEqual(self.run_package(), 5)
        self.assertEqual((self.result()["outcome"], self.result()["exit_code"]), ("exited", 5))
        self.assertEqual(self.steps[-1], "ensure_service")

    def test_hung_setup_is_killed(self) -> None:
        self.supervision = ("timeout", None)
        self.assertEqual(self.run_package(), updater.RUNNER_TIMEOUT_EXIT_CODE)
        self.assertIn("kill 4242", self.steps)
        self.assertIn("kill 4243", self.steps)
        self.assertEqual(self.result()["outcome"], "timeout")
        self.assertEqual(self.steps[-1], "ensure_service")

    def test_package_with_another_hash_is_not_installed(self) -> None:
        self.assertEqual(self.run_package("c" * 64), 1)
        self.assertEqual(self.steps, ["ensure_service"], "nothing is stopped or run, the service stays up")
        self.assertEqual(self.result()["outcome"], "error")
        self.assertIn("SHA-256", self.result()["message"])

    def test_second_runner_does_nothing(self) -> None:
        updater.SingleInstance.return_value.__enter__.side_effect = updater.UpdaterError("em uso")
        self.assertEqual(self.run_package(), 0)
        self.assertEqual(self.steps, [])
        self.assertFalse(updater.INSTALL_RESULT_PATH.exists())

    def test_runner_outcome_mapping(self) -> None:
        self.assertEqual(updater.runner_outcome(None, 4), ("exited", 4, ""))
        self.assertEqual(updater.runner_outcome({"outcome": "timeout"}, 1460), ("timeout", None, ""))
        self.assertEqual(updater.runner_outcome({"outcome": "error", "message": "x"}, 1), ("error", None, "x"))
        self.assertEqual(updater.runner_outcome({"outcome": "exited", "exit_code": 0}, 1), ("exited", 0, ""))


class ProcessDiscoveryTests(unittest.TestCase):
    def test_running_setup_processes_include_runners_but_never_itself(self) -> None:
        own = updater.own_process_ids()
        tasklist = subprocess.CompletedProcess([], 0, stdout='"Ativa-Unified-Agent-Setup-1.6.3.exe","4242","Services","0","3 K"\n')
        with mock.patch.object(updater.os, "name", "nt"), \
                mock.patch.object(updater.subprocess, "run", return_value=tasklist), \
                mock.patch.object(updater, "process_ids_by_image", return_value=[5000, *own]):
            self.assertEqual(updater.running_setup_processes(), [4242, 5000])

    @unittest.skipUnless(updater.os.name == "nt", "Windows process APIs")
    def test_process_ids_by_image_finds_this_interpreter(self) -> None:
        self.assertIn(updater.os.getpid(), updater.process_ids_by_image(Path(updater.sys.executable)))
        self.assertEqual(updater.process_ids_by_image(Path("C:/nao/existe.exe")), [])


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
        self.diagnostics: list[str] = []
        self.command_seq: int | None = None
        self.recovery_note: str | None = None
        self.on_download = None
        FakeApi.instances.append(self)

    def report(self, status: str, installed: str, available: str = "", message: str = "", install_log: str | None = None) -> None:
        self.reports.append({
            "status": status, "installed": installed, "available": available,
            "message": message, "install_log": install_log, "command_seq": self.command_seq,
        })

    def latest(self) -> dict:
        return dict(FakeApi.release)

    def download(self, _release: dict, destination: Path, cancel_requested=lambda: False) -> None:
        if self.on_download:
            self.on_download()
        destination.write_bytes(b"MZ")

    def send_diagnostics(self, text: str) -> None:
        self.diagnostics.append(text)


class CheckOnceFixture(unittest.TestCase):
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
            "RECOVERY_MARKER_PATH": root / "watchdog-recovery.json",
            "INSTALL_RESULT_PATH": root / "install-result.json",
            "ApiClient": FakeApi,
            "launch_installer": self.fake_launch,
            "running_setup_processes": lambda: list(self.setup_processes),
            "kill_process_tree": self.killed.append,
        }.items():
            patcher = mock.patch.object(updater, name, value)
            patcher.start()
            self.addCleanup(patcher.stop)
        self.logger = quiet_logger("ativaupdater-tests")

    def fake_launch(self, path: Path, _logger: logging.Logger, version: str = "", sha256: str = ""):
        self.launched.append(path)
        self.launch_arguments = (version, sha256)
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


class CheckOnceTests(CheckOnceFixture):
    # Normal flow ---------------------------------------------------------

    def test_successful_supervised_install_reports_updated(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(len(self.launched), 1)
        self.assertEqual(self.launch_arguments, ("1.6.0", SHA))
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
                         pending_started_at=time.time() - updater.INSTALL_SUPERVISION_SECONDS - 5)
        self.publish("1.6.0")
        self.setup_processes = [4242]
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(self.killed, [4242])
        self.assertIn("excedeu", self.last_report()["message"])

    def test_runner_result_explains_the_failure_after_the_service_restarts(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_action=updater.ACTION_UPGRADE, pending_started_at=time.time() - 120)
        installer_log = self.log_dir / "installer-9.log"
        installer_log.write_text("Setup was unable to automatically close all applications.\n", encoding="utf-8")
        updater.INSTALL_RESULT_PATH.write_text(json.dumps({
            "version": "1.6.0", "sha256": SHA, "outcome": "exited", "exit_code": 5, "installer_log": str(installer_log),
        }), encoding="utf-8")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 2)
        report = self.last_report()
        self.assertIn("codigo de saida 5", report["message"])
        self.assertIn("unable to automatically close", report["install_log"])

    def test_runner_error_is_reported_with_its_message(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_started_at=time.time() - 120)
        updater.INSTALL_RESULT_PATH.write_text(json.dumps({
            "version": "1.6.0", "sha256": SHA, "outcome": "error", "message": "o instalador baixado nao confere com o SHA-256 publicado",
        }), encoding="utf-8")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertIn("nao confere com o SHA-256", self.last_report()["message"])

    def test_result_of_another_package_is_ignored(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_started_at=time.time() - 120)
        updater.INSTALL_RESULT_PATH.write_text(json.dumps({"version": "1.5.9", "sha256": SHA, "outcome": "exited", "exit_code": 5}), encoding="utf-8")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertIn("encerrado sem concluir", self.last_report()["message"])

    def test_runner_that_exits_early_reports_its_result(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.fail_installer(1)
        original = self.fake_launch

        def launch_with_result(path, logger, version="", sha256=""):
            launched = original(path, logger, version, sha256)
            updater.INSTALL_RESULT_PATH.write_text(json.dumps({
                "version": version, "sha256": sha256, "outcome": "error", "message": "servico nao pode ser parado",
            }), encoding="utf-8")
            return launched

        with mock.patch.object(updater, "launch_installer", side_effect=launch_with_result):
            self.assertEqual(updater.check_once(self.logger), 2)
        self.assertIn("falha ao preparar a instalacao: servico nao pode ser parado", self.last_report()["message"])

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


class CheckOnceCommandTests(CheckOnceFixture):
    """Commands from the dashboard ("Verificar agora", "Reinstalar")."""

    def test_check_command_cancels_a_running_installation_and_starts_over(self) -> None:
        self.write_state(installed_version="1.5.1", pending_version="1.6.0", pending_sha256=SHA,
                         pending_started_at=time.time() - 60, failed_version="1.6.0", failed_sha256=SHA, failed_attempts=2)
        self.publish("1.6.0")
        self.setup_processes = [4242]
        self.assertEqual(updater.check_once(self.logger, manual=True, command=updater.COMMAND_CHECK, command_seq=6), 0)
        self.assertEqual(self.killed, [4242])
        reports = FakeApi.instances[-1].reports
        self.assertIn("Verificacao reiniciada pelo dashboard", reports[0]["message"])
        self.assertIn("foi cancelada", reports[0]["message"])
        self.assertEqual(reports[0]["command_seq"], 6)
        self.assertIn("tentativa 1 de 4", reports[1]["message"], "failures were reset")
        self.assertEqual(len(self.launched), 1)

    def test_cancel_during_installation_restarts_immediately(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        self.installer_exit_code = None
        self.installer_configures = False
        cancel = {"now": False}
        with mock.patch.object(updater, "supervise_installer", return_value=("cancelled", None)):
            result = updater.check_once(self.logger, cancel_requested=lambda: cancel["now"])
        self.assertEqual(result, updater.RESULT_RESTART_NOW)
        self.assertEqual(self.killed, [4242])
        self.assertNotIn("pending_version", self.state())

    def test_cancel_during_download_does_not_install(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0")
        cancel = {"now": False}

        def fake_api(config):
            api = FakeApi(config)
            api.on_download = lambda: cancel.update(now=True)
            return api

        with mock.patch.object(updater, "ApiClient", side_effect=fake_api):
            result = updater.check_once(self.logger, cancel_requested=lambda: cancel["now"])
        self.assertEqual(result, updater.RESULT_RESTART_NOW)
        self.assertEqual(self.launched, [])

    def test_reinstall_installs_the_current_version_again(self) -> None:
        self.write_state(installed_version="1.6.0")
        self.publish("1.6.0")
        self.assertEqual(updater.check_once(self.logger, manual=True, command=updater.COMMAND_REINSTALL), 0)
        self.assertEqual(len(self.launched), 1)
        messages = [report["message"] for report in FakeApi.instances[-1].reports]
        self.assertIn("Reinstalacao solicitada pelo dashboard.", messages[0])
        self.assertTrue(any("Reinstalacao da versao 1.6.0" in message for message in messages))

    def test_reinstall_never_downgrades_without_authorization(self) -> None:
        self.write_state(installed_version="1.6.1")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger, command=updater.COMMAND_REINSTALL), 0)
        self.assertEqual(self.launched, [])
        self.assertIn("Reinstalacao nao aplicada", self.last_report()["message"])

    def test_watchdog_note_is_sent_once(self) -> None:
        updater.RECOVERY_MARKER_PATH.write_text(json.dumps({"at": "2026-09-14 16:00", "note": "Vigia: servico parado foi iniciado"}), encoding="utf-8")
        self.write_state(installed_version="1.6.0")
        self.publish("1.6.0")
        updater.check_once(self.logger)
        self.assertEqual(FakeApi.instances[-1].recovery_note, "2026-09-14 16:00 Vigia: servico parado foi iniciado")


class ServiceRuntimeTests(unittest.TestCase):
    def setUp(self) -> None:
        self.logger = quiet_logger("ativaupdater-runtime-tests")
        for name in ("cleanup_replaced_binaries", "write_heartbeat", "ensure_watchdog_task", "promote_known_good",
                     "schedule_service_restart"):
            patcher = mock.patch.object(updater, name, return_value=True)
            patcher.start()
            self.addCleanup(patcher.stop)
        patcher = mock.patch.object(updater, "get_state", return_value={"check_interval_seconds": 3600})
        patcher.start()
        self.addCleanup(patcher.stop)

    def run_with(self, check_once, runtime: updater.ServiceRuntime | None = None) -> tuple[updater.ServiceRuntime, list]:
        runtime = runtime or updater.ServiceRuntime()
        calls: list[dict] = []

        def recorder(_logger, **kwargs):
            calls.append(kwargs)
            result = check_once(runtime, len(calls), kwargs)
            if isinstance(result, Exception):
                raise result
            return result

        def stop_when_idle(_seconds: float) -> bool:
            runtime.stop_event.set()
            return True

        runtime.wake_event.wait = stop_when_idle  # type: ignore[method-assign]
        with mock.patch.object(updater, "check_once", side_effect=recorder):
            runtime.run(self.logger, start_poller=False)
        return runtime, calls

    def test_unexpected_error_does_not_end_the_service(self) -> None:
        _, calls = self.run_with(lambda *_: RuntimeError("boom"))
        self.assertEqual(len(calls), 1)

    def test_stop_signal_is_forwarded_and_ends_the_loop(self) -> None:
        def check(runtime, _number, kwargs):
            runtime.stop_event.set()
            self.assertTrue(kwargs["stop_requested"]())
            return updater.RESULT_SERVICE_STOPPING

        _, calls = self.run_with(check)
        self.assertEqual(len(calls), 1)

    def test_command_restarts_the_check_immediately(self) -> None:
        def check(runtime, number, kwargs):
            if number == 1:
                self.assertIsNone(kwargs["command"])
                runtime.submit_command(updater.COMMAND_CHECK, 7)
                self.assertTrue(kwargs["cancel_requested"]())
                return updater.RESULT_RESTART_NOW
            self.assertEqual((kwargs["command"], kwargs["manual"]), (updater.COMMAND_CHECK, True))
            self.assertFalse(kwargs["cancel_requested"](), "cancel flag must be reset for the new check")
            return updater.RESULT_OK

        _, calls = self.run_with(check)
        self.assertEqual(len(calls), 2)

    def command_runtime(self) -> tuple[updater.ServiceRuntime, FakeApi]:
        return updater.ServiceRuntime(), FakeApi({})

    def test_check_and_reinstall_commands_cancel_current_work(self) -> None:
        runtime, api = self.command_runtime()
        runtime.handle_command_response({"pending": True, "seq": 3, "command": "reinstall"}, api, self.logger)
        self.assertTrue(runtime.cancel_event.is_set())
        self.assertEqual(runtime.take_command(), ("reinstall", 3))
        self.assertEqual((runtime.last_command_seq, api.command_seq), (3, 3))

    def test_repeated_or_acknowledged_commands_are_ignored(self) -> None:
        runtime, api = self.command_runtime()
        runtime.last_command_seq = 3
        runtime.handle_command_response({"pending": True, "seq": 3, "command": "check"}, api, self.logger)
        runtime.handle_command_response({"pending": False, "seq": 9, "command": None}, api, self.logger)
        self.assertIsNone(runtime.take_command())
        self.assertFalse(runtime.cancel_event.is_set())

    def test_send_logs_does_not_interrupt_the_check(self) -> None:
        runtime, api = self.command_runtime()
        with mock.patch.object(updater, "collect_diagnostics", return_value="LOGS"):
            runtime.handle_command_response({"pending": True, "seq": 4, "command": "send_logs"}, api, self.logger)
        self.assertEqual(api.diagnostics, ["LOGS"])
        self.assertFalse(runtime.cancel_event.is_set())
        self.assertIsNone(runtime.take_command())

    def test_restart_service_command_stops_after_scheduling_a_restart(self) -> None:
        runtime, api = self.command_runtime()
        with mock.patch.object(updater, "collect_diagnostics", return_value="LOGS"):
            runtime.handle_command_response({"pending": True, "seq": 5, "command": "restart_service"}, api, self.logger)
        updater.schedule_service_restart.assert_called_once()
        self.assertTrue(runtime.stop_event.is_set())
        self.assertIn("Reinicio do servico", api.diagnostics[0])


class CommandParsingTests(unittest.TestCase):
    def test_parse_command_response(self) -> None:
        self.assertEqual(
            updater.parse_command_response({"check_now": True, "request_seq": 4, "command": "send_logs"}),
            {"pending": True, "seq": 4, "command": "send_logs"},
        )
        # Servers before 1.6.0 only send check_now.
        self.assertEqual(updater.parse_command_response({"check_now": True}), {"pending": True, "seq": 0, "command": "check"})
        self.assertEqual(
            updater.parse_command_response({"check_now": False, "request_seq": 4, "command": "reinstall"}),
            {"pending": False, "seq": 4, "command": None},
        )
        self.assertEqual(updater.parse_command_response({"check_now": True, "request_seq": True, "command": "x"})["seq"], 0)


class FakeOpener:
    def __init__(self, outcomes: list) -> None:
        self.outcomes = outcomes
        self.requests: list = []

    def open(self, request, timeout=None):
        self.requests.append(request)
        outcome = self.outcomes.pop(0)
        if isinstance(outcome, Exception):
            raise outcome
        return outcome


class FakeDownloadResponse:
    def __init__(self, body: bytes, status: int = 200) -> None:
        self.stream = io.BytesIO(body)
        self.status = status
        self.headers = {"Content-Type": "application/octet-stream"}

    def __enter__(self):
        return self

    def __exit__(self, *_args) -> None:
        return None

    def read(self, size: int = -1) -> bytes:
        return self.stream.read(size)


class NetworkResilienceTests(unittest.TestCase):
    def api(self, outcomes: list) -> tuple[updater.ApiClient, FakeOpener, list]:
        sleeps: list = []
        api = updater.ApiClient({"api_url": API_URL, "api_token": "a" * 64}, retry_sleep=sleeps.append)
        api.opener = FakeOpener(outcomes)
        return api, api.opener, sleeps

    def test_transient_connection_errors_are_retried(self) -> None:
        timeout = updater.URLError(TimeoutError("[WinError 10060] timed out"))
        api, opener, sleeps = self.api([timeout, timeout, FakeHttpResponse({"ok": True})])
        with api._request(API_URL + "/latest", retry_delays=(5, 15)) as response:
            self.assertIn(b"ok", response.read())
        self.assertEqual((len(opener.requests), sleeps), (3, [5, 15]))

    def test_gives_up_after_the_last_retry(self) -> None:
        timeout = updater.URLError(TimeoutError("timed out"))
        api, _, _ = self.api([timeout, timeout])
        with self.assertRaises(updater.UpdaterError) as caught:
            api._request(API_URL + "/latest", retry_delays=(5,))
        self.assertIn("2 tentativa", str(caught.exception))

    def release(self, body: bytes) -> dict:
        import hashlib
        return {"version": "1.6.2", "sha256": hashlib.sha256(body).hexdigest(), "size": len(body),
                "download_url": API_URL + "/download/1.6.2"}

    def test_interrupted_download_is_resumed_with_range(self) -> None:
        body = b"MZ" + b"x" * 5000
        release = self.release(body)
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "Ativa-Unified-Agent-Setup-1.6.2.exe"
            partial = destination.with_name(f"{destination.name}.{release['sha256'][:16]}.part")
            partial.write_bytes(body[:2000])
            api, opener, _ = self.api([FakeDownloadResponse(body[2000:], status=206)])
            api.download(release, destination)
            self.assertEqual(destination.read_bytes(), body)
            self.assertEqual(opener.requests[0].get_header("Range"), "bytes=2000-")
            self.assertFalse(partial.exists())

    def test_server_ignoring_range_restarts_the_download(self) -> None:
        body = b"MZ" + b"y" * 3000
        release = self.release(body)
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "setup.exe"
            destination.with_name(f"{destination.name}.{release['sha256'][:16]}.part").write_bytes(b"garbage")
            api, _, _ = self.api([FakeDownloadResponse(body, status=200)])
            api.download(release, destination)
            self.assertEqual(destination.read_bytes(), body)

    def test_download_can_be_cancelled(self) -> None:
        body = b"MZ" + b"z" * 3000
        with tempfile.TemporaryDirectory() as directory:
            api, _, _ = self.api([FakeDownloadResponse(body)])
            with self.assertRaises(updater.OperationCancelled):
                api.download(self.release(body), Path(directory) / "setup.exe", cancel_requested=lambda: True)

    def test_download_with_retries_resumes_after_network_failure(self) -> None:
        api = mock.Mock()
        api.download.side_effect = [updater.UpdaterError("reset"), OSError("timed out"), None]
        sleeps: list = []
        updater.download_with_retries(api, {}, Path("x.exe"), quiet_logger("dl"), sleep=sleeps.append)
        self.assertEqual((api.download.call_count, sleeps), (3, [10, 30]))

    def test_report_sends_command_seq_and_clears_recovery_marker(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            marker = Path(directory) / "watchdog-recovery.json"
            marker.write_text("{}", encoding="utf-8")
            api, opener, _ = self.api([FakeHttpResponse({"ok": True})])
            api.command_seq = 8
            api.recovery_note = "Vigia: servico parado foi iniciado"
            with mock.patch.object(updater, "RECOVERY_MARKER_PATH", marker), \
                    mock.patch.object(updater, "machine_guid", return_value="a" * 32), \
                    mock.patch.object(updater, "wallpaper_client_version", return_value=""), \
                    mock.patch.object(updater, "glpi_agent_version", return_value=""):
                api.report("current", "1.6.2", "1.6.2", "ok")
            sent = json.loads(opener.requests[0].data.decode("utf-8"))
            self.assertEqual((sent["command_seq"], sent["recovery_note"]), (8, "Vigia: servico parado foi iniciado"))
            self.assertFalse(marker.exists())
            self.assertIsNone(api.recovery_note)


class WatchdogTests(unittest.TestCase):
    RUNNING = (updater.SERVICE_STATE_RUNNING, 100)
    STOPPED = (updater.SERVICE_STATE_STOPPED, 0)

    def plan(self, service, setup=False, age=None, heartbeat=None, healthy=True):
        return updater.plan_watchdog(service, setup, age, heartbeat, lambda: healthy)

    def test_healthy_service_needs_nothing(self) -> None:
        self.assertEqual(self.plan(self.RUNNING, heartbeat=20), [])
        self.assertEqual(self.plan(self.RUNNING, heartbeat=None), [], "older services write no heartbeat")

    def test_hung_service_is_restarted(self) -> None:
        self.assertEqual(self.plan(self.RUNNING, heartbeat=updater.WATCHDOG_HEARTBEAT_LIMIT_SECONDS + 1), ["restart_service"])

    def test_service_running_since_hours_without_writing_is_restarted(self) -> None:
        """Windows reports RUNNING, but nothing has written a heartbeat for hours.

        The computer is on and the service process exists, yet it stopped reaching the
        API. Until the heartbeat age stopped being suppressed for a foreign PID, this
        returned [] and the machine sat on the dashboard as "Sem contato" forever.
        """
        self.assertEqual(self.plan(self.RUNNING, heartbeat=9000.0), ["restart_service"])

    def test_recent_installation_is_left_alone(self) -> None:
        self.assertEqual(self.plan(self.STOPPED, setup=True, age=600), [])

    def test_stuck_installer_is_killed_and_service_started(self) -> None:
        self.assertEqual(
            self.plan(self.STOPPED, setup=True, age=updater.WATCHDOG_INSTALL_LIMIT_SECONDS + 1),
            ["kill_setup", "start_service"],
        )
        self.assertEqual(
            self.plan(self.RUNNING, setup=True, age=updater.WATCHDOG_INSTALL_LIMIT_SECONDS + 1, heartbeat=10),
            ["kill_setup"],
        )

    def test_stopped_service_is_started_and_broken_executable_restored(self) -> None:
        self.assertEqual(self.plan(self.STOPPED), ["start_service"])
        self.assertEqual(self.plan(self.STOPPED, healthy=False), ["restore_exe", "start_service"])
        self.assertEqual(self.plan(None, healthy=False), ["restore_exe", "create_service", "start_service"])

    def test_setup_age_uses_first_sight_and_forgets_it(self) -> None:
        with tempfile.TemporaryDirectory() as directory, mock.patch.object(updater, "PRODUCT_DIR", Path(directory)):
            self.assertEqual(updater.setup_running_age(True, {}, 1000.0), 0.0)
            self.assertEqual(updater.setup_running_age(True, {}, 1600.0), 600.0)
            # A stale pending installation must not make a new installer look old.
            stale = {"pending_version": "1.6.2", "pending_started_at": 1.0}
            self.assertEqual(updater.setup_running_age(True, stale, 1600.0), 600.0)
            self.assertIsNone(updater.setup_running_age(False, {}, 1700.0))
            self.assertEqual(updater.setup_running_age(True, {}, 2000.0), 0.0)

    def test_heartbeat_age_ignores_which_process_wrote_it(self) -> None:
        """A heartbeat left by a previous PID still dates the last proof of life.

        A service that wedges before its first write keeps the old PID in the file;
        suppressing the age there hid exactly the state the watchdog exists to repair.
        """
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "heartbeat.json"
            path.write_text(json.dumps({"at": 1000.0, "pids": [11, 10]}), encoding="utf-8")
            with mock.patch.object(updater, "HEARTBEAT_PATH", path):
                self.assertEqual(updater.heartbeat_age(1030.0), 30.0)
                # Hours later, with nothing having written since: the wedged service.
                self.assertEqual(updater.heartbeat_age(10000.0), 9000.0)

    def test_missing_heartbeat_is_unknown(self) -> None:
        """Services older than 1.5.0 write no file; the watchdog must not touch them."""
        with tempfile.TemporaryDirectory() as directory:
            with mock.patch.object(updater, "HEARTBEAT_PATH", Path(directory) / "absent.json"):
                self.assertIsNone(updater.heartbeat_age(1030.0))

    def test_diagnostics_include_logs_without_the_api_token(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            logs = root / "logs"
            logs.mkdir()
            (logs / "service.log").write_text("ERROR Falha de comunicacao com a API: WinError 10060\n", encoding="utf-8")
            (logs / "watchdog.log").write_text("Vigia: servico parado foi iniciado\n", encoding="utf-8")
            (logs / "installer-100.log").write_text("instalacao anterior\n", encoding="utf-8")
            (logs / "installer-200.log").write_text("instalacao mais recente\n", encoding="utf-8")
            (root / "service-config.json").write_text(json.dumps({"api_token": "SECRET" * 10}), encoding="utf-8")
            (root / "state.json").write_text(json.dumps({"installed_version": "1.6.1"}), encoding="utf-8")
            with mock.patch.object(updater, "PRODUCT_DIR", root), \
                    mock.patch.object(updater, "LOG_DIR", logs), \
                    mock.patch.object(updater, "STATE_PATH", root / "state.json"), \
                    mock.patch.object(updater, "HEARTBEAT_PATH", root / "heartbeat.json"), \
                    mock.patch.object(updater, "INSTALL_FAILURE_LOG_PATH", logs / "install-failure.log"), \
                    mock.patch.object(updater, "INSTALL_RESULT_PATH", root / "install-result.json"), \
                    mock.patch.object(updater, "WALLPAPER_LOG_DIR", root / "wallpaper"), \
                    mock.patch.object(updater, "WATCHDOG_EXE", root / "watchdog" / "x.exe"), \
                    mock.patch.object(updater, "query_service", return_value=self.RUNNING), \
                    mock.patch.object(updater, "running_setup_processes", return_value=[]):
                text = updater.collect_diagnostics()
        self.assertIn("WinError 10060", text)
        self.assertIn("Vigia: servico parado foi iniciado", text)
        self.assertIn("Instalador (installer-100.log)", text)
        self.assertIn("Instalador (installer-200.log)", text)
        self.assertIn("instalacao anterior", text)
        self.assertIn("instalacao mais recente", text)
        self.assertIn('"installed_version": "1.6.1"', text)
        self.assertNotIn("SECRET", text)

    def test_wallpaper_errors_of_every_user_are_collected_in_order(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            logs = Path(directory)
            (logs / "client-aaa.log").write_text(
                "2026-09-15 13:41:55 ERROR HTTP_404 HTTP 404\n"
                "Traceback (most recent call last):\n"
                "2026-09-15 13:42:00 WARNING Wallpaper policy fallback\n"
                "2026-09-15 14:10:00 INFO Configuration unchanged (HTTP 304)\n",
                encoding="utf-8",
            )
            (logs / "client-bbb.log").write_text(
                "2026-09-15 13:59:12 ERROR HTTP_401 Token ausente ou invalido\n", encoding="utf-8",
            )
            lines = updater.wallpaper_error_lines([logs / "client-bbb.log", logs / "client-aaa.log"])
        self.assertEqual(lines, [
            "2026-09-15 13:41:55 ERROR HTTP_404 HTTP 404  [client-aaa.log]",
            "2026-09-15 13:59:12 ERROR HTTP_401 Token ausente ou invalido  [client-bbb.log]",
        ])
        self.assertEqual(updater.wallpaper_error_lines([logs / "missing.log"]), [])

    def test_diagnostics_end_with_the_wallpaper_error_history(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "logs").mkdir()
            wallpaper = root / "wallpaper"
            wallpaper.mkdir()
            (wallpaper / "client-aaa.log").write_text("2026-09-15 13:41:55 ERROR HTTP_404 HTTP 404\n", encoding="utf-8")
            with mock.patch.object(updater, "PRODUCT_DIR", root), \
                    mock.patch.object(updater, "LOG_DIR", root / "logs"), \
                    mock.patch.object(updater, "STATE_PATH", root / "state.json"), \
                    mock.patch.object(updater, "HEARTBEAT_PATH", root / "heartbeat.json"), \
                    mock.patch.object(updater, "INSTALL_FAILURE_LOG_PATH", root / "logs" / "install-failure.log"), \
                    mock.patch.object(updater, "INSTALL_RESULT_PATH", root / "install-result.json"), \
                    mock.patch.object(updater, "WALLPAPER_LOG_DIR", wallpaper), \
                    mock.patch.object(updater, "WATCHDOG_EXE", root / "watchdog" / "x.exe"), \
                    mock.patch.object(updater, "query_service", return_value=self.RUNNING), \
                    mock.patch.object(updater, "running_setup_processes", return_value=[]):
                text = updater.collect_diagnostics()
        self.assertTrue(text.rstrip().endswith("ERROR HTTP_404 HTTP 404  [client-aaa.log]"))
        self.assertIn("== Wallpaper Client (client-aaa.log) ==", text)
        self.assertIn("== Wallpaper Client: erros recentes ==", text)


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


class FakeRemoteApi:
    def __init__(self, responses):
        self.responses = list(responses)
        self.sent = []

    def post_json(self, path, payload, timeout=30):
        self.sent.append((path, json.loads(json.dumps(payload))))
        return self.responses.pop(0)


class RemoteAccessTests(unittest.TestCase):
    def setUp(self) -> None:
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.state_path = Path(self.temp.name) / "remote-state.json"
        patches = [
            mock.patch.object(updater, "machine_guid", return_value="a" * 32),
            mock.patch.object(updater, "rustdesk_installed_version", return_value="1.3.1"),
            mock.patch.object(updater.RemoteAgent, "rustdesk_ready", return_value=True),
            mock.patch.object(updater, "RUSTDESK_INSTALLED_EXE", Path(self.temp.name) / "rustdesk.exe"),
        ]
        for patch in patches:
            patch.start()
            self.addCleanup(patch.stop)
        self.passwords = []
        self.agent = self.new_agent()

    def new_agent(self):
        agent = updater.RemoteAgent(
            quiet_logger("remote-tests"), updater.threading.Event(), state_path=self.state_path
        )
        agent.rustdesk_id = "123456789"
        agent.id_checked_at = time.monotonic()
        agent.set_password = lambda password: self.passwords.append(password) or True
        return agent

    def test_remote_api_is_next_to_the_updater_api(self) -> None:
        self.assertEqual(
            updater.remote_api_url(API_URL),
            "https://chamados.ativalocacao.com.br:8443/plugins/ativaremote/api/v1",
        )
        with self.assertRaises(updater.UpdaterError):
            updater.remote_api_url("https://example.com/other/api/v1")

    def test_rustdesk_id_is_read_only_from_a_clean_line(self) -> None:
        self.assertEqual(updater.parse_rustdesk_id("123 456 789\r\n123456789\r\n"), "123456789")
        self.assertEqual(updater.parse_rustdesk_id("Installation and administrative privileges required!"), "")
        self.assertEqual(updater.parse_rustdesk_id(""), "")

    def test_rustdesk_output_does_not_wait_for_processes_it_leaves_running(self) -> None:
        import sys
        script = (
            "import subprocess, sys; "
            "subprocess.Popen([sys.executable, '-c', 'import time; time.sleep(15)']); "
            "print('123456789')"
        )
        started = time.monotonic()
        output = updater.run_rustdesk(Path(sys.executable), "-c", script, timeout=10)
        self.assertLess(time.monotonic() - started, 8)
        self.assertEqual(updater.parse_rustdesk_id(output), "123456789")

    def test_session_passwords_are_random_and_unambiguous(self) -> None:
        first = updater.generate_session_password()
        self.assertEqual(len(first), 12)
        self.assertNotEqual(first, updater.generate_session_password())
        self.assertFalse(set(first) & set("0O1lI"))

    def test_response_parsing_is_strict(self) -> None:
        parsed = updater.parse_remote_response({
            "require_consent": False,
            "request": {"seq": 3, "action": "open", "requested_by": "Ana"},
            "poll_after_seconds": 1,
        })
        self.assertEqual(parsed, {
            "require_consent": False,
            "request": {"seq": 3, "action": "open", "requested_by": "Ana"},
            "poll_after_seconds": 5,
        })
        parsed = updater.parse_remote_response({"request": {"seq": "3", "action": "run"}})
        self.assertTrue(parsed["require_consent"])
        self.assertIsNone(parsed["request"])
        with self.assertRaises(updater.UpdaterError):
            updater.parse_remote_response([])

    def test_consent_prompt_goes_to_the_console_user_first(self) -> None:
        sessions = [(0, 4), (2, 0), (3, 0), (5, 4)]
        self.assertEqual(updater.active_user_session(sessions, 3), 3)
        self.assertEqual(updater.active_user_session(sessions, 5), 2)
        self.assertIsNone(updater.active_user_session([(0, 0), (4, 4)], None))

    def test_startup_rotates_the_password_and_reports_the_id(self) -> None:
        api = FakeRemoteApi([{"require_consent": True, "request": None}])
        self.assertEqual(self.agent.poll_once(api), 10)
        self.assertEqual(len(self.passwords), 1)
        path, payload = api.sent[0]
        self.assertEqual(path, "/report")
        self.assertEqual(payload["rustdesk"]["id"], "123456789")
        self.assertNotIn("result", payload)
        self.assertNotIn(self.passwords[0], json.dumps(api.sent))

    def test_access_without_consent_sends_the_new_password_once(self) -> None:
        self.agent.password_rotated = True
        request = {"require_consent": False, "request": {"seq": 4, "action": "open"}}
        api = FakeRemoteApi([request, request, request])
        with mock.patch.object(updater, "ask_user_consent") as ask:
            self.agent.poll_once(api)
            self.agent.poll_once(api)
        ask.assert_not_called()
        result = api.sent[1][1]["result"]
        self.assertEqual(result["status"], "accepted")
        self.assertEqual(result["password"], self.passwords[0])
        self.assertNotIn("result", api.sent[2][1])
        self.assertEqual(json.loads(self.state_path.read_text()), {"handled_seq": 4, "session_open": True})

    def test_refused_consent_keeps_the_password(self) -> None:
        self.agent.password_rotated = True
        api = FakeRemoteApi([
            {"require_consent": True, "request": {"seq": 1, "action": "open", "requested_by": "Ana"}},
            {"require_consent": True, "request": None},
        ])
        with mock.patch.object(updater, "enumerate_sessions", return_value=[(1, 0)]), \
                mock.patch.object(updater, "console_session_id", return_value=1), \
                mock.patch.object(updater, "ask_user_consent", return_value="rejected") as ask:
            self.agent.poll_once(api)
        ask.assert_called_once_with(1, "Ana")
        self.assertEqual(api.sent[1][1]["result"]["status"], "rejected")
        self.assertNotIn("password", api.sent[1][1]["result"])
        self.assertEqual(self.passwords, [])

    def test_consent_without_a_logged_on_user_is_refused(self) -> None:
        self.agent.password_rotated = True
        api = FakeRemoteApi([{"request": {"seq": 2, "action": "open"}}, {"request": None}])
        with mock.patch.object(updater, "enumerate_sessions", return_value=[(0, 0)]), \
                mock.patch.object(updater, "console_session_id", return_value=None):
            self.agent.poll_once(api)
        self.assertEqual(api.sent[1][1]["result"]["status"], "no_user")

    def test_closing_replaces_the_password(self) -> None:
        self.agent.password_rotated = True
        self.agent.session_open = True
        self.agent.handled_seq = 4
        api = FakeRemoteApi([{"request": {"seq": 5, "action": "close"}}, {"request": None}])
        self.agent.poll_once(api)
        self.assertEqual(len(self.passwords), 1)
        self.assertEqual(len(self.passwords[0]), 24)
        self.assertEqual(api.sent[1][1]["result"], {"seq": 5, "status": "closed", "message": "Sessao encerrada."})
        self.assertFalse(self.agent.session_open)

    def test_an_open_session_survives_a_service_restart(self) -> None:
        updater.atomic_json(self.state_path, {"handled_seq": 7, "session_open": True})
        agent = self.new_agent()
        agent.poll_once(FakeRemoteApi([{"request": {"seq": 7, "action": "open"}}]))
        self.assertEqual(self.passwords, [])

    def test_a_failed_report_is_sent_again_without_repeating_the_request(self) -> None:
        self.agent.password_rotated = True

        class FlakyApi(FakeRemoteApi):
            failed = False

            def post_json(self, path, payload, timeout=30):
                if "result" in payload and not self.failed:
                    self.failed = True
                    raise updater.UpdaterError("Falha de comunicacao com a API")
                return super().post_json(path, payload, timeout)

        request = {"require_consent": False, "request": {"seq": 9, "action": "open"}}
        api = FlakyApi([request, request])
        with self.assertRaises(updater.UpdaterError):
            self.agent.poll_once(api)
        self.agent.poll_once(api)
        self.assertEqual(len(self.passwords), 1)
        self.assertEqual(api.sent[1][1]["result"]["password"], self.passwords[0])

    def test_missing_rustdesk_is_reported_with_the_reason(self) -> None:
        self.agent.rustdesk_id = ""
        self.agent.message = "Servico do RustDesk nao encontrado."
        with mock.patch.object(updater.RemoteAgent, "rustdesk_ready", return_value=False):
            api = FakeRemoteApi([{"request": {"seq": 1, "action": "open"}}, {"request": None}])
            self.agent.poll_once(api)
        self.assertEqual(api.sent[0][1]["rustdesk"]["message"], "Servico do RustDesk nao encontrado.")
        self.assertEqual(api.sent[1][1]["result"]["status"], "error")
        self.assertEqual(self.passwords, [])


if __name__ == "__main__":
    unittest.main()


class WallpaperWatchdogTests(unittest.TestCase):
    """Religa o Wallpaper Client onde o usuario o encerrou; nunca duplica."""

    def _run(self, running_sessions, user_sessions, is_file=True, session0=True):
        launched = []
        with mock.patch.object(updater.os, "name", "nt"), \
             mock.patch.object(updater, "current_session_id", return_value=0 if session0 else 1), \
             mock.patch.object(type(updater.WALLPAPER_CLIENT_PATH), "is_file", return_value=is_file), \
             mock.patch.object(updater, "process_session_ids", return_value=set(running_sessions)), \
             mock.patch.object(updater, "enumerate_sessions", return_value=[]), \
             mock.patch.object(updater, "select_user_sessions", return_value=list(user_sessions)), \
             mock.patch.object(updater, "launch_in_session", side_effect=lambda s, e: launched.append(s)):
            count = updater.ensure_wallpaper_running(quiet_logger("wallpaper-watchdog"))
        return count, launched

    def test_relaunches_only_missing_sessions(self):
        count, launched = self._run(running_sessions={1}, user_sessions={1, 2})
        self.assertEqual(count, 1)
        self.assertEqual(launched, [2])  # sessao 1 ja rodava, so a 2 e religada

    def test_no_relaunch_when_all_running(self):
        count, launched = self._run(running_sessions={1, 2}, user_sessions={1, 2})
        self.assertEqual(count, 0)
        self.assertEqual(launched, [])

    def test_relaunches_after_user_killed_it(self):
        """Usuario matou o cliente: nenhuma sessao tem processo, religa todas."""
        count, launched = self._run(running_sessions=set(), user_sessions={2})
        self.assertEqual(count, 1)
        self.assertEqual(launched, [2])

    def test_only_runs_in_session_zero(self):
        """Fora do servico (sessao != 0) nao age: nao ha como lancar em outra sessao."""
        count, launched = self._run(running_sessions=set(), user_sessions={2}, session0=False)
        self.assertEqual(count, 0)
        self.assertEqual(launched, [])

    def test_noop_when_client_not_installed(self):
        count, launched = self._run(running_sessions=set(), user_sessions={2}, is_file=False)
        self.assertEqual(count, 0)
        self.assertEqual(launched, [])

    def test_launch_failure_does_not_raise(self):
        with mock.patch.object(updater.os, "name", "nt"), \
             mock.patch.object(updater, "current_session_id", return_value=0), \
             mock.patch.object(type(updater.WALLPAPER_CLIENT_PATH), "is_file", return_value=True), \
             mock.patch.object(updater, "process_session_ids", return_value=set()), \
             mock.patch.object(updater, "enumerate_sessions", return_value=[]), \
             mock.patch.object(updater, "select_user_sessions", return_value=[2]), \
             mock.patch.object(updater, "launch_in_session", side_effect=OSError("sem acesso")):
            count = updater.ensure_wallpaper_running(quiet_logger("wallpaper-watchdog"))
        self.assertEqual(count, 0)  # falhou mas nao levantou


class GuardianWatchdogTests(unittest.TestCase):
    """O Updater vigia o Guardian: religa, re-registra ou reinstala o que caiu."""

    RUNNING = (updater.SERVICE_STATE_RUNNING, 1234)
    STOPPED = (1, 0)

    def _run(self, service, exe_present, maintenance=False, due=True):
        calls = {"start": [], "install_service": False, "reinstall": False}

        def fake_run(cmd, **kw):
            if "--install-service" in cmd:
                calls["install_service"] = True
            return mock.Mock(returncode=0)

        with mock.patch.object(updater.os, "name", "nt"), \
             mock.patch.object(updater, "guardian_maintenance_active", return_value=maintenance), \
             mock.patch.object(updater, "query_service", return_value=service), \
             mock.patch.object(type(updater.GUARDIAN_EXE), "is_file", return_value=exe_present), \
             mock.patch.object(updater, "_guardian_recovery_due", return_value=due), \
             mock.patch.object(updater, "atomic_json"), \
             mock.patch.object(updater, "run_sc", side_effect=lambda *a: calls["start"].append(a) or 0), \
             mock.patch.object(updater.subprocess, "run", side_effect=fake_run), \
             mock.patch.object(updater, "reinstall_unified_package",
                               side_effect=lambda log: calls.__setitem__("reinstall", True)):
            updater.ensure_guardian_running(quiet_logger("guardian-watchdog"))
        return calls

    def test_healthy_does_nothing(self):
        c = self._run(self.RUNNING, exe_present=True)
        self.assertEqual(c["start"], [])
        self.assertFalse(c["install_service"])
        self.assertFalse(c["reinstall"])

    def test_stopped_service_is_started(self):
        c = self._run(self.STOPPED, exe_present=True)
        self.assertIn(("start", "AtivaGuardian"), c["start"])
        self.assertFalse(c["reinstall"])

    def test_missing_service_but_exe_present_reregisters(self):
        c = self._run(None, exe_present=True)
        self.assertTrue(c["install_service"])
        self.assertFalse(c["reinstall"])

    def test_missing_exe_triggers_reinstall(self):
        c = self._run(None, exe_present=False)
        self.assertTrue(c["reinstall"])

    def test_missing_exe_but_rate_limited_skips(self):
        c = self._run(None, exe_present=False, due=False)
        self.assertFalse(c["reinstall"])

    def test_maintenance_window_is_respected(self):
        c = self._run(None, exe_present=False, maintenance=True)
        self.assertFalse(c["reinstall"])
        self.assertFalse(c["install_service"])


class GuardianRecoveryRateLimitTests(unittest.TestCase):
    def test_recovery_due_when_never_run(self):
        with mock.patch.object(updater, "load_json", side_effect=updater.UpdaterError("sem marcador")):
            self.assertTrue(updater._guardian_recovery_due(10_000))

    def test_recovery_not_due_within_interval(self):
        with mock.patch.object(updater, "load_json", return_value={"at": 10_000}):
            self.assertFalse(updater._guardian_recovery_due(10_000 + 60))

    def test_recovery_due_after_interval(self):
        with mock.patch.object(updater, "load_json", return_value={"at": 10_000}):
            self.assertTrue(updater._guardian_recovery_due(10_000 + updater.GUARDIAN_RECOVERY_MIN_INTERVAL + 1))
