import json

from pydantic import ValidationError

from app.schemas.analysis import CandidateAnalysisResult


class JsonValidationError(Exception):
    pass


def extract_json_object(raw_response: str) -> dict:
    cleaned = raw_response.strip()

    if cleaned.startswith("```"):
        cleaned = cleaned.strip("`")
        cleaned = cleaned.removeprefix("json").strip()

    start = cleaned.find("{")
    end = cleaned.rfind("}")

    if start == -1 or end == -1 or end < start:
        raise JsonValidationError("No JSON object found in model response.")

    candidate = cleaned[start : end + 1]

    try:
        parsed = json.loads(candidate)
    except json.JSONDecodeError as exc:
        raise JsonValidationError(str(exc)) from exc

    if not isinstance(parsed, dict):
        raise JsonValidationError("Parsed JSON is not an object.")

    return parsed


def validate_analysis_json(raw_response: str) -> CandidateAnalysisResult:
    try:
        return CandidateAnalysisResult.model_validate(extract_json_object(raw_response))
    except (ValidationError, JsonValidationError) as exc:
        raise JsonValidationError(str(exc)) from exc

