import unittest

import fitz
from fastapi.testclient import TestClient

from app.main import app


def make_pdf(text: str) -> bytes:
    document = fitz.open()
    page = document.new_page()
    page.insert_text((72, 72), text)
    return document.tobytes()


class ParseCvTest(unittest.TestCase):
    def setUp(self) -> None:
        self.client = TestClient(app)

    def test_parse_cv_extracts_and_cleans_pdf_text(self) -> None:
        pdf_bytes = make_pdf("Ahmet Yilmaz\nPython   Developer\nFastAPI")

        response = self.client.post(
            "/parse-cv",
            files={"file": ("cv.pdf", pdf_bytes, "application/pdf")},
        )

        self.assertEqual(response.status_code, 200)

        payload = response.json()

        self.assertTrue(payload["success"])
        self.assertIn("Python", payload["raw_text"])
        self.assertIn("Python Developer", payload["cleaned_text"])
        self.assertEqual(payload["page_count"], 1)

    def test_parse_cv_rejects_non_pdf_uploads(self) -> None:
        response = self.client.post(
            "/parse-cv",
            files={"file": ("cv.txt", b"not a pdf", "text/plain")},
        )

        self.assertEqual(response.status_code, 415)


if __name__ == "__main__":
    unittest.main()
