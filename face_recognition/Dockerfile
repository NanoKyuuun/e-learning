FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    cmake \
    gfortran \
    libgl1 \
    libglib2.0-0 \
    libgomp1 \
    libjpeg62-turbo-dev \
    liblapack-dev \
    libopenblas-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt ./
RUN pip install --upgrade pip setuptools wheel && pip install -r requirements.txt

COPY . .

EXPOSE 5000

CMD ["python", "app.py"]
