from app.core.settings import settings
from app.prompts.candidate_analysis import (
    PROMPT_VERSION,
    build_candidate_analysis_prompt,
    build_json_repair_prompt,
)
from app.schemas.analysis import AnalyzeCandidateRequest, AnalyzeCandidateResponse
from app.services.json_parser import JsonValidationError, validate_analysis_json
from app.services.ollama_client import OllamaClient


class CandidateAnalysisError(Exception):
    pass


class CandidateAnalysisService:
    def __init__(self, ollama_client: OllamaClient | None = None) -> None:
        self.ollama_client = ollama_client or OllamaClient()

    def analyze(self, request: AnalyzeCandidateRequest) -> AnalyzeCandidateResponse:
        payload = request.model_dump()
        prompt = build_candidate_analysis_prompt(payload)
        raw_response = ""
        validation_error = ""

        for attempt in range(1, settings.analysis_retry_count + 2):
            raw_response = self.ollama_client.generate(prompt)

            try:
                result = validate_analysis_json(raw_response)

                return AnalyzeCandidateResponse(
                    result=result,
                    raw_response=raw_response,
                    model=settings.ollama_model,
                    attempt_count=attempt,
                    prompt_version=PROMPT_VERSION,
                )
            except JsonValidationError as exc:
                validation_error = str(exc)

                repaired_response = self.ollama_client.generate(
                    build_json_repair_prompt(raw_response, validation_error),
                )

                try:
                    result = validate_analysis_json(repaired_response)

                    return AnalyzeCandidateResponse(
                        result=result,
                        raw_response=raw_response,
                        model=settings.ollama_model,
                        attempt_count=attempt,
                        prompt_version=PROMPT_VERSION,
                    )
                except JsonValidationError as repair_exc:
                    validation_error = str(repair_exc)

        raise CandidateAnalysisError(
            f"Model response could not be validated after retries: {validation_error}",
        )

