import logging
from app.config import Config
from app.services.openrouter_client import openrouter_client

logger = logging.getLogger(__name__)


class WebSearchService:
    """
    Service untuk web search pembelajaran.
    Prioritas: OpenRouter Web Search Server Tool → DuckDuckGo fallback.
    """

    def search(self, question: str, model: str | None = None) -> dict:
        """
        Cari informasi menggunakan web search.

        Returns:
            {
                "success": bool,
                "answer": str,
                "sources": list[dict],
                "provider": str,
                "web_search_requests": int,
                ...
            }
        """
        from app.services.prompt_builder import build_web_search_prompt

        messages = build_web_search_prompt(question)

        if Config.AI_WEB_SEARCH_MODE == "openrouter_server_tool":
            result = openrouter_client.chat_with_web_search(
                messages=messages,
                model=model,
            )

            if result.get("success"):
                sources = []
                for s in result.get("web_sources", []):
                    sources.append({
                        "type": "web",
                        "url": s.get("url", ""),
                        "title": s.get("title", ""),
                        "snippet": s.get("snippet", ""),
                    })
                return {
                    **result,
                    "sources": sources,
                    "provider": "openrouter_web_search",
                }

            # OpenRouter gagal → fallback
            logger.warning(
                "[WebSearch] OpenRouter Web Search gagal (%s), mencoba fallback.",
                result.get("error_code"),
            )

        # Fallback: DuckDuckGo
        return self._fallback_duckduckgo(question)

    def _fallback_duckduckgo(self, question: str) -> dict:
        """Fallback web search menggunakan duckduckgo-search library."""
        try:
            from duckduckgo_search import DDGS
            from app.services.prompt_builder import build_web_search_prompt
            from app.services.openrouter_client import openrouter_client

            results = []
            with DDGS() as ddgs:
                for r in ddgs.text(question, max_results=5):
                    results.append({
                        "type": "web",
                        "url": r.get("href", ""),
                        "title": r.get("title", ""),
                        "snippet": r.get("body", "")[:300],
                    })

            # Susun konteks dari hasil DuckDuckGo
            context_parts = [
                f"[{r['title']}]\n{r['snippet']}\nSumber: {r['url']}"
                for r in results
                if r.get("snippet")
            ]
            context = "\n\n---\n\n".join(context_parts)

            messages = build_web_search_prompt(
                f"{question}\n\nHasil pencarian:\n{context}"
            )

            result = openrouter_client.chat_completion(messages=messages)

            return {
                **result,
                "sources": results,
                "provider": "duckduckgo_fallback",
            }

        except ImportError:
            logger.error("[WebSearch] duckduckgo-search tidak terinstall.")
            return {
                "success": False,
                "error_code": "FALLBACK_UNAVAILABLE",
                "message": "Web search tidak tersedia saat ini.",
                "answer": "",
                "sources": [],
                "provider": "none",
            }
        except Exception as e:
            logger.error("[WebSearch] DuckDuckGo fallback error: %s", str(e))
            return {
                "success": False,
                "error_code": "FALLBACK_ERROR",
                "message": f"Web search gagal: {str(e)}",
                "answer": "",
                "sources": [],
                "provider": "none",
            }


web_search_service = WebSearchService()
