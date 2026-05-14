import json

PROMPT_VERSION = "candidate-analysis-v4"

OUTPUT_SCHEMA = {
    "aday_adi": "",
    "pozisyon": "",
    "uygunluk_skoru": 0,
    "aday_seviyesi": "",
    "genel_ozet": "",
    "puan_kirilimi": {
        "teknik_yetenekler": {"puan": 0, "maksimum": 35, "yorum": ""},
        "backend_deneyimi": {"puan": 0, "maksimum": 20, "yorum": ""},
        "database_bilgisi": {"puan": 0, "maksimum": 15, "yorum": ""},
        "devops_ve_queue": {"puan": 0, "maksimum": 10, "yorum": ""},
        "takim_calismasi": {"puan": 0, "maksimum": 10, "yorum": ""},
        "cloud_deneyimi": {"puan": 0, "maksimum": 10, "yorum": ""},
    },
    "olumlu_yonler": [],
    "eksik_yonler": [],
    "gelisim_onerileri": [],
    "eslesen_yetenekler": [],
    "eksik_yetenekler": [],
    "required_skill_analizi": {
        "karsilananlar": [],
        "eksik_olanlar": [],
    },
    "preferred_skill_analizi": {
        "karsilananlar": [],
        "eksik_olanlar": [],
    },
    "deneyim_analizi": {
        "istenen_deneyim": "",
        "tespit_edilen_deneyim": "",
        "sonuc": "",
    },
    "egitim_analizi": {
        "istenen_egitim": "",
        "tespit_edilen_egitim": "",
        "sonuc": "",
    },
    "nihai_karar": "",
    "mulakat_sorulari": [
        {
            "soru": "",
            "kategori": "",
            "neden": "",
            "oncelik": ""
        }
    ],
}


def build_candidate_analysis_prompt(payload: dict) -> str:
    job_posting = payload.get("job_posting", {})
    candidate = payload.get("candidate", {})

    job_desc = json.dumps(job_posting, ensure_ascii=False, indent=2)
    cv_text = candidate.get("cleaned_text", "")

    return f"""Sen uzman bir İnsan Kaynakları analisti ve teknik işe alım uzmanısın.

Görevin:
İş ilanı ile aday CV'sini bağlamsal olarak karşılaştırmak ve adayın pozisyona uygunluğunu değerlendirmektir.

────────────────────
KRİTİK KURALLAR
────────────────────

- CV'de açıkça bulunmayan hiçbir yeteneği, deneyimi veya teknolojiyi adayda varmış gibi gösterme.
- Varsayım yapma.
- Belirsiz bilgileri "belirtilmemiş" olarak değerlendir.
- Pozitif özellikleri kesinlikle eksik yönlere yazma.
- CV'de bulunan yetenekleri eksik olarak işaretleme.
- Sadece iş ilanındaki gerçek gereksinimlere göre değerlendirme yap.
- İş ilanında bulunmayan gereksinimleri sonradan üretme.
- "Geliştirilebilir alanlar" ile "eksik gereksinimler" kavramlarını karıştırma.
- Eğer aday iş gereksinimini karşılıyorsa bunu eksik yön olarak yazma.
- Çıktı dili Türkçe olmalıdır.
- Sadece geçerli JSON döndür.
- Markdown kullanma.
- JSON dışında hiçbir açıklama yazma.

────────────────────
EKSİK YÖN KURALI
────────────────────

"eksik_yonler" alanına SADECE:

- iş ilanında açıkça istenen
VE
- adayda eksik olan
özellikleri yaz.

Aşağıdakileri eksik yön olarak yazma:
- geliştirme önerileri
- senior-level tavsiyeleri
- gelecekte öğrenilebilecek alanlar
- adayın zaten sahip olduğu teknolojiler

Eğer eksik yön yoksa boş array döndür.

────────────────────
TAKIM ÇALIŞMASI YORUM KURALI
────────────────────

Aşağıdaki deneyimler takım çalışması göstergesi sayılabilir:

- code review süreçleri
- frontend/backend entegrasyonu
- Git branch yönetimi
- pull request süreçleri
- agile/sprint süreçleri
- ekip içi geliştirme süreçleri

Bu göstergeler varsa:
"takım çalışması eksik" yazma.

────────────────────
REQUIRED vs PREFERRED SKILLS
────────────────────

İş ilanındaki gereksinimleri iki kategoriye ayır:

1. required_skills
2. preferred_skills

Kurallar:

- Required skill eksikliği ciddi skor düşürür.
- Preferred skill eksikliği küçük etki oluşturur.
- Preferred skill eksikliği tek başına adayın güçlü eşleşmesini bozmaz.

Örneğin:
- AWS "tercihen" ise eksikliği küçük etki oluşturmalıdır.
- PHP/Laravel eksikliği ise büyük etki oluşturmalıdır.

────────────────────
SCORING RULES
────────────────────

0-39:
Zayıf Eşleşme

40-59:
Kısmi Eşleşme

60-79:
Güçlü Eşleşme

80-100:
Mükemmel Eşleşme

Skor verirken öncelik sırası:

1. Required technical skills
2. Backend development experience
3. REST API experience
4. Database knowledge
5. Queue/Redis/Docker bilgisi
6. Cloud experience
7. Team collaboration signals
8. Education relevance

Skorlar rastgele verilmemelidir.

────────────────────
EĞİTİM KURALI
────────────────────

Eğer iş ilanında eğitim şartı belirtilmemişse:

"İş ilanında spesifik eğitim şartı belirtilmemiştir."

yaz.

CV'deki eğitimi "istenen eğitim" olarak yorumlama.

────────────────────
MÜLAKAT SORULARI
────────────────────

Analiz sonucuna göre 3-7 adet mülakat sorusu üret.

Soru türleri:

1. Belirsizlik: CV'de muallakta kalan, doğrulanması gereken bilgiler.
2. Eksik Yetenek: İş ilanında istenen ama CV'de bulunmayan yetenekler hakkında.
3. Derinlik: CV'de listelenen ama seviyesi ölçülemeyen yetenekler.
4. Kültür Uyumu: Çalışma tarzı, iletişim, takım dinamiği.
5. Motivasyon: Neden bu pozisyon, kariyer hedefleri.

Her soru için:
- soru: Adaya yöneltilecek soru (profesyonel dil)
- kategori: "belirsizlik" | "eksik_yetenek" | "derinlik" | "kultur_uyumu" | "motivasyon"
- neden: Sorunun neden sorulması gerektiği (1-2 cümle)
- oncelik: "yüksek" | "orta" | "düşük"

Öncelik kuralları:
- Required skill eksikliği → yüksek
- Belirsiz deneyim → yüksek
- Preferred skill eksikliği → orta
- Kültür/motivasyon → düşük

────────────────────
JSON KURALLARI
────────────────────

- Sadece geçerli JSON döndür.
- Kod bloğu kullanma.
- Fazladan açıklama yazma.
- Tüm alanlar doldurulmalıdır.
- Virgül ve parantez hatası yapma.
- Geçerli parse edilebilir JSON döndür.

────────────────────
İŞ İLANI
────────────────────

{job_desc}

────────────────────
ADAY CV METNİ
────────────────────

{cv_text}

────────────────────
SADECE AŞAĞIDAKİ JSON FORMATINDA CEVAP VER
────────────────────

{json.dumps(OUTPUT_SCHEMA, ensure_ascii=False, indent=2)}""".strip()


def build_json_repair_prompt(raw_response: str, validation_error: str) -> str:
    return f"""Asagidaki LLM yaniti gecersiz veya semaya uymuyor.
Sadece duzeltilmis valid JSON dondur. Markdown veya aciklama yazma.

Zorunlu JSON semasi:
{json.dumps(OUTPUT_SCHEMA, ensure_ascii=False, indent=2)}

Validation error:
{validation_error}

Bozuk yanit:
{raw_response}""".strip()
