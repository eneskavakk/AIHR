from fastapi import APIRouter

from app.core.settings import settings
from app.schemas.health import HealthResponse

router = APIRouter(tags=["health"])


@router.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    return HealthResponse(
        ok=True,
        service="ai-service",
        version=settings.app_version,
    )

