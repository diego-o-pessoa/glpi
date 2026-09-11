from __future__ import annotations

from pathlib import Path
import sys
import tempfile
import unittest


CLIENT_DIR = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(CLIENT_DIR))
import updater  # noqa: E402


class UpdaterTests(unittest.TestCase):
    def test_binary_headers_are_enforced(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "package.bin"
            path.write_bytes(b"MZ" + b"\0" * 32)
            updater.validate_download(path, "wallpaper_client")
            with self.assertRaises(updater.wc.ClientError):
                updater.validate_download(path, "glpi_agent")
            path.write_bytes(b"\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1" + b"\0" * 32)
            updater.validate_download(path, "glpi_agent")

    def test_failed_updates_use_exponential_retry(self):
        original = updater.UPDATE_STATE_PATH
        try:
            with tempfile.TemporaryDirectory() as directory:
                updater.UPDATE_STATE_PATH = Path(directory) / "state.json"
                self.assertEqual(updater.record_retry(42, True), 60)
                self.assertEqual(updater.record_retry(42, True), 120)
                self.assertFalse(updater.retry_allowed(42))
                updater.record_retry(42, False)
                self.assertTrue(updater.retry_allowed(42))
        finally:
            updater.UPDATE_STATE_PATH = original

    def test_updater_version_is_valid(self):
        self.assertRegex(updater.UPDATER_VERSION, r"^\d+\.\d+\.\d+$")


if __name__ == "__main__":
    unittest.main()
