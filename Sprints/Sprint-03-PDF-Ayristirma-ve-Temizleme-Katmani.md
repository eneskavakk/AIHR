# Sprint 03 - PDF Ayrıştırma ve Temizleme Katmanı

## Amaç

PDF formatındaki CV'lerden mümkün olduğunca güvenilir metin çıkarmak ve AI analizine uygun temiz metin üretmek.

Bu sprint, sistemin doğruluğunu doğrudan etkiler. Kötü ayrıştırılan CV, iyi modelle bile kötü analiz üretir.

## Kapsam

- FastAPI içinde PDF parsing modülü
- PyMuPDF entegrasyonu
- Ham metin çıkarma
- Temizleme ve normalizasyon katmanı
- Parse sonucu Laravel tarafına yazma akışı
- Hata yönetimi

## Sorumlu Roller

- parsing-engineer
- backend-engineer
- ai-engineer
- security-auditor
- reviewer

## Teknik Görevler

### PDF Extraction

- PyMuPDF (`fitz`) ile PDF açma.
- Sayfa bazlı metin çıkarma.
- Boş sayfa ve image-only PDF durumlarını yakalama.
- Metin çıkarılamazsa anlamlı hata döndürme.

### Cleaning Layer

Temizleme katmanı aşağıdakileri yapmalıdır:

- Whitespace normalizasyonu
- Çoklu boş satır azaltma
- Tekrarlayan sembolleri temizleme
- Gereksiz ikon karakterlerini azaltma
- Bölünmüş satırları makul şekilde birleştirme
- Email, telefon, URL gibi bilgileri bozmadan koruma
- Türkçe karakterleri koruma
- Semantic order korunabildiği kadar koruma

### API Tasarımı

Önerilen endpoint:

- `POST /parse-cv`

Girdi:

- PDF dosyası veya Laravel'in dosyaya erişebileceği güvenli path

Çıktı:

```json
{
  "success": true,
  "raw_text": "",
  "cleaned_text": "",
  "page_count": 0,
  "warnings": []
}
```

## Test Senaryoları

- Tek kolon klasik CV
- İki kolon CV
- Tablo içeren CV
- Türkçe karakterli CV
- Çok kısa CV
- Boş veya image-only PDF
- PDF olmayan dosya
- Bozuk PDF

## Kabul Kriterleri

- PDF'den ham metin çıkarılabiliyor.
- Temizlenmiş metin ayrı üretiliyor.
- Laravel tarafında `raw_extracted_text` ve `cleaned_text` ayrı saklanıyor.
- Parse hataları kullanıcıya teknik detay sızdırmadan gösteriliyor.
- Loglarda debug için yeterli bilgi bulunuyor.

## Dikkat Edilecek Noktalar

- PyMuPDF tek başına yeterli kabul edilmemelidir; cleaning layer zorunludur.
- Temizleme katmanı CV'de olmayan bilgiyi üretmemelidir.
- Prompt injection içerebilecek CV metinleri sanitize edilmeli ve analiz promptunda veri olarak sınırlandırılmalıdır.

## Sprint Sonu Çıktıları

- Çalışan PDF parsing endpoint'i
- Temizleme servisi
- Laravel entegrasyon hazırlığı
- Parse test örnekleri ve hata senaryoları

