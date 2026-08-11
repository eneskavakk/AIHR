# AIHR

AIHR, insan kaynakları ekipleri için geliştirilen AI destekli CV ve iş ilanı eşleştirme platformudur. Sistem bir chatbot değildir; iş ilanlarını ve PDF formatındaki CV'leri analiz ederek açıklanabilir aday uygunluk skorları, eksik yetenekler, güçlü yönler ve okunabilir İK raporları üretir.

Platformun ana hedefi klasik anahtar kelime filtrelerinin ötesine geçerek adayları bağlamsal olarak değerlendirmek, ancak nihai kararı her zaman insan kullanıcıya bırakmaktır.

## Neler Yapar?

- İş ilanı oluşturma ve yönetme
- PDF CV yükleme
- PDF'ten ham ve temizlenmiş metin çıkarma
- Aday-CV ve iş ilanı eşleşme analizi
- 0-100 arası uygunluk skoru üretme
- Aday seviyesi belirleme: Weak Match, Partial Match, Strong Match, Excellent Match
- Eksik ve eşleşen yetenekleri listeleme
- Deneyim ve eğitim uyumunu açıklama
- Analiz geçmişi saklama
- Filament admin panelinde aday raporu ve sıralama sunma

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

## Teknoloji Yığını

- Laravel 12
- FilamentPHP
- TailwindCSS
- MySQL veya lokal geliştirme için SQLite
- Redis queue; SQLite ile çalışacaksan `QUEUE_CONNECTION=database` kullan
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

Alternatif olarak repo icindeki yardimci Composer komutlarini kullanabilirsin:

```bash
composer setup
composer dev
composer test
```

`composer setup`, bağımlılık kurulumunu, `.env` kopyalamayı, anahtar üretimini, migrasyonları ve frontend build adımını tek seferde çalıştırır.
`composer dev` Laravel sunucusu, `php artisan queue:listen`, Pail log akışı ve Vite'i birlikte açar. Bu sayede geliştirme sırasında arka plan işleri ve log akışı tek terminal grubunda izlenebilir.
`composer test` ise test öncesi config temizliği yapıp PHPUnit çalıştırır.

Varsayılan lokal admin:

```text
Email: admin@example.com
Password: password
```

### Frontend assetleri

`composer setup` frontend bagimliliklarini kurup build aldigi icin, asagidaki adimlar ayri bir frontend kurulumu yaptiginda gereklidir:

```bash
npm install
npm run build
```

Geliştirme sırasında Vite kullanmak için:

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

Servisin ayrıntılı kurulum ve API örnekleri için [ai-service/README.md](ai-service/README.md) dosyasına bak.
`AI_SERVICE_URL` degerini FastAPI servisinin calistigi base URL ile ayni tut; yerel gelistirmede bu genelde `http://127.0.0.1:8001` olur.

### Ollama modeli

```bash
ollama pull qwen2.5:7b
```

Opsiyonel fallback:

```bash
ollama pull llama3.1:8b
```

## Ortam Degiskenleri

Gerçek değerler `.env` ve `ai-service/.env` dosyalarında tutulmalıdır. Bu dosyalar git'e dahil edilmez.

Laravel tarafında önemli ayarlar:

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

FastAPI tarafında önemli ayarlar:

```dotenv
AI_SERVICE_OLLAMA_BASE_URL=http://127.0.0.1:11434
AI_SERVICE_OLLAMA_MODEL=qwen2.5:7b
AI_SERVICE_OLLAMA_FALLBACK_MODEL=llama3.1:8b
AI_SERVICE_OLLAMA_TIMEOUT_SECONDS=120
AI_SERVICE_ANALYSIS_RETRY_COUNT=1
AI_SERVICE_API_TOKEN=
```

Laravel tarafında `AI_SERVICE_TOKEN`, FastAPI tarafında `AI_SERVICE_API_TOKEN` kullanılır; ikisi de aynı gizli değeri taşımalıdır. Boş bırakılırsa lokal geliştirme modunda AI endpoint'leri tokensiz çalışır.

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
- Analiz geçmişi

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
