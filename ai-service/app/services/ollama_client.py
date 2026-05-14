import httpx

from app.core.settings import settings


class OllamaError(Exception):
    pass


class OllamaClient:
    def generate(self, prompt: str, model: str | None = None) -> str:
        model_name = model or settings.ollama_model

        try:
            response = httpx.post(
                f"{settings.ollama_base_url.rstrip('/')}/api/generate",
                json={
                    "model": model_name,
                    "prompt": prompt,
                    "stream": False,
                    "format": "json",
                    "options": {
                        "temperature": 0.1,
                    },
                },
                timeout=settings.ollama_timeout_seconds,
            )
            response.raise_for_status()
        except httpx.HTTPError as exc:
            raise OllamaError(str(exc)) from exc

        payload = response.json()
        generated = payload.get("response")

        if not isinstance(generated, str) or generated.strip() == "":
            raise OllamaError("Ollama returned an empty response.")

        return generated

