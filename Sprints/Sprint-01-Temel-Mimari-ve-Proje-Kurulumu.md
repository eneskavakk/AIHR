# Sprint 01 - Temel Mimari ve Proje Kurulumu

## Amaç

Laravel 12 + Filament paneli ve FastAPI AI servisi için temiz, genişletilebilir ve MVP'ye uygun proje iskeletini kurmak.

Bu sprintin ana hedefi özellik geliştirmekten çok, sonraki sprintlerde güvenle ilerlenebilecek temel mimariyi hazırlamaktır.

## Kapsam

- Laravel uygulama iskeleti
- Filament admin panel kurulumu
- FastAPI servis iskeleti
- Ortam değişkenleri standardı
- MySQL ve Redis bağlantı hazırlığı
- Servis tabanlı klasör yaklaşımı
- Lokal geliştirme akışı dokümantasyonu

## Sorumlu Roller

- product-manager
- backend-engineer
- ai-engineer
- reviewer

## Teknik Görevler

### Laravel Tarafı

- Laravel 12 projesini oluştur.
- FilamentPHP kurulumunu yap.
- Admin kullanıcısı oluşturma akışını hazırla.
- MySQL bağlantısını `.env` üzerinden yapılandır.
- Redis queue ayarlarını `.env` üzerinden yapılandır.
- Temel servis klasörlerini oluştur:
  - `app/Services`
  - `app/Actions`
  - `app/Data`
  - `app/Enums`
  - `app/Jobs`

### FastAPI Tarafı

- `ai-service` veya benzeri ayrı backend klasörü oluştur.
- FastAPI temel uygulamasını kur.
- Health check endpoint'i ekle:
  - `GET /health`
- Temel klasörleri oluştur:
  - `app/api`
  - `app/core`
  - `app/models`
  - `app/services`
  - `app/prompts`
  - `app/parsing`
  - `app/schemas`

### Lokal Çalıştırma

- Laravel, queue worker, Redis, MySQL, FastAPI ve Ollama için geliştirme notları hazırla.
- Gerekirse `docker-compose.yml` için ilk taslak oluştur.
- Ollama model adı için env değişkeni tanımla:
  - `OLLAMA_MODEL=qwen2.5:7b`
  - fallback: `llama3.1:8b`

## Kabul Kriterleri

- Laravel uygulaması lokal olarak açılabiliyor.
- Filament paneline giriş yapılabiliyor.
- FastAPI `GET /health` endpoint'i başarılı yanıt veriyor.
- MySQL bağlantısı çalışıyor.
- Redis queue yapılandırması hazır.
- Kod organizasyonu fat controller üretmeyecek şekilde servis tabanlı başlatılmış.

## Dikkat Edilecek Noktalar

- Bu proje chatbot değildir; panel ve servis isimlendirmeleri HR decision support mantığını yansıtmalıdır.
- AI logic Laravel controller içinde olmamalıdır.
- FastAPI servisi Laravel'den bağımsız test edilebilir kalmalıdır.

## Sprint Sonu Çıktıları

- Çalışır Laravel + Filament iskeleti
- Çalışır FastAPI iskeleti
- Geliştirme ortamı dokümantasyonu
- Temel mimari kararları

