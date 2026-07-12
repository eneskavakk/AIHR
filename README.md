# AIHR

AIHR, insan kaynaklari ekipleri icin gelistirilen AI destekli CV ve is ilani eslestirme platformudur. Sistem bir chatbot degildir; is ilanlarini ve PDF formatindaki CV'leri analiz ederek aciklanabilir aday uygunluk skorlari, eksik yetenekler, guclu yonler ve okunabilir IK raporlari uretir.

Platformun ana hedefi klasik anahtar kelime filtrelerinin otesine gecerek adaylari baglamsal olarak degerlendirmek, ancak nihai karari her zaman insan kullaniciya birakmaktir.

## Neler Yapar?

- Is ilani olusturma ve yonetme
- PDF CV yukleme
- PDF'ten ham ve temizlenmis metin cikarma
- Aday-CV ve is ilani eslesme analizi
- 0-100 arasi uygunluk skoru uretme
- Aday seviyesi belirleme: Weak, Partial, Strong, Excellent Match
- Eksik ve eslesen yetenekleri listeleme
- Deneyim ve egitim uyumunu aciklama
- Analiz gecmisi saklama
- Filament admin panelinde aday raporu ve siralama sunma

## Mimari

```text
Laravel + Filament Admin Panel
        |
        v
CV Upload
        |
        v
FastAPI AI Service
        |
        v
PDF Parsing + Cleaning Layer
        |
        v
Prompt Builder
        |
        v
Ollama Local LLM
        |
        v
Pydantic JSON Validation
        |
        v
Database Storage + HR Dashboard
```

## Teknoloji Yigini

- Laravel 12
- FilamentPHP
- TailwindCSS
- MySQL veya lokal gelistirme icin SQLite
- Redis veya database queue
- Python + FastAPI
- Pydantic
- PyMuPDF
- Ollama
- Varsayilan model: `qwen2.5:7b`
- Alternatif model: `llama3.1:8b`

## Proje Yapisi

```text
app/
  Data/                  DTO siniflari
  Enums/                 Domain enum'lari
  Filament/              Admin panel kaynaklari, sayfalari ve widget'lari
  Jobs/                  Queue tabanli analiz isleri
  Models/                Laravel modelleri
  Services/              Laravel servis katmani

ai-service/
  app/api/routes/        FastAPI endpoint'leri
  app/core/              Ayarlar ve auth
  app/parsing/           PDF parsing ve text cleaning
  app/prompts/           Versiyonlu promptlar
  app/schemas/           Pydantic semalari
  app/services/          Ollama, JSON parser ve analiz servisleri

database/
  migrations/            Veritabani semasi
  seeders/               Lokal admin kullanicisi
```

## Kurulum

### Gereksinimler

- PHP 8.2+ (`composer.json` ile uyumlu)
- Composer
- Node.js 20+
- Python 3.11+
- Ollama
- MySQL ve Redis, veya hizli lokal gelistirme icin SQLite + database queue

### Laravel

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Varsayilan lokal admin:

```text
Email: admin@example.com
Password: password
```

### Frontend assetleri

```bash
npm install
npm run build
```

Gelistirme sirasinda Vite kullanmak icin:

```bash
npm run dev
```

### FastAPI AI Service

```bash
cd ai-service
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
```

### Ollama modeli

```bash
ollama pull qwen2.5:7b
```

Opsiyonel fallback:

```bash
ollama pull llama3.1:8b
```

## Ortam Degiskenleri

Gercek degerler `.env` ve `ai-service/.env` dosyalarinda tutulmalidir. Bu dosyalar git'e dahil edilmez.

Laravel tarafinda onemli ayarlar:

```dotenv
AI_SERVICE_URL=http://127.0.0.1:8001
AI_SERVICE_TIMEOUT=180
AI_SERVICE_TOKEN=
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:7b
OLLAMA_FALLBACK_MODEL=llama3.1:8b
MAX_CV_UPLOAD_SIZE_KB=5120
AI_ANALYSIS_RETRY_COUNT=1
```

FastAPI tarafinda onemli ayarlar:

```dotenv
AI_SERVICE_OLLAMA_BASE_URL=http://127.0.0.1:11434
AI_SERVICE_OLLAMA_MODEL=qwen2.5:7b
AI_SERVICE_OLLAMA_FALLBACK_MODEL=llama3.1:8b
AI_SERVICE_API_TOKEN=
```

`AI_SERVICE_TOKEN` ve `AI_SERVICE_API_TOKEN` ayni deger olmalidir. Bos birakilirsa lokal gelistirme modunda AI endpoint'leri tokensiz calisir.

## Lokal Calistirma

Laravel:

```bash
php artisan serve
```

Filament panel:

```text
http://127.0.0.1:8000/admin
```

FastAPI:

```bash
cd ai-service
source .venv/bin/activate
uvicorn app.main:app --host 127.0.0.1 --port 8001 --reload
```

Queue worker:

```bash
php artisan queue:work database --timeout=240 --tries=1
```

Redis queue kullaniyorsaniz:

```bash
php artisan queue:work redis --timeout=240 --tries=1
```

MySQL ve Redis'i Docker ile baslatmak icin:

```bash
docker compose up -d mysql redis
```

## API Kontrolleri

Health check:

```bash
curl http://127.0.0.1:8001/health
```

PDF parse:

```bash
curl -X POST http://127.0.0.1:8001/parse-cv \
  -F "file=@/path/to/cv.pdf;type=application/pdf"
```

Aday analizi:

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

## AI Cikti Ilkeleri

Model ciktisi her zaman:

- Gecerli JSON olmalidir
- Markdown icermemelidir
- CV'de olmayan deneyim veya teknoloji uydurmamalidir
- Belirsiz bilgiler icin `Belirtilmemis` kullanmalidir
- Profesyonel IK diliyle, kisa ve acik yazilmalidir
- Pydantic dogrulamasindan gecmeden kaydedilmemelidir

Beklenen analiz formati:

```json
{
  "aday_adi": "",
  "pozisyon": "",
  "uygunluk_skoru": 0,
  "aday_seviyesi": "",
  "genel_ozet": "",
  "olumlu_yonler": [],
  "eksik_yonler": [],
  "eslesen_yetenekler": [],
  "eksik_yetenekler": [],
  "deneyim_analizi": {
    "istenen_deneyim": "",
    "tespit_edilen_deneyim": "",
    "sonuc": ""
  },
  "egitim_analizi": {
    "istenen_egitim": "",
    "tespit_edilen_egitim": "",
    "sonuc": ""
  },
  "nihai_karar": ""
}
```

## Guvenlik Notlari

- `.env`, `ai-service/.env`, API tokenlari, veritabani dosyalari ve yuklenen CV'ler git'e dahil edilmez.
- Sadece PDF yuklemeleri kabul edilmelidir.
- CV dosya boyutu `MAX_CV_UPLOAD_SIZE_KB` ile sinirlanir.
- FastAPI parse ve analiz endpoint'leri production ortaminda Bearer token ile korunmalidir.
- Aday verileri ve CV icerikleri hassas veri olarak ele alinmalidir.
- Raw LLM ciktisi dogrulanmadan kullanilmamalidir.

## Testler

Laravel testleri:

```bash
php artisan test
```

FastAPI testleri:

```bash
cd ai-service
source .venv/bin/activate
pytest
```

## MVP Durumu

Bu MVP asagidaki temel akislar icin hazirlanmistir:

- Is ilani kaydi
- CV yukleme
- PDF metni ayristirma
- AI destekli aday analizi
- Uygunluk skoru
- Aday raporu
- Aday siralama
- Analiz gecmisi

## Yol Haritasi

Phase 2:

- Toplu CV isleme
- AI destekli mulakat sorulari
- Cok dilli analiz destegi
- Vector search / RAG
- Fine-tuned HR modeli

Phase 3:

- ATS entegrasyonlari
- Takim is birligi
- Hiring pipeline otomasyonu
- Recruitment analytics dashboard

## Lisans

Bu proje su an ozel gelistirme asamasindadir.
