from __future__ import annotations

import hashlib
import importlib.util
import io
import json
from pathlib import Path
import tempfile
import unittest
from unittest import mock
from urllib import error


MODULE_PATH = Path(__file__).resolve().parents[1] / "wallpaper_client.py"
SPEC = importlib.util.spec_from_file_location("wallpaper_client", MODULE_PATH)
assert SPEC and SPEC.loader
wc = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(wc)


class FakeApi:
    def __init__(self, server_config: dict | None, content: bytes = b"new-image", etag: str = "etag-1") -> None:
        self.server_config = server_config
        self.content = content
        self.etag = etag
        self.reports: list[dict] = []
        self.heartbeats: list[dict] = []
        self.downloads = 0

    def get_config(self, etag: str | None):
        return self.server_config, self.etag

    def download(self, url: str, destination: Path, expected_sha256: str, expected_size: int | None = None) -> None:
        self.downloads += 1
        if hashlib.sha256(self.content).hexdigest() != expected_sha256:
            raise wc.ClientError("HASH_MISMATCH", "bad hash")
        destination.write_bytes(self.content)

    def report(self, payload: dict) -> None:
        self.reports.append(payload)

    def heartbeat(self, payload: dict) -> None:
        self.heartbeats.append(payload)


class FakeResponse:
    def __init__(self, body: bytes, *, status: int = 200, content_type: str = "application/json", fail_after_first_read: bool = False) -> None:
        self.body = body
        self.status = status
        self.offset = 0
        self.fail_after_first_read = fail_after_first_read
        self.read_count = 0
        self.headers = {"Content-Type": content_type, "Content-Length": str(len(body)), "ETag": '"fake-etag"'}

    def __enter__(self):
        return self

    def __exit__(self, *_args):
        return None

    def read(self, size: int = -1) -> bytes:
        if self.fail_after_first_read and self.read_count > 0:
            raise OSError("connection interrupted")
        self.read_count += 1
        if size < 0:
            size = len(self.body) - self.offset
        chunk = self.body[self.offset:self.offset + size]
        self.offset += len(chunk)
        return chunk


class FakeOpener:
    def __init__(self, response=None, exception: Exception | None = None) -> None:
        self.response = response
        self.exception = exception
        self.requests = []

    def open(self, req, timeout=None):
        self.requests.append(req)
        if self.exception:
            raise self.exception
        return self.response


def api_with_opener(opener: FakeOpener, token: str | None = "t" * 43):
    api = wc.ApiClient.__new__(wc.ApiClient)
    api.server = "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1"
    api.token = token
    api.timeout = 30
    api.opener = opener
    return api


class ClientTests(unittest.TestCase):
    def test_version_can_be_written_without_a_console(self):
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "version.txt"
            self.assertEqual(wc.main(["--version-file", str(destination)]), 0)
            self.assertEqual(destination.read_text(encoding="utf-8").strip(), wc.CLIENT_VERSION)

    @unittest.skipUnless(wc.os.name == "nt", "Windows named mutex test")
    def test_single_instance_mutex_rejects_second_process(self):
        original_user_key = wc.user_key
        wc.user_key = lambda: "unit-test-exclusive-mutex"
        try:
            with wc.SingleInstance():
                with self.assertRaises(SystemExit) as caught:
                    with wc.SingleInstance():
                        pass
                self.assertEqual(caught.exception.code, 0)
        finally:
            wc.user_key = original_user_key

    def test_registry_style_mapping(self):
        self.assertEqual(wc.wallpaper_registry_values("fill"), ("10", "0"))
        self.assertEqual(wc.wallpaper_registry_values("fit"), ("6", "0"))
        self.assertEqual(wc.wallpaper_registry_values("stretch"), ("2", "0"))
        self.assertEqual(wc.wallpaper_registry_values("center"), ("0", "0"))
        self.assertEqual(wc.wallpaper_registry_values("tile"), ("0", "1"))
        self.assertEqual(wc.wallpaper_registry_values("span"), ("22", "0"))

    def test_server_url_requires_https_hostname_and_api_path(self):
        good = "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1/"
        self.assertEqual(wc.normalize_server_url(good), good.rstrip("/"))
        for bad in (
            "http://chamados.ativalocacao.com.br/plugins/ativawallpaper/api/v1",
            "https://10.117.41.6:8443/plugins/ativawallpaper/api/v1",
            "https://wallpaper.example.com/plugins/ativawallpaper/api/v1",
            "https://chamados.ativalocacao.com.br:8443/",
        ):
            with self.assertRaises(wc.ClientError):
                wc.normalize_server_url(bad)

    def test_interval_is_bounded(self):
        self.assertEqual(wc.bounded_interval(10), 60)
        self.assertEqual(wc.bounded_interval(900), 900)
        self.assertEqual(wc.bounded_interval(999999), 86400)

    def test_version_comparison(self):
        self.assertLess(wc.compare_versions("1.0.0", "1.1.0"), 0)
        self.assertEqual(wc.compare_versions("1.0", "1.0.0"), 0)
        self.assertGreater(wc.compare_versions("2.0.0", "1.9.9"), 0)

    def test_heartbeat_reports_exact_next_cycle_and_result(self):
        api = FakeApi(None)
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            wc.atomic_write_json(root / "client.json", {
                "server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1",
                "client_token": "x" * 43,
                "verify_tls": True,
            })
            client = wc.WallpaperClient(root, api_factory=lambda *_args: api)
            client.identity = lambda: {
                "hostname": "PC-01",
                "machine_guid": "guid-12345678",
                "username": "test",
                "client_version": "1.4.0",
                "os_version": "Windows 11",
            }
            wc.atomic_write_json(client.state_path, {"last_cycle_action": "already_current"})

            client.report_heartbeat(67)

            self.assertEqual(len(api.heartbeats), 1)
            self.assertEqual(api.heartbeats[0]["next_check_seconds"], 67)
            self.assertEqual(api.heartbeats[0]["cycle_action"], "already_current")
            self.assertEqual(api.heartbeats[0]["hostname"], "PC-01")

    def test_should_download_compares_version_revision_and_hash(self):
        server = {"enabled": True, "wallpaper_version": "20260908-001", "config_revision": "r1", "sha256": "a" * 64}
        local = {**server, "wallpaper_path": "x"}
        self.assertFalse(wc.should_download(server, local))
        self.assertTrue(wc.should_download({**server, "force_reapply": True}, local))
        self.assertTrue(wc.should_download({**server, "config_revision": "r2"}, local))

    def test_atomic_json_and_sha(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "state.json"
            wc.atomic_write_json(path, {"ok": True})
            self.assertEqual(wc.load_json(path), {"ok": True})
            self.assertEqual(wc.sha256_file(path), hashlib.sha256(path.read_bytes()).hexdigest())
            self.assertFalse(path.with_name("state.json.tmp").exists())

    def test_invalid_json_is_rejected(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "client.json"
            path.write_text("<html>not json</html>", encoding="utf-8")
            with self.assertRaisesRegex(wc.ClientError, "Cannot read"):
                wc.load_json(path)

    def test_successful_sync_downloads_applies_and_reports(self):
        content = b"verified-wallpaper"
        digest = hashlib.sha256(content).hexdigest()
        server = {
            "enabled": True,
            "wallpaper_version": "20260908-001",
            "config_revision": "revision-1",
            "download_url": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1/wallpaper/20260908-001/download",
            "sha256": digest,
            "style": "fill",
            "mime_type": "image/jpeg",
            "filesize": len(content),
            "poll_interval_seconds": 900,
            "poll_jitter_seconds": 120,
            "lock_change": False,
            "force_reapply": False,
        }
        api = FakeApi(server, content)
        applied: list[tuple[Path, str]] = []
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            wc.atomic_write_json(root / "client.json", {
                "server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1",
                "client_token": "x" * 43,
                "verify_tls": True,
            })
            client = wc.WallpaperClient(
                root,
                api_factory=lambda _server, _token: api,
                apply_function=lambda path, style: applied.append((path, style)),
                policy_function=lambda *_args: None,
            )
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.0.0", "os_version": "Windows 11"}
            self.assertEqual(client.sync_once(), (900, 120))
            state = wc.load_json(client.state_path)
            self.assertEqual(state["wallpaper_version"], "20260908-001")
            self.assertFalse(state["status_pending"])
            self.assertEqual(Path(state["wallpaper_path"]).name, "wallpaper-20260908-001.jpg")
            self.assertEqual(Path(state["wallpaper_path"]).read_bytes(), content)
            self.assertEqual(len(applied), 1)
            self.assertEqual(api.reports[0]["status"], "success")
            self.assertEqual(api.reports[0]["apply_reason"], "initial_applied")
            self.assertRegex(api.reports[0]["apply_event_id"], r"^[a-f0-9]{32}$")

    def test_force_reapply_reuses_verified_cache_and_temporarily_releases_policy(self):
        content = b"verified-wallpaper"
        digest = hashlib.sha256(content).hexdigest()
        server = {
            "enabled": True,
            "wallpaper_version": "20260908-001",
            "config_revision": "revision-1",
            "rollout_id": "rollout-42",
            "download_url": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1/wallpaper/20260908-001/download",
            "sha256": digest,
            "style": "fill",
            "mime_type": "image/jpeg",
            "filesize": len(content),
            "poll_interval_seconds": 60,
            "poll_jitter_seconds": 10,
            "lock_change": True,
            "force_reapply": True,
        }
        api = FakeApi(server, content)
        applied = []
        policies = []
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            cached = root / "data" / "wallpaper-20260908-001.jpg"
            cached.parent.mkdir(parents=True)
            cached.write_bytes(content)
            wc.atomic_write_json(root / "client.json", {
                "server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1",
                "client_token": "x" * 43,
                "verify_tls": True,
            })
            client = wc.WallpaperClient(
                root,
                api_factory=lambda *_args: api,
                apply_function=lambda path, style: applied.append((path, style)),
                policy_function=lambda lock, path, style, _state: policies.append((lock, path, style)),
                current_function=lambda *_args: True,
            )
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.4.0", "os_version": "Windows 11"}
            wc.atomic_write_json(client.state_path, {
                "wallpaper_version": "20260908-001",
                "config_revision": "revision-1",
                "sha256": digest,
                "style": "fill",
                "wallpaper_path": str(cached),
                "lock_change": True,
                "distribution_enabled": True,
            })

            client.sync_once()

            self.assertEqual(api.downloads, 0)
            self.assertEqual(applied, [(cached, "fill")])
            # Restore the cached policy before the request, then temporarily
            # release it for the explicit force-reapply and restore it again.
            self.assertEqual([entry[0] for entry in policies], [True, False, True])
            self.assertEqual(api.reports[0]["rollout_id"], "rollout-42")
            self.assertEqual(api.reports[0]["apply_reason"], "forced_applied")

    def test_304_detects_manual_change_and_restores_cached_wallpaper(self):
        content = b"corporate-wallpaper"
        digest = hashlib.sha256(content).hexdigest()
        api = FakeApi(None)
        applied = []
        policies = []
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            cached = root / "data" / "wallpaper-20260908-001.jpg"
            cached.parent.mkdir(parents=True)
            cached.write_bytes(content)
            wc.atomic_write_json(root / "client.json", {
                "server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1",
                "client_token": "x" * 43,
                "verify_tls": True,
            })
            client = wc.WallpaperClient(
                root,
                api_factory=lambda *_args: api,
                apply_function=lambda path, style: applied.append((path, style)),
                policy_function=lambda lock, path, style, _state: policies.append((lock, path, style)),
                current_function=lambda *_args: False,
            )
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.4.0", "os_version": "Windows 11"}
            wc.atomic_write_json(client.state_path, {
                "wallpaper_version": "20260908-001",
                "sha256": digest,
                "style": "fill",
                "wallpaper_path": str(cached),
                "lock_change": True,
                "distribution_enabled": True,
                "config_etag": "etag-1",
                "poll_interval_seconds": 60,
                "poll_jitter_seconds": 10,
            })

            client.sync_once()

            self.assertEqual(applied, [(cached, "fill")])
            self.assertEqual([entry[0] for entry in policies], [False, True])
            self.assertEqual(api.reports[0]["status"], "success")
            self.assertEqual(api.reports[0]["apply_reason"], "drift_corrected")
            self.assertRegex(api.reports[0]["apply_event_id"], r"^[a-f0-9]{32}$")
            self.assertFalse(wc.load_json(client.state_path)["status_pending"])

    def test_apply_failure_restores_previous_file_and_state(self):
        old = b"old-wallpaper"
        new = b"new-wallpaper"
        new_hash = hashlib.sha256(new).hexdigest()
        server = {
            "enabled": True, "wallpaper_version": "20260908-002", "config_revision": "r2",
            "download_url": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1/wallpaper/20260908-002/download",
            "sha256": new_hash, "style": "fit", "mime_type": "image/jpeg", "filesize": len(new),
        }
        api = FakeApi(server, new)
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            old_path = root / "data" / "wallpaper.jpg"
            old_path.parent.mkdir(parents=True)
            old_path.write_bytes(old)
            wc.atomic_write_json(root / "client.json", {"server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1", "client_token": "x" * 43, "verify_tls": True})
            client = wc.WallpaperClient(
                root,
                api_factory=lambda _server, _token: api,
                apply_function=lambda path, _style: (_ for _ in ()).throw(wc.ClientError("SPI_ERROR", "failed")) if path.read_bytes() == new else None,
                policy_function=lambda *_args: None,
            )
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.0.0", "os_version": "Windows 11"}
            wc.atomic_write_json(client.state_path, {"wallpaper_version": "20260908-001", "config_revision": "r1", "sha256": hashlib.sha256(old).hexdigest(), "style": "fill", "wallpaper_path": str(old_path)})
            with self.assertRaises(wc.ClientError):
                client.sync_once()
            self.assertEqual(old_path.read_bytes(), old)
            self.assertEqual(wc.load_json(client.state_path)["wallpaper_version"], "20260908-001")

    def test_hash_mismatch_does_not_replace_destination(self):
        content = b"downloaded"
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "wallpaper.download"
            destination.write_bytes(b"existing")
            api = api_with_opener(FakeOpener(FakeResponse(content, content_type="image/jpeg")))
            with self.assertRaises(wc.ClientError):
                api.download(api.server + "/wallpaper/v/download", destination, "0" * 64, len(content))
            self.assertEqual(destination.read_bytes(), b"existing")

    def test_interrupted_download_keeps_previous_and_removes_partial(self):
        content = b"x" * (1024 * 1024 + 5)
        response = FakeResponse(content, content_type="image/png", fail_after_first_read=True)
        api = api_with_opener(FakeOpener(response))
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "wallpaper.download"
            destination.write_bytes(b"previous")
            with self.assertRaisesRegex(wc.ClientError, "download failed"):
                api.download(api.server + "/wallpaper/v/download", destination, hashlib.sha256(content).hexdigest(), len(content))
            self.assertEqual(destination.read_bytes(), b"previous")
            self.assertFalse(destination.with_name(destination.name + ".part").exists())

    def test_binary_download_accepts_update_content_type_and_larger_limit(self):
        content = b"MZ" + (b"x" * 1024)
        api = api_with_opener(FakeOpener(FakeResponse(content, content_type="application/octet-stream")))
        with tempfile.TemporaryDirectory() as directory:
            destination = Path(directory) / "client.exe"
            api.download(
                api.server + "/updates/7/download",
                destination,
                hashlib.sha256(content).hexdigest(),
                len(content),
                maximum_bytes=250 * 1024 * 1024,
                allowed_content_types=("application/octet-stream",),
            )
            self.assertEqual(destination.read_bytes(), content)

    def test_api_parses_json_and_sends_compatible_token_headers(self):
        response = FakeResponse(json.dumps({"enabled": False}).encode())
        opener = FakeOpener(response)
        api = api_with_opener(opener)
        data, etag = api.get_config(None)
        self.assertEqual(data, {"enabled": False})
        self.assertEqual(etag, "fake-etag")
        self.assertEqual(opener.requests[0].get_header("Authorization"), "Bearer " + "t" * 43)
        self.assertEqual(opener.requests[0].get_header("X-ativa-client-token"), "t" * 43)

    def test_api_checks_and_reports_updates(self):
        check_response = FakeResponse(json.dumps({"updates": [{"id": 7, "component": "wallpaper_client"}]}).encode())
        check_opener = FakeOpener(check_response)
        api = api_with_opener(check_opener)
        updates = api.check_updates({"wallpaper_client_version": "1.4.0"})
        self.assertEqual(updates[0]["id"], 7)
        self.assertTrue(check_opener.requests[0].full_url.endswith("/updates/check"))

        status_opener = FakeOpener(FakeResponse(b"{}", status=202))
        api = api_with_opener(status_opener)
        api.report_update({"update_id": 7, "status": "success"})
        self.assertTrue(status_opener.requests[0].full_url.endswith("/updates/status"))

    def test_api_rejects_html_instead_of_json(self):
        api = api_with_opener(FakeOpener(FakeResponse(b"<!DOCTYPE html>", content_type="text/html")))
        with self.assertRaisesRegex(wc.ClientError, "did not return JSON"):
            api.get_config(None)

    def test_server_offline_is_a_retriable_error(self):
        api = api_with_opener(FakeOpener(exception=error.URLError("offline")))
        with self.assertRaises(wc.ClientError) as caught:
            api.get_config(None)
        self.assertEqual(caught.exception.code, "SERVER_UNAVAILABLE")
        self.assertTrue(caught.exception.retriable)

    def test_invalid_token_http_401_is_not_retriable(self):
        body = io.BytesIO(b'{"error":{"message":"Token invalido"}}')
        failure = error.HTTPError(api_with_opener(FakeOpener()).server + "/config", 401, "Unauthorized", {"Content-Type": "application/json"}, body)
        api = api_with_opener(FakeOpener(exception=failure))
        with self.assertRaises(wc.ClientError) as caught:
            api.get_config(None)
        self.assertEqual(caught.exception.code, "HTTP_401")
        self.assertFalse(caught.exception.retriable)

    def test_register_and_status_payloads(self):
        token = "new-client-token-" + "x" * 32
        register_opener = FakeOpener(FakeResponse(json.dumps({"client_token": token}).encode(), status=201))
        register_api = api_with_opener(register_opener, token=None)
        returned = register_api.register("s" * 43, {"hostname": "PC-01", "machine_guid": "guid-12345678", "client_version": "1.0.0"})
        self.assertEqual(returned, token)
        register_body = json.loads(register_opener.requests[0].data.decode())
        self.assertEqual(register_body["registration_secret"], "s" * 43)

        status_opener = FakeOpener(FakeResponse(b'{"status":"accepted"}', status=202))
        status_api = api_with_opener(status_opener)
        status_api.report({"status": "success"})
        self.assertEqual(status_opener.requests[0].method, "POST")

    def test_304_retries_pending_status(self):
        api = FakeApi(None)
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            wc.atomic_write_json(root / "client.json", {"server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1", "client_token": "x" * 43, "verify_tls": True})
            client = wc.WallpaperClient(root, api_factory=lambda _server, _token: api, apply_function=lambda *_args: None, policy_function=lambda *_args: None)
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.0.0", "os_version": "Windows 11"}
            wc.atomic_write_json(client.state_path, {"wallpaper_version": "20260908-001", "sha256": "a" * 64, "status_pending": True, "poll_interval_seconds": 900, "poll_jitter_seconds": 120})
            client.sync_once()
            self.assertEqual(len(api.reports), 1)
            self.assertFalse(wc.load_json(client.state_path)["status_pending"])

    def test_disabled_server_keeps_existing_wallpaper(self):
        api = FakeApi({"enabled": False, "config_revision": "disabled", "poll_interval_seconds": 900, "poll_jitter_seconds": 120})
        policies = []
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            wallpaper = root / "data" / "wallpaper.jpg"
            wallpaper.parent.mkdir(parents=True)
            wallpaper.write_bytes(b"keep-me")
            wc.atomic_write_json(root / "client.json", {"server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1", "client_token": "x" * 43, "verify_tls": True})
            client = wc.WallpaperClient(root, api_factory=lambda _server, _token: api, apply_function=lambda *_args: None, policy_function=lambda *args: policies.append(args))
            client.identity = lambda: {"hostname": "PC-01", "machine_guid": "guid-12345678", "username": "test", "client_version": "1.0.0", "os_version": "Windows 11"}
            wc.atomic_write_json(client.state_path, {"wallpaper_path": str(wallpaper), "wallpaper_version": "old"})
            client.sync_once()
            self.assertEqual(wallpaper.read_bytes(), b"keep-me")
            self.assertFalse(policies[0][0])

    def test_config_cannot_disable_tls_verification(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            wc.atomic_write_json(root / "client.json", {"server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1", "client_token": "x" * 43, "verify_tls": False})
            client = wc.WallpaperClient(root, api_factory=lambda *_args: FakeApi(None), apply_function=lambda *_args: None, policy_function=lambda *_args: None)
            with self.assertRaises(wc.ClientError) as caught:
                client.load_config()
            self.assertEqual(caught.exception.code, "TLS_VERIFICATION_DISABLED")


class OfflineStartupTests(unittest.TestCase):
    def test_cache_is_applied_and_saved_before_network_failure(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            image = root / "wallpaper.jpg"
            image.write_bytes(b"verified cached image")
            calls = []
            api = mock.Mock()
            def offline(_etag):
                calls.append("network")
                raise wc.ClientError("NETWORK_ERROR", "offline")
            api.get_config.side_effect = offline
            client = wc.WallpaperClient(root, api_factory=lambda *_: api,
                apply_function=lambda *_: calls.append("apply"), policy_function=lambda *_: None,
                current_function=lambda *_: False)
            client.identity = lambda: {}
            wc.atomic_write_json(root / "client.json", {
                "server": "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1",
                "client_token": "x" * 43,
            })
            wc.atomic_write_json(client.state_path, {"distribution_enabled": True,
                "wallpaper_path": str(image), "sha256": wc.sha256_file(image),
                "wallpaper_version": "v1", "style": "fill", "lock_change": True})
            with self.assertRaises(wc.ClientError):
                client.sync_once()
            self.assertEqual(calls, ["apply", "network"])
            self.assertTrue(wc.load_json(client.state_path)["status_pending"])
            api.report.assert_not_called()

    def test_disabled_or_corrupt_cache_is_not_applied(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            image = root / "image.jpg"
            image.write_bytes(b"image")
            apply = mock.Mock()
            client = wc.WallpaperClient(root, apply_function=apply, policy_function=lambda *_: None)
            state = {"distribution_enabled": False, "wallpaper_path": str(image), "sha256": wc.sha256_file(image)}
            self.assertFalse(client.enforce_cached_wallpaper(state))
            state.update(distribution_enabled=True, sha256="0" * 64, config_etag="old")
            self.assertFalse(client.enforce_cached_wallpaper(state))
            self.assertNotIn("config_etag", state)
            apply.assert_not_called()

    def test_normal_stop_preserves_policy_for_next_logon(self):
        client = mock.Mock()
        client.sync_once.return_value = (60, 0)
        stop = mock.Mock()
        stop.wait.return_value = True
        stop.flag_path.exists.return_value = False
        with mock.patch.object(wc, "current_session_id", return_value=1), \
             mock.patch.object(wc, "ensure_supported_windows"), mock.patch.object(wc, "WallpaperClient", return_value=client), \
             mock.patch.object(wc, "StopEvent", return_value=stop), mock.patch.object(wc, "SingleInstance"), \
             mock.patch.object(wc, "protect_session_process"), mock.patch.object(wc, "UPDATE_RESTART_FLAG") as flag:
            flag.exists.return_value = False
            self.assertEqual(wc.run_client(False, False), 0)
            client.policy_function.assert_not_called()

    def test_logon_task_uses_interactive_user_and_no_network_dependency(self):
        from xml.etree import ElementTree as ET
        xml = wc.logon_task_xml(Path(r"C:\ProgramData\Ativa & Test\Client.exe"))
        task = ET.fromstring(xml)
        ns = {"t": "http://schemas.microsoft.com/windows/2004/02/mit/task"}
        self.assertEqual(task.findtext("t:Principals/t:Principal/t:GroupId", namespaces=ns), "S-1-5-32-545")
        self.assertEqual(task.findtext("t:Principals/t:Principal/t:RunLevel", namespaces=ns), "LeastPrivilege")
        self.assertIn("Ativa & Test", task.findtext("t:Actions/t:Exec/t:Command", namespaces=ns))
        self.assertIsNotNone(task.find("t:Triggers/t:LogonTrigger", ns))
        self.assertNotIn("RunOnlyIfNetworkAvailable", xml)


class SilentUpdateTests(unittest.TestCase):
    def test_client_refuses_to_run_in_session_zero(self):
        original = wc.current_session_id
        wc.current_session_id = lambda: 0
        try:
            with self.assertRaises(wc.ClientError) as caught:
                wc.run_client(once=True, debug=False)
            self.assertEqual(caught.exception.code, "INTERACTIVE_SESSION_REQUIRED")
        finally:
            wc.current_session_id = original

    @unittest.skipUnless(wc.os.name == "nt", "Windows session API")
    def test_interactive_test_process_is_not_in_session_zero(self):
        self.assertNotEqual(wc.current_session_id(), 0)

    def test_replace_executable_waits_for_the_stopping_client(self):
        with tempfile.TemporaryDirectory() as directory:
            staged = Path(directory) / "client.exe.new"
            destination = Path(directory) / "client.exe"
            staged.write_bytes(b"new")
            destination.write_bytes(b"old")
            real_replace = wc.os.replace
            calls = []

            def flaky_replace(source, target):
                calls.append(source)
                if len(calls) < 3:
                    raise PermissionError("file in use")
                real_replace(source, target)

            wc.os.replace = flaky_replace
            try:
                wc.replace_executable(staged, destination, attempts=5, delay_seconds=0)
            finally:
                wc.os.replace = real_replace
            self.assertEqual(len(calls), 3)
            self.assertEqual(destination.read_bytes(), b"new")

    def test_replace_renames_locked_executable_out_of_the_way(self):
        """Cenario real: cliente antigo em execucao nao libera o .exe.

        os.replace falha sempre; o rename do destino e o que destrava. Sem isso
        a instalacao terminava em CLIENT_REPLACE_FAILED numa maquina que so
        tinha uma versao antiga rodando.
        """
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            staged = root / "client.exe.new"
            staged.write_bytes(b"new")
            destination = root / "client.exe"
            destination.write_bytes(b"old")

            real_replace = wc.os.replace
            calls = {"n": 0}

            def locked(source, target):
                calls["n"] += 1
                if Path(target) == destination and destination.exists():
                    raise PermissionError("file in use")
                return real_replace(source, target)

            wc.os.replace = locked
            try:
                wc.replace_executable(staged, destination, attempts=2, delay_seconds=0)
            finally:
                wc.os.replace = real_replace

            self.assertEqual(destination.read_bytes(), b"new")
            self.assertFalse(staged.exists())
            retired = list(root.glob("client.exe.old-*"))
            self.assertEqual(len(retired), 1, "o executavel antigo deve ficar guardado ao lado")
            self.assertEqual(retired[0].read_bytes(), b"old")

    def test_replace_restores_original_when_rename_does_not_help(self):
        """Se mesmo apos o rename nao der para gravar, a maquina nao fica sem cliente."""
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            staged = root / "client.exe.new"
            staged.write_bytes(b"new")
            destination = root / "client.exe"
            destination.write_bytes(b"old")

            real_replace = wc.os.replace
            wc.os.replace = lambda _s, _t: (_ for _ in ()).throw(PermissionError("file in use"))
            try:
                with self.assertRaises(wc.ClientError) as caught:
                    wc.replace_executable(staged, destination, attempts=1, delay_seconds=0)
            finally:
                wc.os.replace = real_replace

            self.assertEqual(caught.exception.code, "CLIENT_REPLACE_FAILED")
            self.assertTrue(destination.exists(), "o executavel original deve voltar ao lugar")
            self.assertEqual(destination.read_bytes(), b"old")
            self.assertEqual(list(root.glob("client.exe.old-*")), [])

    def test_replace_purges_leftovers_from_previous_upgrades(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "client.exe.old-20250101000000").write_bytes(b"antigo")
            staged = root / "client.exe.new"
            staged.write_bytes(b"new")
            destination = root / "client.exe"

            wc.replace_executable(staged, destination, attempts=1, delay_seconds=0)

            self.assertEqual(destination.read_bytes(), b"new")
            self.assertEqual(list(root.glob("client.exe.old-*")), [])

    def test_replace_executable_gives_up_and_cleans_staged_file(self):
        with tempfile.TemporaryDirectory() as directory:
            staged = Path(directory) / "client.exe.new"
            staged.write_bytes(b"new")
            real_replace = wc.os.replace

            def locked(_source, _target):
                raise PermissionError("file in use")

            wc.os.replace = locked
            try:
                with self.assertRaises(wc.ClientError) as caught:
                    wc.replace_executable(staged, Path(directory) / "client.exe", attempts=2, delay_seconds=0)
            finally:
                wc.os.replace = real_replace
            self.assertEqual(caught.exception.code, "CLIENT_REPLACE_FAILED")
            self.assertFalse(staged.exists())


class ReusableRegistrationTests(unittest.TestCase):
    SERVER = "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1"

    def setUp(self):
        directory = tempfile.TemporaryDirectory()
        self.addCleanup(directory.cleanup)
        self.root = Path(directory.name)
        self.logger = wc.logging.getLogger("wallpaper-registration-tests")
        self.logger.addHandler(wc.logging.NullHandler())
        self.logger.propagate = False
        self.calls = []

    def factory(self, error_code=None):
        def build(server, token):
            self.calls.append((server, token))
            api = FakeApi({"enabled": True})
            if error_code:
                def fail(_etag):
                    raise wc.ClientError(error_code, "rejected", retriable=False)
                api.get_config = fail
            return api
        return build

    def write_client_json(self, **values):
        wc.atomic_write_json(self.root / "client.json", {"server": self.SERVER, "client_token": "t" * 43, **values})

    def test_accepted_token_is_reused_without_registering(self):
        self.write_client_json()
        token = wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory())
        self.assertEqual(token, "t" * 43)
        self.assertEqual(self.calls, [(self.SERVER, "t" * 43)])

    def test_rejected_token_falls_back_to_registration(self):
        self.write_client_json()
        self.assertIsNone(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory("HTTP_401")))

    def test_unreachable_server_keeps_the_existing_registration(self):
        # A network timeout during the unified installation must not abort it.
        self.write_client_json()

        def unreachable(server, token):
            api = FakeApi({"enabled": True})

            def fail(_etag):
                raise wc.ClientError("SERVER_UNAVAILABLE", "timed out")
            api.get_config = fail
            return api

        self.assertEqual(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=unreachable), "t" * 43)

    def test_registration_retries_only_transient_failures(self):
        class FlakyApi:
            def __init__(self, errors):
                self.errors = list(errors)
                self.calls = 0

            def register(self, _secret, _identity):
                self.calls += 1
                if self.errors:
                    raise self.errors.pop(0)
                return "token-" + "x" * 40

        sleeps = []
        flaky = FlakyApi([wc.ClientError("SERVER_UNAVAILABLE", "timeout"), wc.ClientError("HTTP_503", "busy")])
        self.assertTrue(wc.register_with_retries(flaky, "s", {}, self.logger, sleep=sleeps.append).startswith("token-"))
        self.assertEqual((flaky.calls, sleeps), (3, [5, 15]))

        rejected = FlakyApi([wc.ClientError("HTTP_401", "bad secret", retriable=False)])
        with self.assertRaises(wc.ClientError):
            wc.register_with_retries(rejected, "s", {}, self.logger, sleep=sleeps.append)
        self.assertEqual(rejected.calls, 1)

    def test_missing_invalid_or_foreign_registration_is_not_reused(self):
        self.assertIsNone(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory()))
        self.write_client_json(client_token="short")
        self.assertIsNone(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory()))
        self.write_client_json(server="https://chamados.ativalocacao.com.br:9443/plugins/ativawallpaper/api/v1")
        self.assertIsNone(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory()))
        (self.root / "client.json").write_text("not json", encoding="utf-8")
        self.assertIsNone(wc.reusable_client_token(self.root, self.SERVER, self.logger, api_factory=self.factory()))
        self.assertEqual(self.calls, [])


if __name__ == "__main__":
    unittest.main()
