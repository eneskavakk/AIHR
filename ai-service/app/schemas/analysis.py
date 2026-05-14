from typing import Any, Literal

from pydantic import BaseModel, Field, field_validator, model_validator


CandidateLevel = Literal[
    "Weak Match",
    "Partial Match",
    "Strong Match",
    "Excellent Match",
]


class JobPostingInput(BaseModel):
    title: str
    description: str
    requirements: str | None = None
    responsibilities: str | None = None
    seniority_level: str | None = None


class CandidateInput(BaseModel):
    cleaned_text: str = Field(min_length=1)


class AnalyzeCandidateRequest(BaseModel):
    job_posting: JobPostingInput
    candidate: CandidateInput
    language_hint: str = "tr"


# --- Score breakdown models ---

class ScoreCategory(BaseModel):
    puan: int = Field(ge=0)
    maksimum: int = Field(ge=1)
    yorum: str = ""

    @field_validator("yorum", mode="before")
    @classmethod
    def default_empty_yorum(cls, value: Any) -> str:
        if value is None:
            return ""
        return str(value).strip()


class ScoreBreakdown(BaseModel):
    teknik_yetenekler: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=35))
    backend_deneyimi: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=20))
    database_bilgisi: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=15))
    devops_ve_queue: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=10))
    takim_calismasi: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=10))
    cloud_deneyimi: ScoreCategory = Field(default_factory=lambda: ScoreCategory(puan=0, maksimum=10))


# --- Skill analysis models ---

class SkillAnalysis(BaseModel):
    karsilananlar: list[str] = Field(default_factory=list)
    eksik_olanlar: list[str] = Field(default_factory=list)


# --- Experience & Education ---

class ExperienceAnalysis(BaseModel):
    istenen_deneyim: str
    tespit_edilen_deneyim: str
    sonuc: str


class EducationAnalysis(BaseModel):
    istenen_egitim: str
    tespit_edilen_egitim: str
    sonuc: str


InterviewCategory = Literal[
    "belirsizlik",
    "eksik_yetenek",
    "derinlik",
    "kultur_uyumu",
    "motivasyon",
]

InterviewPriority = Literal[
    "yüksek",
    "orta",
    "düşük",
]


class InterviewQuestion(BaseModel):
    soru: str
    kategori: InterviewCategory = "belirsizlik"
    neden: str = ""
    oncelik: InterviewPriority = "orta"

    @field_validator("soru", "neden", mode="before")
    @classmethod
    def default_empty(cls, value: Any) -> str:
        if value is None:
            return ""
        return str(value).strip()

    @field_validator("kategori", mode="before")
    @classmethod
    def normalize_kategori(cls, value: Any) -> str:
        valid = {"belirsizlik", "eksik_yetenek", "derinlik", "kultur_uyumu", "motivasyon"}
        val = str(value).strip().lower() if value else "belirsizlik"
        return val if val in valid else "belirsizlik"

    @field_validator("oncelik", mode="before")
    @classmethod
    def normalize_oncelik(cls, value: Any) -> str:
        valid = {"yüksek", "orta", "düşük"}
        val = str(value).strip().lower() if value else "orta"
        return val if val in valid else "orta"


# --- Main result ---

class CandidateAnalysisResult(BaseModel):
    aday_adi: str
    pozisyon: str
    uygunluk_skoru: int = Field(ge=0, le=100)
    aday_seviyesi: CandidateLevel
    genel_ozet: str

    puan_kirilimi: ScoreBreakdown = Field(default_factory=ScoreBreakdown)

    olumlu_yonler: list[str]
    eksik_yonler: list[str]
    gelisim_onerileri: list[str] = Field(default_factory=list)
    eslesen_yetenekler: list[str]
    eksik_yetenekler: list[str]

    required_skill_analizi: SkillAnalysis = Field(default_factory=SkillAnalysis)
    preferred_skill_analizi: SkillAnalysis = Field(default_factory=SkillAnalysis)

    deneyim_analizi: ExperienceAnalysis
    egitim_analizi: EducationAnalysis
    nihai_karar: str

    # Legacy: kept for backward compatibility with existing analyses
    mulakat_sorulari: list[InterviewQuestion] = Field(default_factory=list)

    @field_validator(
        "aday_adi",
        "pozisyon",
        "genel_ozet",
        "nihai_karar",
        mode="before",
    )
    @classmethod
    def empty_string_as_unspecified(cls, value: Any) -> str:
        if value is None or str(value).strip() == "":
            return "Belirtilmemiş"

        return str(value).strip()

    @model_validator(mode="after")
    def normalize_candidate_level(self) -> "CandidateAnalysisResult":
        self.aday_seviyesi = level_for_score(self.uygunluk_skoru)

        return self


class AnalyzeCandidateResponse(BaseModel):
    success: bool = True
    result: CandidateAnalysisResult
    raw_response: str
    model: str
    attempt_count: int = Field(ge=1)
    prompt_version: str


def level_for_score(score: int) -> CandidateLevel:
    if score <= 39:
        return "Weak Match"

    if score <= 59:
        return "Partial Match"

    if score <= 79:
        return "Strong Match"

    return "Excellent Match"
