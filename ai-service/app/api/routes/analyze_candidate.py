from fastapi import APIRouter, HTTPException, status

from app.schemas.analysis import AnalyzeCandidateRequest, AnalyzeCandidateResponse
from app.services.candidate_analysis import CandidateAnalysisError, CandidateAnalysisService
from app.services.ollama_client import OllamaError

router = APIRouter(tags=["candidate-analysis"])


@router.post("/analyze-candidate", response_model=AnalyzeCandidateResponse)
def analyze_candidate(request: AnalyzeCandidateRequest) -> AnalyzeCandidateResponse:
    try:
        return CandidateAnalysisService().analyze(request)
    except (CandidateAnalysisError, OllamaError) as exc:
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail=str(exc),
        ) from exc

