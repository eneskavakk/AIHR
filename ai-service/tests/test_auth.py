import unittest
from unittest.mock import patch

from fastapi.testclient import TestClient

from app.main import app


class ApiTokenAuthenticationTest(unittest.TestCase):
    def setUp(self) -> None:
        self.client = TestClient(app)

    @patch("app.core.auth.settings.api_token", "secret-token")
    def test_protected_endpoint_rejects_missing_token(self) -> None:
        response = self.client.post("/parse-cv")

        self.assertEqual(response.status_code, 401)
        self.assertEqual(response.headers["www-authenticate"], "Bearer")

    @patch("app.core.auth.settings.api_token", "secret-token")
    def test_protected_endpoint_rejects_invalid_token(self) -> None:
        response = self.client.post(
            "/parse-cv",
            headers={"Authorization": "Bearer wrong-token"},
        )

        self.assertEqual(response.status_code, 403)

    @patch("app.core.auth.settings.api_token", "secret-token")
    def test_protected_endpoint_accepts_valid_token(self) -> None:
        response = self.client.post(
            "/parse-cv",
            headers={"Authorization": "Bearer secret-token"},
        )

        self.assertEqual(response.status_code, 422)


if __name__ == "__main__":
    unittest.main()
