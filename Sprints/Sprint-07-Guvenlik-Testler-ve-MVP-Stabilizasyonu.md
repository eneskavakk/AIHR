# Sprint 07 - Güvenlik, Testler ve MVP Stabilizasyonu

## Amaç

MVP'yi gerçek kullanım öncesinde güvenlik, doğruluk, hata dayanıklılığı ve temel performans açısından stabilize etmek.

Bu sprint sonunda sistem demo yapılabilir ve kontrollü pilot kullanıma hazır hale gelmelidir.

## Kapsam

- Upload güvenliği
- API koruması
- Prompt injection azaltma
- Test kapsamı
- Loglama
- Performans ölçümü
- Hata senaryoları
- MVP kabul testi

## Sorumlu Roller

- security-auditor
- reviewer
- backend-engineer
- ai-engineer
- parsing-engineer
- frontend-engineer

## Güvenlik Görevleri

- PDF MIME doğrulamasını tekrar kontrol et.
- Dosya boyutu limitini test et.
- Dosya path traversal risklerini kontrol et.
- FastAPI endpoint'lerini korumak için API token veya internal network kuralı uygula.
- CV metni içindeki prompt injection denemelerini prompt içinde veri olarak izole et.
- Raw AI response ve CV metni erişimini yetkilendir.
- Loglarda hassas kişisel veri sızıntısını azalt.

## Test Görevleri

### Laravel Testleri

- İş ilanı oluşturma testi
- PDF CV yükleme testi
- PDF olmayan dosya reddi testi
- Analiz kaydı oluşturma testi
- Queue job status transition testi
- Failed analiz retry testi

### FastAPI Testleri

- Health check testi
- PDF parse başarı testi
- Bozuk PDF hata testi
- Cleaning layer testi
- Pydantic schema validation testi
- Invalid JSON repair testi
- Ollama timeout handling testi

### Uçtan Uca Test

Senaryo:

1. İş ilanı oluştur.
2. PDF CV yükle.
3. Queue job çalıştır.
4. Parse sonucunu doğrula.
5. AI analiz sonucunu doğrula.
6. Filament panelinde raporu görüntüle.
7. Adayı skor sıralamasında gör.

## Performans Hedefleri

- CV upload isteği hızlı dönmeli.
- Ortalama analiz süresi hedefi: `<15s`.
- Uzun analizler UI'ı bloklamamalı.
- Queue worker memory kullanımı izlenmeli.
- FastAPI timeout değerleri kontrollü olmalı.

## MVP Kabul Kontrol Listesi

- İş ilanı oluşturulabiliyor.
- PDF CV yüklenebiliyor.
- PDF metni çıkarılıyor.
- Temizlenmiş metin saklanıyor.
- Analiz arka planda çalışıyor.
- AI valid JSON üretiyor veya failed durumuna düşüyor.
- Skor ve açıklamalar kaydediliyor.
- Adaylar sıralanabiliyor.
- Aday raporu okunabiliyor.
- Analiz geçmişi görülebiliyor.
- Güvenlik kontrolleri temel seviyede uygulanmış.

## Kabul Kriterleri

- Kritik güvenlik açıkları kapatılmış.
- Ana mutlu yol uçtan uca çalışıyor.
- Bilinen hata senaryoları kontrollü şekilde failed durumuna düşüyor.
- Testler temel MVP risklerini kapsıyor.
- Demo için stabil veri seti hazırlanmış.

## Dikkat Edilecek Noktalar

- MVP'de fine-tuning yapılması zorunlu değildir.
- Vector search/RAG bu sprint kapsamına alınmamalıdır.
- Gereksiz büyük refactor yapılmamalıdır.
- Öncelik güvenilirlik ve açıklanabilirliktir.

## Sprint Sonu Çıktıları

- Stabil MVP
- Güvenlik kontrol listesi
- Test paketi
- Demo akışı
- Pilot kullanıma hazır sürüm

