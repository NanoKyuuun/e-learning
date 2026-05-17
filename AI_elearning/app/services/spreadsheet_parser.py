import io
import pandas as pd
from app.utils.text_cleaner import clean_text


def parse_xlsx(file_bytes: bytes) -> dict:
    """
    Membaca file XLSX dan mengekstrak teks dari tiap sheet.

    Returns:
        {
            "sheets": [{"sheet_name": "Sheet1", "text": "..."}],
            "total_sheets": int,
        }
    """
    try:
        xl = pd.ExcelFile(io.BytesIO(file_bytes), engine="openpyxl")
        sheets = []

        for sheet_name in xl.sheet_names:
            df = xl.parse(sheet_name)
            if df.empty:
                continue

            # Konversi header + baris ke teks
            lines = ["Sheet: " + str(sheet_name)]
            headers = " | ".join(str(h) for h in df.columns.tolist())
            lines.append("Header: " + headers)
            lines.append("---")

            for _, row in df.iterrows():
                row_text = " | ".join(
                    str(v) if pd.notna(v) else "" for v in row.tolist()
                )
                if row_text.strip().replace("|", "").strip():
                    lines.append(row_text)

            text = clean_text("\n".join(lines))
            if text:
                sheets.append({"sheet_name": str(sheet_name), "text": text})

        return {
            "sheets": sheets,
            "total_sheets": len(sheets),
        }

    except Exception as e:
        raise ValueError(f"Gagal membaca XLSX: {str(e)}")


def parse_csv(file_bytes: bytes) -> dict:
    """
    Membaca file CSV dan mengekstrak teks.

    Returns:
        {
            "sheets": [{"sheet_name": "csv", "text": "..."}],
            "total_sheets": 1,
        }
    """
    try:
        # Coba berbagai encoding
        for encoding in ["utf-8", "utf-8-sig", "latin-1", "cp1252"]:
            try:
                df = pd.read_csv(io.BytesIO(file_bytes), encoding=encoding)
                break
            except UnicodeDecodeError:
                continue
        else:
            raise ValueError("Tidak dapat mendeteksi encoding CSV.")

        if df.empty:
            return {"sheets": [], "total_sheets": 0}

        lines = []
        headers = " | ".join(str(h) for h in df.columns.tolist())
        lines.append("Header: " + headers)
        lines.append("---")

        for _, row in df.iterrows():
            row_text = " | ".join(
                str(v) if pd.notna(v) else "" for v in row.tolist()
            )
            if row_text.strip().replace("|", "").strip():
                lines.append(row_text)

        text = clean_text("\n".join(lines))

        return {
            "sheets": [{"sheet_name": "csv", "text": text}] if text else [],
            "total_sheets": 1,
        }

    except Exception as e:
        raise ValueError(f"Gagal membaca CSV: {str(e)}")
