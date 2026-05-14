from fastapi import Depends, HTTPException, Request, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer

from app.core.settings import settings

security = HTTPBearer(auto_error=False)


def verify_api_token(
    request: Request,
    credentials: HTTPAuthorizationCredentials | None = Depends(security),
) -> None:
    """
    Verify Bearer token on all protected endpoints.
    Skips verification if no token is configured (development mode).
    """
    configured_token = settings.api_token

    # If no token configured, allow all requests (dev mode)
    if not configured_token:
        return

    if credentials is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Missing authentication token.",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if credentials.credentials != configured_token:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Invalid authentication token.",
        )
