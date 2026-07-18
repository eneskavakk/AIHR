# AIHR AI Service

FastAPI tabanlı lokal AI servisidir. Health kontrolü, PDF metin çıkarma ve Ollama destekli aday analizi endpoint'lerini sağlar.

## Kurulum

```bash
cd ai-service
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
```

## Ortam Degiskenleri

Servis, `AI_SERVICE_` on ekiyle okunan su ayarlari kullanir:

- `AI_SERVICE_OLLAMA_BASE_URL=http://127.0.0.1:11434`
- `AI_SERVICE_OLLAMA_MODEL=qwen2.5:7b`
- `AI_SERVICE_OLLAMA_FALLBACK_MODEL=llama3.1:8b`
- `AI_SERVICE_OLLAMA_TIMEOUT_SECONDS=120`
- `AI_SERVICE_ANALYSIS_RETRY_COUNT=1`
- `AI_SERVICE_API_TOKEN=`

`health` endpoint'i aciktir; `parse-cv` ve `analyze-candidate` endpoint'leri `AI_SERVICE_API_TOKEN` tanimliyken Bearer token ile korunur.

## Çalıştırma

```bash
uvicorn app.main:app --host 127.0.0.1 --port 8001 --reload
```

## Health Check

```bash
curl http://127.0.0.1:8001/health
```

Beklenen yanıt:

```json
{
  "ok": true,
  "service": "ai-service",
  "version": "0.1.0"
}
```

## PDF Parse

```bash
curl -X POST http://127.0.0.1:8001/parse-cv \
  -F "file=@/path/to/cv.pdf;type=application/pdf"
```

Başarılı yanıt:

```json
{
  "success": true,
  "raw_text": "",
  "cleaned_text": "",
  "page_count": 1,
  "warnings": []
}
```

Notlar:

- Sadece `application/pdf` kabul edilir.
- Image-only veya metin çıkarılamayan PDF'ler `422` döndürür.
- Cleaning layer ham metinden ayrı çalışır; CV'de olmayan bilgi üretmez.

## Candidate Analysis

```bash
curl -X POST http://127.0.0.1:8001/analyze-candidate \
  -H "Content-Type: application/json" \
  -d '{
    "job_posting": {
      "title": "Satış Danışmanı",
      "description": "Müşteri ilişkileri güçlü satış danışmanı aranıyor.",
      "requirements": "Satış deneyimi",
      "responsibilities": "Müşteri bilgilendirme ve satış desteği",
      "seniority_level": "Junior"
    },
    "candidate": {
      "cleaned_text": "Aday satış danışmanı olarak çalışmıştır."
    },
    "language_hint": "tr"
  }'
```

Analiz akışı:

- Prompt version: `candidate-analysis-v1`
- Ollama model: `AI_SERVICE_OLLAMA_MODEL`
- Çıktı önce JSON olarak parse edilir.
- Pydantic şeması valid değilse repair promptu çalışır.
- Repair başarısızsa sınırlı retry yapılır.
- Geçersiz çıktı başarılı analiz olarak dönmez.
