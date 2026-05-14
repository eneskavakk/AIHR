from fastapi import APIRouter, File, HTTPException, UploadFile, status

from app.parsing.pdf_parser import PdfParser, PdfParsingError
from app.parsing.text_cleaner import CvTextCleaner
from app.schemas.parse_cv import ParseCvResponse

router = APIRouter(tags=["cv-parsing"])


@router.post("/parse-cv", response_model=ParseCvResponse)
async def parse_cv(file: UploadFile = File(...)) -> ParseCvResponse:
    if file.content_type != "application/pdf":
        raise HTTPException(
            status_code=status.HTTP_415_UNSUPPORTED_MEDIA_TYPE,
            detail="Only PDF files are accepted.",
        )

    pdf_bytes = await file.read()

    try:
        parsed = PdfParser().extract_text(pdf_bytes)
    except PdfParsingError as exc:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=str(exc),
        ) from exc

    cleaned_text = CvTextCleaner().clean(parsed.raw_text)

    if not cleaned_text:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Cleaned text is empty after normalization.",
        )

    return ParseCvResponse(
        raw_text=parsed.raw_text,
        cleaned_text=cleaned_text,
        page_count=parsed.page_count,
        warnings=parsed.warnings,
    )

