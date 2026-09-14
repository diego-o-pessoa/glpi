from __future__ import annotations

import importlib.util
from pathlib import Path
import unittest


MODULE_PATH = Path(__file__).parents[1] / "client" / "unified_updater_service.py"
SPEC = importlib.util.spec_from_file_location("unified_updater_service", MODULE_PATH)
assert SPEC is not None and SPEC.loader is not None
updater = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(updater)


class VersionTests(unittest.TestCase):
    def test_updater_version_is_valid(self) -> None:
        self.assertEqual(updater.UPDATER_VERSION, "1.2.0")
        self.assertEqual(updater.version_tuple(updater.UPDATER_VERSION), (1, 2, 0))
        self.assertEqual(updater.COMMAND_POLL_SECONDS, 15)

    def test_semantic_version_comparison(self) -> None:
        self.assertLess(updater.version_tuple("1.4.3"), updater.version_tuple("1.4.4"))
        self.assertGreater(updater.version_tuple("2.0.0"), updater.version_tuple("1.99.99"))

    def test_rejects_non_strict_version(self) -> None:
        for value in ("1.4", "v1.4.4", "1.4.4-beta", "1.4.4.1"):
            with self.subTest(value=value), self.assertRaises(updater.UpdaterError):
                updater.version_tuple(value)


class ConfigTests(unittest.TestCase):
    def valid(self) -> dict:
        return {
            "api_url": "https://chamados.ativalocacao.com.br:8443/plugins/ativaupdater/api/v1",
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
