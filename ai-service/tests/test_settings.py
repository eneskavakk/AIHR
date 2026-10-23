import unittest

from pydantic import ValidationError

from app.core.settings import Settings


class SettingsTest(unittest.TestCase):
    def test_rejects_non_positive_ollama_timeout(self) -> None:
        with self.assertRaises(ValidationError):
            Settings(ollama_timeout_seconds=0)

    def test_rejects_negative_analysis_retry_count(self) -> None:
        with self.assertRaises(ValidationError):
            Settings(analysis_retry_count=-1)


if __name__ == "__main__":
    unittest.main()
