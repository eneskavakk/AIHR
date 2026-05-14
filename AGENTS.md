# AGENTS.md

# PROJECT
AI Destekli CV & İş İlanı Eşleştirme Platformu

---

# PROJECT OVERVIEW

Bu proje, klasik anahtar kelime filtreleme sistemlerinin ötesine geçen, yapay zeka destekli bağlamsal aday değerlendirme platformudur.

Amaç:
- İş ilanlarını analiz etmek
- CV içeriklerini bağlamsal olarak anlamak
- Aday uygunluk skorları üretmek
- İnsan kaynakları süreçlerini hızlandırmak
- Açıklanabilir (Explainable AI) aday değerlendirmesi sağlamak

Sistem:
- İş ilanlarını
- PDF formatındaki CV’leri
- Yerel çalışan LLM modellerini
bir araya getirerek otomatik aday analizi yapar.

Bu proje bir chatbot değildir.

Bu proje:
- HR decision support system
- AI-powered recruitment assistant
- contextual candidate analysis platform
olarak tasarlanmalıdır.

---

# PRIMARY TARGET MARKET

Bu proje öncelikli olarak:
- Türkiye’deki İK ekipleri
- KOBİ’ler
- teknoloji şirketleri
- danışmanlık firmaları
- işe alım ajansları
için geliştirilmektedir.

Default language:
- Turkish

Secondary language:
- English

---

# CORE GOALS

Sistem şunları yapabilmelidir:

- İş ilanı oluşturma
- CV yükleme
- PDF’den metin çıkarma
- Aday–ilan eşleşmesi analizi
- Uygunluk skoru üretme
- Eksik yetenekleri belirleme
- Güçlü yönleri listeleme
- Adayları sıralama
- Analiz geçmişi saklama
- İnsan kaynakları için okunabilir rapor oluşturma

---

# CORE PRINCIPLES

## 1. Explainability First

Model her zaman neden o skoru verdiğini açıklamalıdır.

Sadece skor üretmek yeterli değildir.

---

## 2. No Hallucination

Model:
- CV’de olmayan deneyimleri uydurmamalı
- teknolojileri varsaymamalı
- belirsiz bilgileri kesinmiş gibi yazmamalı

Eğer bilgi yoksa:
- “Belirtilmemiş”
olarak işaretlenmelidir.

---

## 3. Structured Outputs

LLM çıktıları:
- deterministic
- parse edilebilir
- valid JSON
olmalıdır.

---

## 4. Human-in-the-Loop

Sistem son kararı vermez.

Sistem:
- öneri üretir
- analiz yapar
- sıralama sağlar

Nihai işe alım kararı insana aittir.

---

# SYSTEM ARCHITECTURE

Laravel + Filament Admin Panel
        ↓
CV Upload
        ↓
FastAPI Backend
        ↓
PDF Parsing Layer
        ↓
Prompt Builder
        ↓
Local LLM (Ollama)
        ↓
JSON Validation
        ↓
Database Storage
        ↓
HR Dashboard

---

# TECH STACK

## FRONTEND / PANEL
- Laravel 12
- FilamentPHP
- TailwindCSS

## BACKEND AI SERVICE
- Python
- FastAPI

## LOCAL AI RUNTIME
- Ollama

## PREFERRED MODELS

Primary:
- qwen2.5:7b

Alternative:
- llama3.1:8b

---

# DATABASE
- MySQL

---

# QUEUE SYSTEM
- Redis
- Laravel Queues

---

# PDF PARSING
- PyMuPDF (fitz)

---

# AI OUTPUT LANGUAGE RULES

## Turkish Priority

Eğer:
- iş ilanı Türkçeyse
VEYA
- CV Türkçeyse

çıktı dili:
- Türkçe olmalıdır.

---

## English Support

Eğer:
- iş ilanı İngilizceyse
VE
- CV İngilizceyse

çıktı dili:
- İngilizce olabilir.

---

# AI ANALYSIS RULES

Model:

MUST:
- return valid JSON
- use professional HR language
- stay concise
- stay factual
- avoid hallucinations
- evaluate contextually

MUST NOT:
- output markdown
- explain outside JSON
- invent experience
- assume technologies
- generate random scores

---

# ANALYSIS CRITERIA

## Score Calculation Must Consider

- Technical skill overlap
- Experience relevance
- Seniority compatibility
- Industry relevance
- Education relevance
- Soft skills
- Project relevance

---

# CANDIDATE LEVELS

0-39:
- Weak Match

40-59:
- Partial Match

60-79:
- Strong Match

80-100:
- Excellent Match

---

# REQUIRED JSON OUTPUT FORMAT

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

---

# IMPORTANT DEVELOPMENT PITFALLS

# 1. OLLAMA JSON INSTABILITY

Small local models (7B/8B) may occasionally produce invalid JSON.

Common issues:
- missing brackets
- trailing commas
- markdown formatting
- extra explanations
- malformed arrays

Required solution:
- validate all outputs with Pydantic
- build automatic retry mechanism
- use JSON repair prompts
- never trust raw model outputs

All AI responses must pass validation before saving.

---

# 2. LONG INFERENCE TIMES

Local inference may take:
- 10–30 seconds per CV

NEVER process synchronously.

Required architecture:
- upload CV
- create pending analysis
- dispatch queue job
- process in background
- update status asynchronously

Required statuses:
- pending
- processing
- completed
- failed

---

# 3. PDF PARSING CHAOS

CV PDFs may contain:
- two-column layouts
- tables
- hidden text
- icons
- strange fonts
- broken reading order

PyMuPDF extraction alone is NOT enough.

A dedicated cleaning layer is required.

The cleaning system should:
- normalize whitespace
- merge broken lines
- remove repeated symbols
- reduce layout corruption
- preserve semantic order

Store separately:
- raw_extracted_text
- cleaned_text

---

# SECURITY RULES

- Accept PDF only
- Validate MIME types
- Limit upload size
- Sanitize extracted text
- Protect API endpoints
- Log AI requests/responses
- Prevent prompt injection attempts

---

# PERFORMANCE TARGETS

Goal:
- <15s average analysis
- stable JSON outputs
- low memory usage
- responsive UI

---

# DEVELOPMENT PHILOSOPHY

This project must be developed incrementally.

Priority order:

1. Stable architecture
2. Reliable PDF parsing
3. Stable JSON outputs
4. Queue system
5. Explainable scoring
6. Good UX
7. Fine-tuning

DO NOT over-engineer MVP.

---

# MVP DEFINITION

The first MVP must already support:

- Job posting creation
- CV upload
- PDF text extraction
- AI analysis
- Suitability scoring
- Candidate reports
- Candidate ranking
- Analysis history

---

# FUTURE ROADMAP

## Phase 2
- Fine-tuned HR model
- Bulk CV processing
- AI-generated interview questions
- Multi-language support
- Vector search / RAG

## Phase 3
- ATS integrations
- AI analytics dashboard
- Team collaboration
- Hiring pipeline automation
- Recruitment insights

---

# AGENT ROLES

## product-manager

Responsible for:
- sprint planning
- feature prioritization
- business logic validation
- UX direction

---

## backend-engineer

Responsible for:
- Laravel APIs
- FastAPI services
- database logic
- queues
- caching
- async workflows

---

## ai-engineer

Responsible for:
- prompt engineering
- Ollama integration
- model optimization
- JSON stability
- retry systems
- evaluation quality

---

## frontend-engineer

Responsible for:
- Filament panels
- candidate dashboards
- reports
- filters
- responsive UI

---

## parsing-engineer

Responsible for:
- PDF extraction
- text cleaning
- layout normalization
- parsing reliability

---

## security-auditor

Responsible for:
- upload security
- API protection
- validation
- prompt injection mitigation

---

## reviewer

Responsible for:
- code quality
- architecture consistency
- refactoring
- performance review

---

# CODE QUALITY RULES

- Use service-based architecture
- Avoid fat controllers
- Use reusable utilities
- Separate AI logic from business logic
- Version prompts
- Store AI responses for debugging
- Write modular parsing systems

---

# IMPORTANT FINAL NOTE

This system should feel like:
- an intelligent HR assistant
NOT:
- a generic chatbot

Focus on:
- accuracy
- explainability
- structured outputs
- workflow automation
- reliability