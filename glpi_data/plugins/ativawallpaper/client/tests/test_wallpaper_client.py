from __future__ import annotations

import hashlib
import importlib.util
import io
import json
from pathlib import Path
import tempfile
import unittest
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
            self.assertEqual(Path(state["wallpaper_path"]).read_bytes(), content)
            self.assertEqual(len(applied), 1)
            self.assertEqual(api.reports[0]["status"], "success")

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

    def test_api_parses_json_and_sends_bearer_token(self):
        response = FakeResponse(json.dumps({"enabled": False}).encode())
        opener = FakeOpener(response)
        api = api_with_opener(opener)
        data, etag = api.get_config(None)
        self.assertEqual(data, {"enabled": False})
        self.assertEqual(etag, "fake-etag")
        self.assertEqual(opener.requests[0].get_header("Authorization"), "Bearer " + "t" * 43)

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


if __name__ == "__main__":
    unittest.main()
