# Sprint 02 - İş İlanı ve CV Veri Modeli

## Amaç

MVP'nin temel iş nesnelerini oluşturmak: iş ilanı, aday/CV, analiz kaydı ve analiz sonucu.

Bu sprint sonunda sistem henüz AI analizi yapmak zorunda değildir; ancak iş ilanı oluşturma, CV yükleme ve analiz kaydı üretme için veritabanı temeli hazır olmalıdır.

## Kapsam

- İş ilanı modeli
- CV/dosya modeli
- Aday modeli veya CV merkezli aday bilgisi
- Analiz modeli
- Analiz durum enum'u
- Filament kaynakları
- Dosya yükleme validasyonu

## Sorumlu Roller

- product-manager
- backend-engineer
- frontend-engineer
- security-auditor
- reviewer

## Veri Modelleri

### JobPosting

Önerilen alanlar:

- `id`
- `title`
- `department`
- `description`
- `requirements`
- `responsibilities`
- `seniority_level`
- `location`
- `employment_type`
- `language`
- `is_active`
- `created_by`
- `created_at`
- `updated_at`

### CandidateCv

Önerilen alanlar:

- `id`
- `job_posting_id`
- `candidate_name`
- `candidate_email`
- `original_file_name`
- `stored_file_path`
- `mime_type`
- `file_size`
- `raw_extracted_text`
- `cleaned_text`
- `parse_status`
- `uploaded_by`
- `created_at`
- `updated_at`

### CandidateAnalysis

Önerilen alanlar:

- `id`
- `job_posting_id`
- `candidate_cv_id`
- `status`
- `score`
- `candidate_level`
- `result_json`
- `raw_ai_response`
- `error_message`
- `started_at`
- `completed_at`
- `created_at`
- `updated_at`

## Durumlar

Analiz status değerleri:

- `pending`
- `processing`
- `completed`
- `failed`

PDF parse status değerleri:

- `pending`
- `completed`
- `failed`

## Filament Görevleri

- İş ilanı listeleme, oluşturma, düzenleme ekranları.
- CV yükleme ekranı.
- İş ilanı detayında ilişkili CV'leri gösterme.
- Analiz kaydı durumunu gösterme.
- İlk aşamada analiz sonucu yoksa kullanıcıya sade durum bilgisi gösterme.

## Güvenlik Görevleri

- Yalnızca PDF kabul et.
- MIME type doğrulaması yap.
- Dosya boyutu limiti belirle.
- Orijinal dosya adını sanitize et.
- Public storage altında hassas CV dosyası yayınlama.

## Kabul Kriterleri

- İK kullanıcısı Filament panelinden iş ilanı oluşturabiliyor.
- İK kullanıcısı iş ilanına bağlı PDF CV yükleyebiliyor.
- PDF dışı dosya reddediliyor.
- Büyük dosya limitleniyor.
- CV kaydı veritabanına kaydediliyor.
- Her CV için başlangıç analiz kaydı oluşturulabiliyor.

## Dikkat Edilecek Noktalar

- CV içeriği kişisel veri içerir; dosya erişimi kontrollü olmalıdır.
- Model isimleri generic chatbot hissi vermemelidir.
- `raw_extracted_text` ve `cleaned_text` ayrı alanlar olarak tasarlanmalıdır.

## Sprint Sonu Çıktıları

- İş ilanı yönetimi
- CV yükleme altyapısı
- Analiz kayıt modeli
- Temel Filament kaynakları

