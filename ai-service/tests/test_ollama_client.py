import unittest
from unittest.mock import patch

import httpx

from app.services.ollama_client import OllamaClient, OllamaError


class OllamaClientTest(unittest.TestCase):
    @patch("app.services.ollama_client.httpx.post")
    def test_invalid_json_response_raises_ollama_error(self, post) -> None:
        post.return_value = self.response(text="not json")

        with self.assertRaisesRegex(OllamaError, "invalid JSON response"):
            OllamaClient().generate("prompt")

    @patch("app.services.ollama_client.httpx.post")
    def test_non_object_json_response_raises_ollama_error(self, post) -> None:
        post.return_value = self.response(json=[])

        with self.assertRaisesRegex(OllamaError, "invalid JSON response"):
            OllamaClient().generate("prompt")

    @staticmethod
    def response(**kwargs) -> httpx.Response:
        request = httpx.Request("POST", "http://127.0.0.1:11434/api/generate")

        return httpx.Response(200, request=request, **kwargs)


if __name__ == "__main__":
    unittest.main()
