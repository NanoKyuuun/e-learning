import time
import json
import httpx
from app.config import Config
import logging

logger = logging.getLogger(__name__)


class OpenRouterClient:
    """
    HTTP client ke OpenRouter API.
    Menangani chat completion standar dan web search server tool.
    """

    def __init__(self):
        self.base_url = Config.OPENROUTER_BASE_URL.rstrip("/")
        self.api_key = Config.OPENROUTER_API_KEY
        self.default_model = Config.OPENROUTER_MODEL
        self.timeout = Config.OPENROUTER_TIMEOUT
        self.http_referer = Config.OPENROUTER_HTTP_REFERER
        self.app_title = Config.OPENROUTER_APP_TITLE

    def _headers(self) -> dict:
        return {
            "Authorization": f"Bearer {self.api_key}",
            "HTTP-Referer": self.http_referer,
            "X-Title": self.app_title,
            "Content-Type": "application/json",
        }

    def chat_completion(
        self,
        messages: list[dict],
        model: str | None = None,
        temperature: float = 0.3,
        max_tokens: int = 1500,
        tools: list[dict] | None = None,
    ) -> dict:
        """
        Kirim request chat completion ke OpenRouter.

        Returns:
            {
                "success": bool,
                "answer": str,
                "model": str,
                "prompt_tokens": int,
                "completion_tokens": int,
                "web_search_requests": int,
                "latency_ms": int,
                "raw": dict,
            }
        """
        if not self.api_key:
            return {
                "success": False,
                "error_code": "NO_API_KEY",
                "message": "OpenRouter API key belum dikonfigurasi.",
                "answer": "",
            }

        payload: dict = {
            "model": model or self.default_model,
            "messages": messages,
            "temperature": temperature,
            "max_tokens": max_tokens,
        }

        if tools:
            payload["tools"] = tools

        start_time = time.time()

        try:
            with httpx.Client(timeout=self.timeout) as client:
                response = client.post(
                    f"{self.base_url}/chat/completions",
                    headers=self._headers(),
                    json=payload,
                )

            latency_ms = int((time.time() - start_time) * 1000)

            if response.status_code != 200:
                logger.error(
                    "[OpenRouter] Request gagal. Status: %s Body: %s",
                    response.status_code,
                    response.text[:500],
                )
                return {
                    "success": False,
                    "error_code": f"HTTP_{response.status_code}",
                    "message": f"OpenRouter mengembalikan status {response.status_code}.",
                    "answer": "",
                    "latency_ms": latency_ms,
                }

            data = response.json()
            answer = self._extract_answer(data)
            usage = data.get("usage", {})

            # Ekstrak web_search_requests dari usage jika ada
            web_search_requests = (
                usage.get("server_tool_use", {}).get("web_search_requests", 0)
                if isinstance(usage.get("server_tool_use"), dict)
                else 0
            )

            return {
                "success": True,
                "answer": answer,
                "model": data.get("model", model or self.default_model),
                "prompt_tokens": usage.get("prompt_tokens"),
                "completion_tokens": usage.get("completion_tokens"),
                "web_search_requests": web_search_requests,
                "latency_ms": latency_ms,
                "raw": data,
            }

        except httpx.TimeoutException:
            latency_ms = int((time.time() - start_time) * 1000)
            logger.error("[OpenRouter] Request timeout setelah %dms", latency_ms)
            return {
                "success": False,
                "error_code": "TIMEOUT",
                "message": "Request ke OpenRouter timeout. Coba lagi nanti.",
                "answer": "",
                "latency_ms": latency_ms,
            }
        except Exception as e:
            latency_ms = int((time.time() - start_time) * 1000)
            logger.error("[OpenRouter] Error: %s", str(e))
            return {
                "success": False,
                "error_code": "CONNECTION_ERROR",
                "message": f"Tidak dapat terhubung ke OpenRouter: {str(e)}",
                "answer": "",
                "latency_ms": latency_ms,
            }

    def chat_with_web_search(
        self,
        messages: list[dict],
        model: str | None = None,
        temperature: float = 0.3,
        max_tokens: int = 1500,
    ) -> dict:
        """
        Kirim request ke OpenRouter dengan Web Search Server Tool aktif.
        """
        tools = [
            {
                "type": "openrouter:web_search",
                "parameters": {
                    "engine": Config.AI_WEB_SEARCH_ENGINE,
                    "max_results": Config.AI_WEB_SEARCH_MAX_RESULTS,
                    "max_total_results": Config.AI_WEB_SEARCH_MAX_TOTAL_RESULTS,
                    "search_context_size": Config.AI_WEB_SEARCH_CONTEXT_SIZE,
                },
            }
        ]

        result = self.chat_completion(
            messages=messages,
            model=model,
            temperature=temperature,
            max_tokens=max_tokens,
            tools=tools,
        )

        # Ekstrak anotasi sumber dari response jika tersedia
        sources = []
        if result.get("success") and result.get("raw"):
            sources = self._extract_web_sources(result["raw"])

        result["web_sources"] = sources
        return result

    def _extract_answer(self, data: dict) -> str:
        """Ekstrak teks jawaban dari response OpenRouter."""
        try:
            choices = data.get("choices", [])
            if not choices:
                return ""
            message = choices[0].get("message", {})
            return message.get("content", "") or ""
        except (IndexError, KeyError, AttributeError):
            return ""

    def _extract_web_sources(self, data: dict) -> list[dict]:
        """Ekstrak URL sumber dari annotations OpenRouter jika tersedia."""
        sources = []
        try:
            choices = data.get("choices", [])
            if not choices:
                return sources
            message = choices[0].get("message", {})
            annotations = message.get("annotations", [])
            for ann in annotations:
                if ann.get("type") == "url_citation":
                    url_citation = ann.get("url_citation", {})
                    sources.append({
                        "type": "web",
                        "url": url_citation.get("url", ""),
                        "title": url_citation.get("title", ""),
                        "snippet": url_citation.get("content", "")[:200],
                    })
        except Exception:
            pass
        return sources


# Singleton instance
openrouter_client = OpenRouterClient()
