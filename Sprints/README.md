# Sprint Planı

Bu klasör, AI Destekli CV & İş İlanı Eşleştirme Platformu için MVP odaklı sprint planını içerir.

Plan, `AGENTS.md` içindeki öncelik sırasına göre hazırlanmıştır:

1. Stable architecture
2. Reliable PDF parsing
3. Stable JSON outputs
4. Queue system
5. Explainable scoring
6. Good UX
7. Fine-tuning

## Sprint Dosyaları

- [Sprint 01 - Temel Mimari ve Proje Kurulumu](./Sprint-01-Temel-Mimari-ve-Proje-Kurulumu.md)
- [Sprint 02 - İş İlanı ve CV Veri Modeli](./Sprint-02-Is-Ilani-ve-CV-Veri-Modeli.md)
- [Sprint 03 - PDF Ayrıştırma ve Temizleme Katmanı](./Sprint-03-PDF-Ayristirma-ve-Temizleme-Katmani.md)
- [Sprint 04 - AI Analiz Servisi ve JSON Stabilitesi](./Sprint-04-AI-Analiz-Servisi-ve-JSON-Stabilitesi.md)
- [Sprint 05 - Queue Tabanlı Analiz Akışı](./Sprint-05-Queue-Tabanli-Analiz-Akisi.md)
- [Sprint 06 - HR Dashboard, Raporlar ve Sıralama](./Sprint-06-HR-Dashboard-Raporlar-ve-Siralama.md)
- [Sprint 07 - Güvenlik, Testler ve MVP Stabilizasyonu](./Sprint-07-Guvenlik-Testler-ve-MVP-Stabilizasyonu.md)

## MVP Tamamlanma Tanımı

MVP, aşağıdaki akış uçtan uca çalıştığında tamamlanmış sayılır:

1. İK kullanıcısı iş ilanı oluşturur.
2. PDF formatında CV yükler.
3. Sistem PDF metnini çıkarır ve temizler.
4. Analiz işi arka plana alınır.
5. FastAPI servisi Ollama üzerinden aday-iş ilanı eşleşmesini analiz eder.
6. LLM çıktısı Pydantic ile doğrulanır.
7. Geçerli analiz sonucu veritabanına kaydedilir.
8. Filament panelinde aday skoru, açıklamalar, eksik/güçlü yönler ve rapor görüntülenir.
9. Adaylar uygunluk skoruna göre sıralanabilir.
10. Analiz geçmişi incelenebilir.

