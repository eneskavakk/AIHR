from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    app_name: str = "AIHR AI Service"
    app_version: str = "0.1.0"
    debug: bool = True
    ollama_base_url: str = "http://127.0.0.1:11434"
    ollama_model: str = "qwen2.5:7b"
    ollama_fallback_model: str = "llama3.1:8b"
    ollama_timeout_seconds: int = 120
    analysis_retry_count: int = 1
    api_token: str = ""  # Set via AI_SERVICE_API_TOKEN env var

    model_config = SettingsConfigDict(
        env_file=".env",
        env_prefix="AI_SERVICE_",
        extra="ignore",
    )


@lru_cache
def get_settings() -> Settings:
    return Settings()


settings = get_settings()
