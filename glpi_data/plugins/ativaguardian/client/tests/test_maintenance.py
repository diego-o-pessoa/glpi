import hashlib
from pathlib import Path
import sys
import tempfile
import unittest
from unittest import mock

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))
import maintenance as m


def verifier(password="a password for Ativa"):
    salt = bytes.fromhex("ab" * 16)
    digest = hashlib.pbkdf2_hmac("sha256", password.encode(), salt, 600000).hex()
    return "pbkdf2_sha256$600000$" + salt.hex() + "$" + digest


class PasswordTests(unittest.TestCase):
    def test_correct_password_and_wrong_password(self):
        hashed = verifier()
        self.assertTrue(m.verify_password("a password for Ativa", hashed))
        self.assertFalse(m.verify_password("incorrect", hashed))

    def test_invalid_parameters_fail_closed(self):
        for value in ("", "plain password", "pbkdf2_sha256$1$ab$ab", "pbkdf2_sha256$999999999$ab$ab"):
            self.assertFalse(m.verify_password("", value))

    def test_expired_or_unbounded_lease_is_not_authorization(self):
        self.assertTrue(m.lease_active({"until": 1500}, now=1000))
        for until in (0, 999, 1000, 999999):
            self.assertFalse(m.lease_active({"until": until}, now=1000))

    def test_unauthorized_call_never_modifies_services(self):
        with mock.patch.object(m, "is_admin", return_value=False), mock.patch.object(m, "command") as command:
            with self.assertRaises(PermissionError):
                m.authorize(installation=True)
            command.assert_not_called()

    def test_system_update_does_not_prompt_for_password(self):
        with mock.patch.object(m, "is_admin", return_value=True), \
             mock.patch.object(m, "is_system", return_value=True), \
             mock.patch.object(m, "read_json", return_value={"maintenance_password_hash": verifier()}), \
             mock.patch.object(m, "open_lease") as opened:
            m.authorize(installation=True)
            opened.assert_called_once()

    def test_wrong_password_or_cancel_never_opens_lease(self):
        hashed = verifier()
        with tempfile.TemporaryDirectory() as directory:
            for password in ("incorrect", None):
                with self.subTest(password=password), mock.patch.object(m, "GUARDIAN", Path(directory)), \
                     mock.patch.object(m, "is_admin", return_value=True), mock.patch.object(m, "is_system", return_value=False), \
                     mock.patch.object(m, "read_json", side_effect=[{"maintenance_password_hash": hashed}, {}]), \
                     mock.patch.object(m, "prompt_password", return_value=password), \
                     mock.patch.object(m, "open_lease") as opened, mock.patch.object(m, "install_recovery_task") as task:
                    with self.assertRaises(PermissionError):
                        m.authorize(installation=True)
                    opened.assert_not_called()
                    task.assert_not_called()

    def test_admin_install_requires_password_and_arms_recovery_before_unlock(self):
        calls = []
        with tempfile.TemporaryDirectory() as directory, mock.patch.object(m, "GUARDIAN", Path(directory)), \
             mock.patch.object(m, "is_admin", return_value=True), mock.patch.object(m, "is_system", return_value=False), \
             mock.patch.object(m, "read_json", side_effect=[{"maintenance_password_hash": verifier()}, {}]), \
             mock.patch.object(m, "prompt_password", return_value="a password for Ativa"), \
             mock.patch.object(m, "install_recovery_task", side_effect=lambda: calls.append("recovery")), \
             mock.patch.object(m, "open_lease", side_effect=lambda: calls.append("unlock")):
            m.authorize(installation=True)
            self.assertEqual(calls, ["recovery", "unlock"])

    def test_locked_out_user_is_not_even_prompted(self):
        with mock.patch.object(m, "is_admin", return_value=True), mock.patch.object(m, "is_system", return_value=False), \
             mock.patch.object(m, "read_json", side_effect=[{"maintenance_password_hash": verifier()}, {"locked_until": 1500}]), \
             mock.patch.object(m.time, "time", return_value=1000), mock.patch.object(m, "prompt_password") as prompt:
            with self.assertRaises(PermissionError):
                m.authorize(installation=True)
            prompt.assert_not_called()

    def test_active_lease_is_not_relocked_by_recovery(self):
        with mock.patch.object(m, "is_admin", return_value=True), \
             mock.patch.object(m, "read_json", side_effect=[{"maintenance_password_hash": verifier()}, {"until": 1400}]), \
             mock.patch.object(m.time, "time", return_value=1000), \
             mock.patch.object(m, "command") as command:
            m.enforce()
            command.assert_not_called()

    def test_expiration_relocks_and_only_resumes_previously_running_services(self):
        with mock.patch.object(m, "is_admin", return_value=True), \
             mock.patch.object(m, "read_json", side_effect=[{"maintenance_password_hash": verifier()}, {"until": 999, "resume": ["AtivaUnifiedUpdater", "UnrelatedService"]}]), \
             mock.patch.object(m.time, "time", return_value=1000), \
             mock.patch.object(m, "service_exists", return_value=True), \
             mock.patch.object(m, "protect_files"), mock.patch.object(m, "save_state") as save, \
             mock.patch.object(m, "command") as command:
            m.enforce()
            command.assert_any_call("sc.exe", "sdset", "AtivaGuardian", m.LOCKED_SERVICE_DACL)
            command.assert_any_call("sc.exe", "start", "AtivaUnifiedUpdater", required=False)
            self.assertFalse(any("UnrelatedService" in call.args for call in command.call_args_list))
            self.assertEqual(save.call_args.args[0]["until"], 0)

    def test_legacy_install_without_password_still_authorizes_upgrade(self):
        with mock.patch.object(m, "is_admin", return_value=True), \
             mock.patch.object(m, "read_json", return_value={}), mock.patch.object(m, "open_lease") as opened:
            m.authorize(installation=True)
            opened.assert_not_called()
            with self.assertRaises(RuntimeError):
                m.authorize()

    def test_restoration_does_not_touch_unrelated_services(self):
        self.assertEqual(set(m.SERVICES), {"AtivaGuardian", "AtivaUnifiedUpdater", "RustDesk", "glpi-agent", "GLPIAgent"})


if __name__ == "__main__":
    unittest.main()
