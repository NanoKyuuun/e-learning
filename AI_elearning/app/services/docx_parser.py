import io
from docx import Document
from app.utils.text_cleaner import clean_text


def parse_docx(file_bytes: bytes) -> dict:
    """
    Membaca file DOCX dan mengekstrak teks dari paragraf dan tabel.

    Returns:
        {
            "pages": [{"page_number": 1, "text": "..."}],
            "total_pages": null,
        }
    """
    try:
        doc = Document(io.BytesIO(file_bytes))
        sections: list[str] = []
        current_section: list[str] = []

        for element in doc.element.body:
            tag = element.tag.split("}")[-1] if "}" in element.tag else element.tag

            if tag == "p":
                para_text = "".join(
                    run.text for run in element.findall(
                        ".//{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t"
                    )
                )
                para_text = clean_text(para_text)
                if para_text:
                    current_section.append(para_text)

            elif tag == "tbl":
                # Ekstrak tabel sebagai teks terstruktur
                rows = element.findall(
                    ".//{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tr"
                )
                table_lines = []
                for row in rows:
                    cells = row.findall(
                        ".//{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tc"
                    )
                    cell_texts = []
                    for cell in cells:
                        texts = cell.findall(
                            ".//{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t"
                        )
                        cell_texts.append(" ".join(t.text or "" for t in texts).strip())
                    table_lines.append(" | ".join(cell_texts))

                if table_lines:
                    current_section.append("[TABEL]\n" + "\n".join(table_lines))

        if current_section:
            sections.append("\n".join(current_section))

        combined = clean_text("\n\n".join(sections))

        return {
            "pages": [{"page_number": 1, "text": combined}] if combined else [],
            "total_pages": None,
        }

    except Exception as e:
        raise ValueError(f"Gagal membaca DOCX: {str(e)}")
