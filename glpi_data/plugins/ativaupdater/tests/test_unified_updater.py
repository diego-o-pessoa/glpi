from __future__ import annotations

import importlib.util
import json
import logging
from pathlib import Path
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


class VersionTests(unittest.TestCase):
    def test_updater_version_is_valid(self) -> None:
        self.assertEqual(updater.UPDATER_VERSION, "1.3.0")
        self.assertEqual(updater.version_tuple(updater.UPDATER_VERSION), (1, 3, 0))
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
    def test_backoff_grows_for_the_same_package_and_resets_for_another(self) -> None:
        state = {"pending_version": "1.6.0", "pending_sha256": SHA, "pending_started_at": 1.0}
        self.assertEqual(updater.register_install_failure(state, 1000.0), 300)
        self.assertNotIn("pending_version", state)
        for expected_delay in (1800, updater.SUSPENDED_RETRY_SECONDS, updater.SUSPENDED_RETRY_SECONDS):
            state.update({"pending_version": "1.6.0", "pending_sha256": SHA})
            self.assertEqual(updater.register_install_failure(state, 1000.0), expected_delay)
        self.assertEqual(state["failed_attempts"], 4)

        state.update({"pending_version": "1.6.1", "pending_sha256": "c" * 64})
        self.assertEqual(updater.register_install_failure(state, 1000.0), 300)
        self.assertEqual(state["failed_attempts"], 1)

    def test_block_reason(self) -> None:
        release = {"version": "1.6.0", "sha256": SHA}
        state = {"failed_version": "1.6.0", "failed_sha256": SHA, "failed_attempts": 1, "retry_after": 1300.0}
        self.assertEqual(updater.install_block_reason(state, release, 1000.0)[1], False)
        self.assertIsNone(updater.install_block_reason(state, release, 1300.0))
        self.assertIsNone(updater.install_block_reason(state, {"version": "1.6.0", "sha256": "c" * 64}, 1000.0))

    def test_suspended_package_is_retried_daily(self) -> None:
        release = {"version": "1.6.0", "sha256": SHA}
        state = {
            "failed_version": "1.6.0",
            "failed_sha256": SHA,
            "failed_attempts": updater.MAX_INSTALL_ATTEMPTS,
            "retry_after": 1000.0 + updater.SUSPENDED_RETRY_SECONDS,
        }
        message, permanent = updater.install_block_reason(state, release, 1000.0)
        self.assertTrue(permanent)
        self.assertIn("24 h", message)
        self.assertIsNone(updater.install_block_reason(state, release, 1000.0 + updater.SUSPENDED_RETRY_SECONDS))


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
        self.logger = logging.getLogger("ativaupdater-session-tests")
        self.logger.addHandler(logging.NullHandler())
        self.logger.propagate = False

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
        self.reports: list[tuple[str, str, str, str]] = []
        FakeApi.instances.append(self)

    def report(self, status: str, installed: str, available: str = "", message: str = "") -> None:
        self.reports.append((status, installed, available, message))

    def latest(self) -> dict:
        return dict(FakeApi.release)

    def download(self, _release: dict, destination: Path) -> None:
        destination.write_bytes(b"MZ")


class CheckOnceTests(unittest.TestCase):
    def setUp(self) -> None:
        directory = tempfile.TemporaryDirectory()
        self.addCleanup(directory.cleanup)
        root = Path(directory.name)
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
        FakeApi.instances = []
        for name, value in {
            "CONFIG_PATH": config_path,
            "STATE_PATH": self.state_path,
            "DOWNLOAD_DIR": root / "downloads",
            "LOG_DIR": self.log_dir,
            "ApiClient": FakeApi,
            "launch_installer": lambda path, _logger: self.launched.append(path),
        }.items():
            patcher = mock.patch.object(updater, name, value)
            patcher.start()
            self.addCleanup(patcher.stop)
        self.logger = logging.getLogger("ativaupdater-tests")
        self.logger.addHandler(logging.NullHandler())
        self.logger.propagate = False

    def publish(self, version: str, allow_downgrade: bool) -> None:
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

    def last_report(self) -> tuple[str, str, str, str]:
        return FakeApi.instances[-1].reports[-1]

    def test_upgrade_launches_installer(self) -> None:
        self.write_state(installed_version="1.5.1")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 10)
        self.assertEqual(len(self.launched), 1)
        self.assertEqual(self.state()["pending_action"], updater.ACTION_UPGRADE)

    def test_newer_machine_stays_when_downgrade_is_not_authorized(self) -> None:
        self.write_state(installed_version="1.6.1")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.launched, [])
        status, _, _, message = self.last_report()
        self.assertEqual(status, "current")
        self.assertIn("downgrade nao autorizado", message)

    def test_authorized_rollback_launches_older_installer(self) -> None:
        self.write_state(installed_version="1.6.1")
        self.publish("1.6.0", allow_downgrade=True)
        self.assertEqual(updater.check_once(self.logger), 10)
        self.assertEqual([path.name for path in self.launched], ["Ativa-Unified-Agent-Setup-1.6.0.exe"])
        state = self.state()
        self.assertEqual(state["pending_version"], "1.6.0")
        self.assertEqual(state["pending_sha256"], SHA)
        self.assertEqual(state["pending_action"], updater.ACTION_DOWNGRADE)
        self.assertIn("Rollback", self.last_report()[3])

    def test_rollback_below_minimum_package_is_refused(self) -> None:
        self.write_state(installed_version="1.6.0")
        self.publish("1.5.1", allow_downgrade=True)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.launched, [])

    def test_timed_out_installation_is_reported_and_not_retried_immediately(self) -> None:
        (self.log_dir / "installer-1.log").write_text("line one\nFatal: msiexec returned 1603\n", encoding="utf-8")
        self.write_state(
            installed_version="1.6.1",
            pending_version="1.6.0",
            pending_sha256=SHA,
            pending_action=updater.ACTION_DOWNGRADE,
            pending_started_at=time.time() - updater.INSTALL_TIMEOUT_SECONDS - 1,
        )
        self.publish("1.6.0", allow_downgrade=True)

        self.assertEqual(updater.check_once(self.logger), 2)
        status, _, available, message = self.last_report()
        self.assertEqual((status, available), ("error", "1.6.0"))
        self.assertIn("1603", message)
        state = self.state()
        self.assertEqual(state["failed_attempts"], 1)
        self.assertNotIn("pending_version", state)

        self.assertEqual(updater.check_once(self.logger), 2)
        self.assertEqual(self.launched, [])
        self.assertIn("nova tentativa", self.last_report()[3])

    def test_suspended_after_max_attempts_until_manual_check(self) -> None:
        self.write_state(
            installed_version="1.6.1",
            failed_version="1.6.0",
            failed_sha256=SHA,
            failed_attempts=updater.MAX_INSTALL_ATTEMPTS,
            retry_after=time.time() + updater.SUSPENDED_RETRY_SECONDS,
        )
        self.publish("1.6.0", allow_downgrade=True)

        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.launched, [])
        self.assertIn("suspensa", self.last_report()[3])

        self.assertEqual(updater.check_once(self.logger, manual=True), 10)
        self.assertEqual(len(self.launched), 1)

    def test_reaching_the_published_version_clears_failures(self) -> None:
        self.write_state(installed_version="1.6.0", failed_version="1.6.0", failed_sha256=SHA, failed_attempts=2)
        self.publish("1.6.0", allow_downgrade=True)
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertNotIn("failed_attempts", self.state())

    def test_first_check_after_installation_reports_updated_once(self) -> None:
        self.write_state(installed_version="1.6.0", last_result="installed")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 0)
        status, installed, _, message = self.last_report()
        self.assertEqual((status, installed), ("updated", "1.6.0"))
        self.assertIn("instalada com sucesso", message)

        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(self.last_report()[0], "current")

    def test_invalid_installed_version_reinstalls_instead_of_stopping(self) -> None:
        self.write_state(installed_version="corrompido")
        self.publish("1.6.0", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 10)
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
        self.publish("1.6.1", allow_downgrade=False)
        self.assertEqual(updater.check_once(self.logger), 10)
        self.assertFalse(old.exists())
        self.assertTrue((download_dir / "Ativa-Unified-Agent-Setup-1.6.1.exe").exists())

        self.write_state(installed_version="1.6.1", last_result="installed")
        self.assertEqual(updater.check_once(self.logger), 0)
        self.assertEqual(list(download_dir.iterdir()), [])


class ServiceRuntimeTests(unittest.TestCase):
    def setUp(self) -> None:
        self.logger = logging.getLogger("ativaupdater-runtime-tests")
        self.logger.addHandler(logging.NullHandler())
        self.logger.propagate = False

    def run_once_with(self, check_once) -> list[float]:
        runtime = updater.ServiceRuntime()
        waits: list[float] = []

        def wait(seconds: float) -> bool:
            waits.append(seconds)
            runtime.stop_event.set()
            return True

        runtime.stop_event.wait = wait  # type: ignore[method-assign]
        with mock.patch.object(updater, "check_once", side_effect=check_once), \
                mock.patch.object(updater, "get_state", return_value={"check_interval_seconds": 3600}):
            runtime.run(self.logger)
        return waits

    def test_unexpected_error_does_not_end_the_service(self) -> None:
        waits = self.run_once_with(RuntimeError("boom"))
        self.assertEqual(len(waits), 1)

    def test_service_keeps_running_after_launching_installer(self) -> None:
        # Previously the loop broke out immediately; it must now wait so that
        # an installer that dies early is detected by the timeout.
        waits = self.run_once_with(lambda _logger, manual=False: 10)
        self.assertEqual(len(waits), 1)


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
