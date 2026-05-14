# Sprint 04 - AI Analiz Servisi ve JSON Stabilitesi

## Amaç

İş ilanı ve temizlenmiş CV metnini kullanarak Ollama üzerinden açıklanabilir, doğrulanabilir ve parse edilebilir aday analiz sonucu üretmek.

Bu sprintin temel başarı ölçütü skor üretmek değil, stabil ve valid JSON üretmektir.

## Kapsam

- Prompt builder
- Ollama client
- Pydantic şeması
- JSON validation
- Retry mekanizması
- JSON repair promptu
- Skor ve aday seviyesi kuralları
- AI request/response loglama

## Sorumlu Roller

- ai-engineer
- backend-engineer
- product-manager
- reviewer

## Zorunlu JSON Formatı

Model çıktısı aşağıdaki yapıya uymalıdır:

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

## Prompt İlkeleri

- Model, yalnızca verilen iş ilanı ve CV metnine dayanmalıdır.
- CV'de olmayan bilgi için `Belirtilmemiş` yazmalıdır.
- Markdown üretmemelidir.
- JSON dışında açıklama üretmemelidir.
- Türkçe iş ilanı veya Türkçe CV varsa çıktı Türkçe olmalıdır.
- İngilizce iş ilanı ve İngilizce CV birlikteyse çıktı İngilizce olabilir.
- Son karar insanın vereceği şekilde öneri diliyle yazılmalıdır.

## Skor Kuralları

Skor değerlendirmesi aşağıdaki kriterleri dikkate almalıdır:

- Teknik yetenek örtüşmesi
- Deneyim uygunluğu
- Seniority uyumu
- Sektör uyumu
- Eğitim uygunluğu
- Soft skill sinyalleri
- Proje uygunluğu

Seviye eşikleri:

- `0-39`: Weak Match
- `40-59`: Partial Match
- `60-79`: Strong Match
- `80-100`: Excellent Match

## JSON Stabilitesi Görevleri

- Pydantic modelini oluştur.
- LLM çıktısını önce raw olarak sakla.
- JSON parse başarısızsa repair denemesi yap.
- Repair başarısızsa sınırlı retry çalıştır.
- Retry sayısı env ile yönetilsin.
- Geçersiz sonuçlar veritabanına completed olarak kaydedilmesin.

## Önerilen Endpoint

- `POST /analyze-candidate`

Girdi:

```json
{
  "job_posting": {
    "title": "",
    "description": "",
    "requirements": "",
    "responsibilities": "",
    "seniority_level": ""
  },
  "candidate": {
    "cleaned_text": ""
  },
  "language_hint": "tr"
}
```

Çıktı:

```json
{
  "success": true,
  "result": {},
  "raw_response": "",
  "model": "qwen2.5:7b",
  "attempt_count": 1
}
```

## Kabul Kriterleri

- Ollama üzerinden analiz çalışıyor.
- Her başarılı analiz Pydantic validation'dan geçiyor.
- Geçersiz JSON doğrudan kaydedilmiyor.
- En az bir repair/retry mekanizması var.
- Raw AI response debug amacıyla saklanabiliyor.
- Model CV'de olmayan bilgiyi uydurmadan `Belirtilmemiş` diyebiliyor.

## Dikkat Edilecek Noktalar

- Küçük lokal modeller JSON konusunda kararsızdır; raw response'a güvenilmemelidir.
- Prompt versiyonlanmalıdır.
- Analiz sonucu profesyonel İK diliyle kısa ve açıklanabilir olmalıdır.

## Sprint Sonu Çıktıları

- Çalışan AI analiz endpoint'i
- Pydantic validasyon katmanı
- Retry ve repair mekanizması
- Prompt v1
- Skor ve seviye standardı

