from dataclasses import dataclass, field

import fitz


class PdfParsingError(Exception):
    pass


@dataclass(frozen=True)
class PdfParseResult:
    raw_text: str
    page_count: int
    warnings: list[str] = field(default_factory=list)


class PdfParser:
    def extract_text(self, pdf_bytes: bytes) -> PdfParseResult:
        if not pdf_bytes.startswith(b"%PDF"):
            raise PdfParsingError("Uploaded file is not a valid PDF document.")

        try:
            document = fitz.open(stream=pdf_bytes, filetype="pdf")
        except Exception as exc:
            raise PdfParsingError("PDF could not be opened or is corrupted.") from exc

        warnings: list[str] = []
        page_texts: list[str] = []

        for page_index, page in enumerate(document, start=1):
            text = page.get_text("text", sort=True).strip()

            if not text:
                warnings.append(f"Page {page_index} has no extractable text.")
                continue

            page_texts.append(text)

        raw_text = "\n\n".join(page_texts).strip()

        if not raw_text:
            raise PdfParsingError("No extractable text found. The PDF may be image-only.")

        return PdfParseResult(
            raw_text=raw_text,
            page_count=document.page_count,
            warnings=warnings,
        )

