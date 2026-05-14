from pydantic import BaseModel, Field


class ParseCvResponse(BaseModel):
    success: bool = True
    raw_text: str
    cleaned_text: str
    page_count: int = Field(ge=1)
    warnings: list[str] = Field(default_factory=list)

