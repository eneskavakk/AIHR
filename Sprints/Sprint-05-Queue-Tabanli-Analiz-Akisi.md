# Sprint 05 - Queue Tabanlı Analiz Akışı

## Amaç

CV yükleme, PDF parse ve AI analiz sürecini kullanıcı isteğinden ayırarak arka planda çalışan güvenilir bir workflow haline getirmek.

Yerel LLM analizleri 10-30 saniye sürebileceği için hiçbir analiz senkron çalıştırılmamalıdır.

## Kapsam

- Laravel queue job'ları
- Redis queue kullanımı
- Analiz durum geçişleri
- FastAPI parse ve analiz entegrasyonu
- Hata yönetimi
- Yeniden deneme stratejisi
- Kullanıcıya durum gösterimi

## Sorumlu Roller

- backend-engineer
- ai-engineer
- parsing-engineer
- frontend-engineer
- reviewer

## Workflow

1. Kullanıcı iş ilanına PDF CV yükler.
2. Laravel `CandidateCv` kaydı oluşturur.
3. Laravel `CandidateAnalysis` kaydını `pending` olarak oluşturur.
4. Queue job dispatch edilir.
5. Job analizi `processing` durumuna çeker.
6. FastAPI PDF parse endpoint'i çağrılır.
7. Ham ve temiz metin kaydedilir.
8. FastAPI AI analiz endpoint'i çağrılır.
9. Valid JSON sonucu kaydedilir.
10. Analiz `completed` olur.
11. Hata varsa analiz `failed` olur ve hata mesajı saklanır.

## Job Tasarımı

Önerilen job:

- `ProcessCandidateAnalysisJob`

Sorumlulukları:

- Analiz durumunu yönetmek
- FastAPI çağrılarını yapmak
- Parse sonucunu kaydetmek
- AI sonucunu kaydetmek
- Hataları yakalamak
- Gerekiyorsa retry politikası uygulamak

## Durum Geçişleri

Geçerli geçişler:

- `pending -> processing`
- `processing -> completed`
- `processing -> failed`
- `failed -> pending` manuel yeniden deneme için

Geçersiz geçişler engellenmelidir:

- `completed -> processing`
- `completed -> failed`

## Kullanıcı Deneyimi

Filament ekranlarında:

- Analiz bekliyor
- Analiz yapılıyor
- Analiz tamamlandı
- Analiz başarısız

durumları net görünmelidir.

Başarısız analiz için:

- Teknik olmayan hata özeti
- Manuel yeniden deneme aksiyonu

olmalıdır.

## Kabul Kriterleri

- CV yükleme isteği uzun süre bekletilmeden tamamlanıyor.
- Analiz queue üzerinden arka planda çalışıyor.
- Status alanları doğru güncelleniyor.
- FastAPI hatası analizi failed durumuna alıyor.
- Başarısız analiz yeniden denenebiliyor.
- Completed analiz sonucu tekrar yanlışlıkla işlenmiyor.

## Dikkat Edilecek Noktalar

- LLM çağrısı controller içinde yapılmamalıdır.
- Queue job idempotent tasarlanmalıdır.
- Hata mesajları kullanıcıya sade, loglara detaylı yazılmalıdır.
- API timeout değerleri kontrollü olmalıdır.

## Sprint Sonu Çıktıları

- Uçtan uca async analiz workflow'u
- Redis queue entegrasyonu
- Durum yönetimi
- Yeniden deneme akışı

