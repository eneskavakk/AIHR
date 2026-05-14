from fastapi import Depends, FastAPI

from app.api.routes.analyze_candidate import router as analyze_candidate_router
from app.api.routes.health import router as health_router
from app.api.routes.parse_cv import router as parse_cv_router
from app.core.auth import verify_api_token
from app.core.settings import settings


def create_app() -> FastAPI:
    app = FastAPI(
        title=settings.app_name,
        version=settings.app_version,
        docs_url="/docs" if settings.debug else None,
        redoc_url="/redoc" if settings.debug else None,
    )

    # Health endpoint is public (no auth required)
    app.include_router(health_router)

    # Protected endpoints require Bearer token (when configured)
    app.include_router(
        analyze_candidate_router,
        dependencies=[Depends(verify_api_token)],
    )
    app.include_router(
        parse_cv_router,
        dependencies=[Depends(verify_api_token)],
    )

    return app


app = create_app()
