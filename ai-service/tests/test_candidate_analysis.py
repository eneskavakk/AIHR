import json
import unittest

from app.schemas.analysis import AnalyzeCandidateRequest
from app.services.candidate_analysis import CandidateAnalysisService


VALID_RESULT = {
    "aday_adi": "Melek Su Kavak",
    "pozisyon": "Satış Danışmanı",
    "uygunluk_skoru": 72,
    "aday_seviyesi": "Strong Match",
    "genel_ozet": "Aday perakende satış deneyimiyle pozisyona güçlü ölçüde uygundur.",
    "olumlu_yonler": ["Satış deneyimi", "Müşteri ilişkileri"],
    "eksik_yonler": ["Belirtilmemiş"],
    "eslesen_yetenekler": ["Satış", "İletişim"],
    "eksik_yetenekler": ["Belirtilmemiş"],
    "deneyim_analizi": {
        "istenen_deneyim": "Satış danışmanlığı",
        "tespit_edilen_deneyim": "Sedef Giyim satış danışmanı",
        "sonuc": "Deneyim uyumludur.",
    },
    "egitim_analizi": {
        "istenen_egitim": "Belirtilmemiş",
        "tespit_edilen_egitim": "Fatih Kalu Lisesi",
        "sonuc": "Eğitim için kesin gereksinim belirtilmemiş.",
    },
    "nihai_karar": "İK görüşmesi için değerlendirilebilir.",
}


class FakeOllamaClient:
    def __init__(self, responses: list[str]) -> None:
        self.responses = responses
        self.prompts: list[str] = []

    def generate(self, prompt: str, model: str | None = None) -> str:
        self.prompts.append(prompt)
        return self.responses.pop(0)


def make_request() -> AnalyzeCandidateRequest:
    return AnalyzeCandidateRequest(
        job_posting={
            "title": "Satış Danışmanı",
            "description": "Müşteri ilişkileri güçlü satış danışmanı aranıyor.",
            "requirements": "Satış deneyimi",
            "responsibilities": "Müşteri bilgilendirme ve satış desteği",
            "seniority_level": "Junior",
        },
        candidate={
            "cleaned_text": "Melek Su Kavak satış danışmanı olarak Sedef Giyim'de çalıştı.",
        },
        language_hint="tr",
    )


class CandidateAnalysisServiceTest(unittest.TestCase):
    def test_valid_json_response_is_validated(self) -> None:
        client = FakeOllamaClient([json.dumps(VALID_RESULT, ensure_ascii=False)])
        response = CandidateAnalysisService(client).analyze(make_request())

        self.assertTrue(response.success)
        self.assertEqual(response.result.uygunluk_skoru, 72)
        self.assertEqual(response.result.aday_seviyesi, "Strong Match")
        self.assertEqual(response.attempt_count, 1)

    def test_invalid_json_is_repaired(self) -> None:
        client = FakeOllamaClient([
            "```json\n{\"aday_adi\": \"Eksik\"",
            json.dumps(VALID_RESULT, ensure_ascii=False),
        ])

        response = CandidateAnalysisService(client).analyze(make_request())

        self.assertTrue(response.success)
        self.assertEqual(response.result.aday_adi, "Melek Su Kavak")
        self.assertEqual(len(client.prompts), 2)


if __name__ == "__main__":
    unittest.main()

