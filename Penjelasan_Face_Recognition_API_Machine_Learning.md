# Penjelasan Project Face Recognition API dan Metode Machine Learning

## 1. Gambaran Umum Project

Project ini adalah **Face Recognition API berbasis Python Flask**. API ini berfungsi sebagai layanan tambahan yang dapat dihubungkan dengan sistem utama, misalnya aplikasi Laravel, untuk mendukung fitur **absensi berbasis pengenalan wajah**.

Secara umum, project ini memiliki tiga fungsi utama:

1. **Enrollment wajah siswa**, yaitu proses mendaftarkan wajah siswa ke sistem.
2. **Verification wajah siswa**, yaitu proses memeriksa apakah wajah pada foto absensi cocok dengan wajah siswa yang sudah terdaftar.
3. **Delete data wajah**, yaitu proses menghapus data embedding wajah siswa dari penyimpanan.

Hal penting yang perlu dipahami adalah bahwa project ini **bukan hanya sekadar upload foto**, tetapi menggunakan proses **face recognition berbasis machine learning** melalui library `face_recognition`.

---

## 2. Apakah Project Ini Menggunakan Machine Learning?

Jawabannya: **ya, project ini menggunakan machine learning**.

Namun, project ini **tidak melatih model machine learning sendiri dari awal**. Project ini menggunakan **pre-trained model**, yaitu model machine learning yang sudah dilatih sebelumnya dan disediakan melalui library `face_recognition`.

Dengan kata lain, project ini tidak memiliki proses seperti:

```python
model.fit()
model.train()
epoch
loss
accuracy training
training dataset
```

Yang dilakukan oleh project ini adalah:

```python
face_recognition.face_locations(image)
face_recognition.face_encodings(image, face_locations)
face_recognition.face_distance([known_encoding], face_encoding)
```

Artinya, project ini memakai model yang sudah ada untuk:

1. mendeteksi wajah;
2. mengekstraksi fitur wajah;
3. mengubah wajah menjadi embedding numerik;
4. membandingkan embedding wajah menggunakan jarak tertentu.

Jadi, istilah yang paling tepat untuk project ini adalah:

> **Face recognition berbasis pre-trained machine learning model dengan pendekatan face embedding dan Euclidean distance.**

---

## 3. Posisi Project Ini dalam Konsep Artificial Intelligence

Secara konsep, project ini dapat diposisikan sebagai berikut:

```text
Artificial Intelligence
└── Machine Learning
    └── Pre-trained Face Recognition Model
        └── Face Embedding
            └── Euclidean Distance Matching
```

Project ini termasuk ke dalam bidang **Artificial Intelligence** karena sistem mampu mengenali pola wajah manusia. Project ini juga termasuk **Machine Learning** karena proses pengenalan wajah dilakukan oleh model yang telah dilatih sebelumnya untuk membaca karakteristik wajah.

Namun, perlu ditekankan bahwa project ini **tidak membangun model baru**, melainkan **menggunakan model machine learning yang sudah tersedia**.

---

## 4. Struktur Folder Project

Struktur utama project adalah sebagai berikut:

```text
app.py
config.py
requirements.txt
.env.example
run.bat
setup.bat

routes/
├── enroll.py
├── verify.py
├── delete.py
└── health.py

services/
├── face_service.py
└── storage_service.py

storage/
├── embeddings/
└── reference_images/

templates/
└── index.html

reference_images/
└── 1.jpg
```

Penjelasan setiap bagian:

| File/Folder | Fungsi |
|---|---|
| `app.py` | File utama untuk menjalankan aplikasi Flask API |
| `config.py` | Berisi konfigurasi API, API key, threshold wajah, port, CORS, dan storage |
| `routes/` | Berisi endpoint API seperti enroll, verify, delete, dan health check |
| `services/` | Berisi logika utama face recognition dan penyimpanan data |
| `storage/embeddings/` | Folder penyimpanan embedding wajah siswa dalam format JSON |
| `storage/reference_images/` | Folder yang disiapkan untuk menyimpan gambar referensi, tetapi belum aktif digunakan dalam coding utama |
| `templates/index.html` | Halaman sederhana untuk akses kamera, tetapi belum sepenuhnya sesuai dengan endpoint API yang tersedia |
| `requirements.txt` | Daftar library Python yang dibutuhkan project |
| `.env.example` | Contoh file konfigurasi environment |
| `setup.bat` | Script instalasi otomatis untuk Windows |
| `run.bat` | Script untuk menjalankan server Flask |

---

## 5. Library yang Digunakan

Isi file `requirements.txt` menunjukkan bahwa project menggunakan library berikut:

| Library | Fungsi |
|---|---|
| `flask` | Framework backend untuk membuat API |
| `flask-cors` | Mengatur akses CORS agar API bisa dipanggil dari aplikasi lain seperti Laravel |
| `face-recognition` | Library utama untuk deteksi wajah, encoding wajah, dan perbandingan wajah |
| `opencv-python` | Library computer vision, meskipun pada coding utama belum banyak digunakan langsung |
| `numpy` | Mengolah data numerik embedding wajah |
| `python-dotenv` | Membaca konfigurasi dari file `.env` |
| `requests` | Library HTTP request, tetapi belum digunakan secara langsung pada kode utama |
| `Pillow` | Pengolahan gambar |
| `filelock` | Library pengunci file, tetapi pada kode saat ini penguncian file dilakukan dengan `threading.Lock` |
| `setuptools` | Mendukung instalasi dependency tertentu |

Library paling penting dalam konteks machine learning adalah:

```python
face-recognition
numpy
```

---

## 6. Penjelasan File `app.py`

File `app.py` adalah file utama untuk menjalankan server Flask.

Bagian penting:

```python
app = Flask(__name__)
```

Kode ini membuat instance aplikasi Flask.

```python
app.config["MAX_CONTENT_LENGTH"] = Config.MAX_CONTENT_LENGTH
```

Kode ini membatasi ukuran maksimal file upload. Berdasarkan `.env.example`, batas default-nya adalah 10 MB.

```python
CORS(app, origins=Config.ALLOWED_ORIGINS)
```

Kode ini mengatur agar API hanya dapat diakses dari origin tertentu, misalnya:

```text
http://localhost:8000
http://localhost:8085
http://127.0.0.1:8000
```

Kemudian Flask mendaftarkan beberapa blueprint:

```python
app.register_blueprint(health_bp)
app.register_blueprint(enroll_bp)
app.register_blueprint(verify_bp)
app.register_blueprint(delete_bp)
```

Artinya, endpoint dari file `health.py`, `enroll.py`, `verify.py`, dan `delete.py` dimasukkan ke aplikasi utama.

File ini juga memiliki global error handler untuk menangani error:

| Error | Keterangan |
|---|---|
| `404` | Endpoint tidak ditemukan |
| `405` | Method HTTP tidak sesuai |
| `413` | File terlalu besar |
| `500` | Kesalahan internal server |

---

## 7. Penjelasan File `config.py`

File `config.py` berisi konfigurasi utama aplikasi.

### 7.1 API Key Internal

```python
INTERNAL_API_KEY = os.getenv("FACE_API_KEY", "secret-internal-key")
```

Bagian ini digunakan untuk mengamankan komunikasi antara aplikasi utama, misalnya Laravel, dengan Flask API.

Request harus membawa header:

```text
X-Internal-Api-Key: secret-internal-key
```

Jika API key salah, request ditolak dengan status `401 Unauthorized`.

### 7.2 CORS

```python
ALLOWED_ORIGINS = [...]
```

CORS digunakan untuk membatasi origin yang boleh mengakses API.

### 7.3 Folder Penyimpanan

```python
STORAGE_DIR = os.path.join(BASE_DIR, "storage")
EMBEDDINGS_DIR = os.path.join(STORAGE_DIR, "embeddings")
REFERENCE_DIR = os.path.join(STORAGE_DIR, "reference_images")
```

Folder paling penting adalah `storage/embeddings`, karena di sinilah data embedding wajah siswa disimpan.

### 7.4 Threshold Face Recognition

```python
FACE_DISTANCE_THRESHOLD = float(os.getenv("FACE_THRESHOLD", 0.45))
```

Threshold adalah batas maksimal jarak wajah agar dua wajah dianggap cocok.

Default threshold project ini adalah:

```text
0.45
```

Aturannya:

```text
Jika distance <= 0.45, wajah dianggap cocok.
Jika distance > 0.45, wajah dianggap tidak cocok.
```

Semakin kecil threshold, sistem semakin ketat. Semakin besar threshold, sistem semakin longgar.

---

## 8. Endpoint API dalam Project

Project ini memiliki empat endpoint utama:

| Endpoint | Method | Fungsi |
|---|---|---|
| `/health` | `GET` | Mengecek apakah API aktif |
| `/faces/enroll` | `POST` | Mendaftarkan atau memperbarui data wajah siswa |
| `/faces/verify` | `POST` | Memverifikasi wajah siswa saat absensi |
| `/faces/delete` | `POST` | Menghapus data wajah siswa |

Endpoint ini didesain untuk dipanggil oleh sistem lain, bukan langsung oleh pengguna akhir.

---

## 9. Penjelasan File `routes/enroll.py`

File `routes/enroll.py` digunakan untuk proses **pendaftaran wajah siswa**.

Endpoint:

```python
@enroll_bp.route("/faces/enroll", methods=["POST"])
def enroll_face():
```

Data yang dikirim melalui `multipart/form-data`:

| Field | Status | Fungsi |
|---|---|---|
| `student_id` | Wajib | ID siswa dari sistem utama |
| `user_id` | Opsional | ID user dari sistem utama |
| `name` | Opsional | Nama siswa |
| `image` | Wajib | Foto wajah siswa |

Alur prosesnya:

```text
Request dari Laravel
↓
Validasi API key
↓
Validasi student_id
↓
Validasi file image
↓
Deteksi wajah
↓
Pastikan hanya ada satu wajah
↓
Ekstraksi embedding wajah
↓
Simpan embedding ke storage/embeddings
↓
Return response sukses
```

Bagian penting:

```python
encodings, face_count = FaceService.get_encodings_from_image(image_file)
```

Kode ini memanggil `FaceService` untuk memproses gambar dan mengambil encoding wajah.

Jika tidak ada wajah, response-nya:

```json
{
  "success": false,
  "error_code": "NO_FACE_DETECTED",
  "message": "Tidak ada wajah terdeteksi dalam gambar."
}
```

Jika wajah lebih dari satu, response-nya:

```json
{
  "success": false,
  "error_code": "MULTIPLE_FACES_DETECTED",
  "message": "Terdeteksi lebih dari satu wajah. Pastikan hanya ada satu wajah dalam gambar."
}
```

Jika valid, embedding disimpan menggunakan:

```python
StorageService.save_embedding(
    student_id=student_id,
    embedding=encodings[0],
    metadata={
        "name": request.form.get("name", ""),
        "user_id": request.form.get("user_id", ""),
    }
)
```

Perlu dipahami bahwa proses ini **bukan training model**, melainkan **enrollment wajah**.

---

## 10. Penjelasan File `routes/verify.py`

File `routes/verify.py` digunakan untuk proses **verifikasi wajah saat absensi**.

Endpoint:

```python
@verify_bp.route("/faces/verify", methods=["POST"])
def verify_face():
```

Data yang dikirim melalui `multipart/form-data`:

| Field | Status | Fungsi |
|---|---|---|
| `expected_student_id` | Wajib | ID siswa yang seharusnya melakukan absensi |
| `image` | Wajib | Foto wajah dari kamera absensi |

Alur prosesnya:

```text
Request dari Laravel
↓
Validasi API key
↓
Ambil expected_student_id
↓
Cari embedding siswa yang sudah tersimpan
↓
Validasi file foto absensi
↓
Deteksi wajah dari foto absensi
↓
Pastikan hanya ada satu wajah
↓
Ekstraksi embedding wajah baru
↓
Bandingkan embedding lama dan baru
↓
Hitung distance
↓
Tentukan verified true atau false
↓
Return hasil ke Laravel
```

Bagian penting:

```python
known_encoding = StorageService.get_embedding(expected_student_id)
```

Kode ini mengambil embedding wajah siswa yang sudah tersimpan.

Jika belum ada data wajah, maka response-nya:

```json
{
  "success": false,
  "error_code": "NO_PROFILE_FOUND",
  "verified": false,
  "message": "Data wajah siswa belum terdaftar di sistem pengenalan wajah."
}
```

Setelah itu gambar absensi diproses:

```python
encodings, face_count = FaceService.get_encodings_from_image(request.files["image"])
```

Kemudian wajah dibandingkan:

```python
is_match, distance = FaceService.compare_faces(
    known_encoding,
    encodings[0],
    Config.FACE_DISTANCE_THRESHOLD
)
```

Hasil akhirnya:

```json
{
  "success": true,
  "verified": true,
  "distance": 0.38,
  "threshold": 0.45,
  "message": "Wajah berhasil diverifikasi."
}
```

Atau jika tidak cocok:

```json
{
  "success": true,
  "verified": false,
  "distance": 0.62,
  "threshold": 0.45,
  "message": "Wajah tidak cocok dengan data yang terdaftar."
}
```

Catatan penting: HTTP status `200` bisa muncul untuk `verified=true` maupun `verified=false`. Artinya, aplikasi utama seperti Laravel harus membaca field `verified`, bukan hanya membaca HTTP status.

---

## 11. Penjelasan File `routes/delete.py`

File `routes/delete.py` digunakan untuk menghapus data embedding wajah siswa.

Endpoint:

```python
@delete_bp.route("/faces/delete", methods=["POST"])
def delete_face():
```

Data yang dibutuhkan:

| Field | Status | Fungsi |
|---|---|---|
| `student_id` | Wajib | ID siswa yang data wajahnya akan dihapus |

Endpoint ini menggunakan method `POST`, bukan `DELETE`, karena pada catatan coding disebutkan bahwa Laravel `Http::delete()` tidak mudah mengirim body.

Alur proses:

```text
Request dari Laravel
↓
Validasi API key
↓
Ambil student_id
↓
Cari file embedding siswa
↓
Hapus file JSON
↓
Return sukses atau data tidak ditemukan
```

Kode utama:

```python
success = StorageService.delete_embedding(student_id)
```

Jika berhasil:

```json
{
  "success": true,
  "message": "Data wajah berhasil dihapus."
}
```

Jika data tidak ditemukan:

```json
{
  "success": false,
  "error_code": "PROFILE_NOT_FOUND",
  "message": "Data wajah siswa tidak ditemukan. Mungkin sudah dihapus sebelumnya."
}
```

---

## 12. Penjelasan File `routes/health.py`

File `routes/health.py` digunakan untuk mengecek apakah API berjalan dengan baik.

Endpoint:

```python
@health_bp.route("/health", methods=["GET"])
def health_check():
```

Endpoint ini tidak membutuhkan API key agar dapat digunakan untuk monitoring.

Response yang dikembalikan berisi:

```json
{
  "success": true,
  "service": "face-recognition-api",
  "version": "1.0.0",
  "status": "ok",
  "config": {
    "threshold": 0.45,
    "embeddings_count": 1
  }
}
```

Fungsi `embeddings_count` adalah menghitung berapa data wajah siswa yang sudah tersimpan.

---

## 13. Penjelasan File `services/face_service.py`

File ini adalah bagian paling penting dalam project karena berhubungan langsung dengan proses face recognition.

Di dalamnya terdapat class:

```python
class FaceService:
```

Class ini memiliki dua method utama:

1. `get_encodings_from_image()`
2. `compare_faces()`

---

### 13.1 Method `get_encodings_from_image()`

Kode:

```python
def get_encodings_from_image(image_file) -> tuple[list, int]:
```

Fungsi method ini adalah membaca gambar, mendeteksi wajah, lalu menghasilkan encoding atau embedding wajah.

Alur method:

```text
File gambar diterima
↓
Gambar dibaca oleh face_recognition
↓
Ukuran gambar divalidasi
↓
Lokasi wajah dideteksi
↓
Wajah diubah menjadi encoding/embedding
↓
Return encoding dan jumlah wajah
```

Bagian membaca gambar:

```python
image = face_recognition.load_image_file(image_file)
```

Jika file tidak dapat dibaca sebagai gambar, sistem mengembalikan error.

Validasi ukuran gambar:

```python
if image.shape[0] < 50 or image.shape[1] < 50:
    raise ValueError("Ukuran gambar terlalu kecil. Minimal 50x50 pixel.")
```

Artinya, gambar harus memiliki ukuran minimal 50 x 50 piksel.

Deteksi wajah:

```python
face_locations = face_recognition.face_locations(image)
```

Kode ini mencari lokasi wajah dalam gambar.

Ekstraksi embedding:

```python
face_encodings = face_recognition.face_encodings(image, face_locations)
```

Kode ini mengubah wajah menjadi representasi numerik.

Return:

```python
return face_encodings, len(face_encodings)
```

Output method ini adalah:

| Output | Fungsi |
|---|---|
| `face_encodings` | Data numerik wajah |
| `len(face_encodings)` | Jumlah wajah yang terdeteksi |

---

### 13.2 Method `compare_faces()`

Kode:

```python
def compare_faces(
    known_encoding: np.ndarray,
    face_encoding: np.ndarray,
    threshold: float = 0.45
) -> tuple[bool, float]:
```

Fungsi method ini adalah membandingkan embedding wajah yang sudah tersimpan dengan embedding wajah baru.

Kode utama:

```python
distance = float(face_recognition.face_distance([known_encoding], face_encoding)[0])
is_match = distance <= threshold
return is_match, distance
```

Metode yang digunakan adalah **Euclidean distance**.

Logikanya:

```text
Semakin kecil distance, semakin mirip wajahnya.
Semakin besar distance, semakin berbeda wajahnya.
```

Aturan keputusan:

```text
Jika distance <= threshold, wajah cocok.
Jika distance > threshold, wajah tidak cocok.
```

Contoh:

| Distance | Threshold | Hasil |
|---:|---:|---|
| `0.38` | `0.45` | Cocok |
| `0.44` | `0.45` | Cocok |
| `0.52` | `0.45` | Tidak cocok |
| `0.61` | `0.45` | Tidak cocok |

---

## 14. Penjelasan File `services/storage_service.py`

File ini mengatur penyimpanan data wajah siswa.

Class utama:

```python
class StorageService:
```

Data wajah tidak disimpan di database, tetapi disimpan dalam file `.json` di folder:

```text
storage/embeddings/
```

---

### 14.1 Method `_get_path()`

Kode:

```python
def _get_path(student_id: str) -> str:
    return os.path.join(Config.EMBEDDINGS_DIR, f"{student_id}.json")
```

Method ini membuat lokasi file embedding berdasarkan `student_id`.

Contoh:

```text
storage/embeddings/019dc2c7-f69f-73fb-a9a2-d1db94a38760.json
```

---

### 14.2 Method `save_embedding()`

Kode:

```python
def save_embedding(student_id: str, embedding: np.ndarray, metadata: dict = None) -> bool:
```

Method ini menyimpan atau memperbarui embedding wajah siswa.

Data yang disimpan:

```json
{
  "student_id": "...",
  "embedding": [...],
  "metadata": {
    "name": "...",
    "user_id": "..."
  },
  "updated_at": "..."
}
```

Bagian penting:

```python
embedding.tolist() if isinstance(embedding, np.ndarray) else embedding
```

Embedding awalnya berbentuk `numpy.ndarray`. Agar bisa disimpan ke JSON, data tersebut diubah menjadi list.

Project ini juga menggunakan lock:

```python
_write_lock = threading.Lock()
```

Dan saat menyimpan file:

```python
with _write_lock:
    with open(file_path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False)
```

Tujuannya adalah mencegah benturan penulisan file jika ada beberapa request masuk bersamaan.

---

### 14.3 Method `get_embedding()`

Kode:

```python
def get_embedding(student_id: str) -> np.ndarray | None:
```

Method ini membaca embedding dari file JSON.

Jika file tidak ditemukan, return:

```python
None
```

Jika file ditemukan, embedding dikembalikan sebagai `numpy array`:

```python
return np.array(data["embedding"])
```

---

### 14.4 Method `delete_embedding()`

Kode:

```python
def delete_embedding(student_id: str) -> bool:
```

Method ini menghapus file embedding siswa.

Jika file ada, method menghapus file dan mengembalikan:

```python
True
```

Jika file tidak ada, method mengembalikan:

```python
False
```

---

### 14.5 Method `list_all_students()`

Kode:

```python
def list_all_students() -> list[str]:
```

Method ini mengambil semua `student_id` yang sudah memiliki file embedding.

Method ini digunakan oleh endpoint `/health` untuk menghitung jumlah embedding yang tersimpan.

---

### 14.6 Method `student_exists()`

Kode:

```python
def student_exists(student_id: str) -> bool:
```

Method ini mengecek apakah siswa tertentu sudah memiliki data embedding atau belum.

---

## 15. Metode Machine Learning yang Digunakan

Project ini menggunakan beberapa tahapan metode dalam face recognition.

---

### 15.1 Face Detection

Face detection adalah proses mendeteksi keberadaan wajah dalam gambar.

Kode:

```python
face_locations = face_recognition.face_locations(image)
```

Tahap ini menjawab pertanyaan:

> Apakah ada wajah dalam foto ini, dan di mana posisi wajah tersebut?

Jika tidak ada wajah, proses dihentikan. Jika ada lebih dari satu wajah, proses juga dihentikan karena sistem hanya mengizinkan satu wajah dalam satu gambar.

---

### 15.2 Face Encoding atau Face Embedding

Face encoding adalah proses mengubah wajah menjadi data numerik.

Kode:

```python
face_encodings = face_recognition.face_encodings(image, face_locations)
```

Hasilnya disebut **embedding wajah**.

Embedding wajah adalah representasi wajah dalam bentuk angka. Angka-angka ini merepresentasikan ciri-ciri penting wajah seseorang.

Contoh bentuk sederhana embedding:

```text
[-0.111, 0.078, 0.017, -0.095, ...]
```

Data tersebut bukan foto, tetapi representasi numerik dari karakteristik wajah.

---

### 15.3 Penyimpanan Embedding

Setelah wajah diubah menjadi embedding, data tersebut disimpan ke file JSON.

Penting untuk dipahami bahwa saat enrollment, sistem tidak melakukan training ulang terhadap model. Sistem hanya menyimpan embedding sebagai data referensi.

Alur enrollment:

```text
Foto siswa
↓
Deteksi wajah
↓
Ekstraksi embedding
↓
Simpan embedding sebagai referensi
```

Jadi, istilah yang benar adalah:

> Enrollment wajah atau registrasi wajah.

Bukan:

> Training wajah siswa.

---

### 15.4 Face Verification

Project ini menggunakan pendekatan **face verification 1:1**.

Face verification menjawab pertanyaan:

> Apakah orang pada foto absensi benar merupakan siswa dengan ID tertentu?

Contoh:

```text
expected_student_id = siswa A
foto absensi = dikirim ke API
sistem membandingkan foto dengan embedding siswa A
hasil = cocok atau tidak cocok
```

Project ini **belum menggunakan face identification 1:N**.

Face identification menjawab pertanyaan:

> Orang pada foto ini siapa?

Jika menggunakan face identification, sistem harus membandingkan satu foto dengan semua embedding siswa yang ada. Pada project ini, sistem hanya membandingkan wajah baru dengan satu `expected_student_id`.

---

### 15.5 Euclidean Distance

Project ini membandingkan dua embedding wajah menggunakan jarak Euclidean.

Kode:

```python
distance = float(face_recognition.face_distance([known_encoding], face_encoding)[0])
```

Konsepnya:

```text
Embedding wajah lama  → data referensi
Embedding wajah baru  → data absensi
Distance              → jarak antara keduanya
```

Jika distance kecil, dua wajah dianggap mirip. Jika distance besar, dua wajah dianggap berbeda.

---

### 15.6 Threshold Matching

Threshold adalah batas untuk menentukan cocok atau tidaknya wajah.

Kode:

```python
is_match = distance <= threshold
```

Dalam project ini, threshold default adalah:

```text
0.45
```

Maka aturan keputusan:

```text
Jika distance <= 0.45 → verified = true
Jika distance > 0.45  → verified = false
```

Threshold ini sangat penting karena memengaruhi sensitivitas sistem.

Jika threshold terlalu kecil:

- sistem menjadi sangat ketat;
- wajah asli bisa ditolak;
- risiko false reject meningkat.

Jika threshold terlalu besar:

- sistem menjadi terlalu longgar;
- wajah orang lain bisa diterima;
- risiko false accept meningkat.

Karena itu, threshold sebaiknya diuji dengan data nyata sebelum digunakan secara luas.

---

## 16. Perbedaan Training, Enrollment, dan Verification

| Istilah | Arti | Apakah Ada di Project Ini? |
|---|---|---|
| Training | Melatih model machine learning dari dataset | Tidak ada |
| Enrollment | Mendaftarkan wajah dengan menyimpan embedding | Ada |
| Verification | Membandingkan wajah baru dengan wajah yang sudah terdaftar | Ada |
| Identification | Mencari identitas seseorang dari banyak data wajah | Belum ada |

Jadi, ketika menjelaskan project ini, sebaiknya jangan mengatakan:

> Sistem melakukan training wajah siswa.

Kalimat yang lebih tepat adalah:

> Sistem melakukan enrollment wajah siswa dengan mengekstraksi embedding wajah menggunakan pre-trained model, kemudian menyimpan embedding tersebut sebagai data referensi untuk proses verifikasi.

---

## 17. Alur Lengkap Sistem

### 17.1 Alur Enrollment Wajah

```text
Laravel / aplikasi utama
↓
Mengirim student_id, user_id, name, dan image
↓
Flask API menerima request di /faces/enroll
↓
API key divalidasi
↓
student_id dan image divalidasi
↓
FaceService membaca gambar
↓
FaceService mendeteksi wajah
↓
FaceService membuat embedding wajah
↓
StorageService menyimpan embedding ke file JSON
↓
API mengirim response sukses ke Laravel
```

---

### 17.2 Alur Verifikasi Absensi

```text
Laravel / aplikasi utama
↓
Mengirim expected_student_id dan image absensi
↓
Flask API menerima request di /faces/verify
↓
API key divalidasi
↓
StorageService mengambil embedding siswa yang sudah tersimpan
↓
FaceService membaca foto absensi
↓
FaceService mendeteksi wajah dalam foto absensi
↓
FaceService membuat embedding wajah baru
↓
FaceService membandingkan embedding lama dan baru
↓
Sistem menghitung distance
↓
Sistem membandingkan distance dengan threshold
↓
Jika cocok, verified = true
Jika tidak cocok, verified = false
↓
API mengirim hasil ke Laravel
```

---

### 17.3 Alur Delete Data Wajah

```text
Laravel / aplikasi utama
↓
Mengirim student_id
↓
Flask API menerima request di /faces/delete
↓
API key divalidasi
↓
StorageService mencari file embedding siswa
↓
Jika ada, file JSON dihapus
↓
API mengirim response sukses atau data tidak ditemukan
```

---

## 18. Bagian yang Termasuk Machine Learning dan Bukan Machine Learning

| Bagian Project | Termasuk ML? | Penjelasan |
|---|---|---|
| `face_recognition.face_locations()` | Ya | Digunakan untuk mendeteksi lokasi wajah |
| `face_recognition.face_encodings()` | Ya | Mengubah wajah menjadi embedding numerik |
| `face_recognition.face_distance()` | Ya, bagian dari proses matching | Menghitung jarak antar embedding |
| `Flask route` | Tidak | Hanya mengatur endpoint API |
| `API key` | Tidak | Bagian keamanan API |
| `StorageService` | Tidak langsung | Menyimpan hasil embedding, bukan melakukan ML |
| `JSON embedding` | Hasil proses ML | Data hasil ekstraksi fitur wajah |
| `threshold` | Pendukung keputusan | Menentukan cocok atau tidak berdasarkan distance |

---

## 19. Analisis Kelebihan Project

Beberapa kelebihan project ini:

1. Struktur project sudah cukup rapi karena memisahkan `routes` dan `services`.
2. Sudah memiliki validasi API key untuk komunikasi internal.
3. Sudah memiliki error handling yang cukup jelas.
4. Sudah membatasi ukuran upload file.
5. Sudah menggunakan threshold untuk menentukan kecocokan wajah.
6. Sudah menggunakan lock saat menulis file agar lebih aman dari benturan request.
7. Sudah cocok untuk integrasi awal dengan Laravel atau sistem absensi lain.
8. Sudah menggunakan pendekatan face verification yang sesuai untuk absensi berbasis ID siswa.

---

## 20. Keterbatasan Project

Beberapa keterbatasan project ini:

### 20.1 Tidak Ada Training Model Sendiri

Project ini tidak melatih model sendiri. Sistem hanya memakai model yang sudah tersedia dari library `face_recognition`.

Hal ini sebenarnya bukan masalah untuk tahap implementasi awal, tetapi perlu dijelaskan secara benar dalam laporan.

### 20.2 Penyimpanan Masih Berbasis File JSON

Data embedding disimpan dalam file JSON. Untuk jumlah siswa yang sedikit, cara ini masih bisa digunakan.

Namun, untuk skala lebih besar, sebaiknya menggunakan database seperti:

- MySQL;
- PostgreSQL;
- SQLite;
- Redis;
- atau vector database.

### 20.3 Belum Ada Liveness Detection

Project ini belum memiliki fitur untuk membedakan wajah asli dan foto wajah.

Artinya, ada kemungkinan seseorang mencoba melakukan absensi menggunakan foto orang lain. Untuk meningkatkan keamanan, dapat ditambahkan liveness detection, misalnya:

- deteksi kedipan mata;
- deteksi gerakan kepala;
- challenge-response seperti menoleh kanan/kiri;
- deteksi kedalaman wajah;
- anti-spoofing model.

### 20.4 Belum Ada Evaluasi Akurasi

Project ini belum memiliki modul untuk menguji akurasi sistem.

Idealnya, sistem diuji dengan data nyata untuk menghitung:

- accuracy;
- false accept rate;
- false reject rate;
- precision;
- recall;
- confusion matrix.

### 20.5 Belum Ada Face Identification 1:N

Project ini hanya melakukan verification 1:1. Sistem belum bisa menerima satu foto lalu mencari siapa orang tersebut dari seluruh database siswa.

### 20.6 File `templates/index.html` Belum Sinkron dengan API

Pada file `templates/index.html`, JavaScript mengirim request ke:

```javascript
fetch("/scan", {
```

Namun, pada Flask project tidak ada endpoint `/scan`.

Endpoint yang tersedia adalah:

```text
/faces/enroll
/faces/verify
/faces/delete
/health
```

Artinya, file `index.html` kemungkinan masih template lama atau belum disesuaikan dengan API saat ini.

---

## 21. Penjelasan Sederhana untuk Presentasi

Jika harus menjelaskan project ini secara singkat dalam presentasi, bisa menggunakan narasi berikut:

> Project ini merupakan API pengenalan wajah berbasis Python Flask yang digunakan untuk mendukung absensi berbasis kamera. Sistem ini menggunakan library `face_recognition` yang di dalamnya sudah tersedia pre-trained machine learning model. Model tersebut digunakan untuk mendeteksi wajah dan mengubah wajah menjadi embedding numerik. Pada saat pendaftaran, sistem menyimpan embedding wajah siswa sebagai data referensi. Pada saat absensi, sistem mengambil foto baru, mengekstraksi embedding wajah, lalu membandingkannya dengan embedding yang sudah tersimpan menggunakan Euclidean distance. Jika jarak kemiripan berada di bawah threshold yang ditentukan, maka wajah dianggap cocok dan proses absensi dapat dinyatakan valid.

---

## 22. Penjelasan Akademik untuk Laporan

Berikut contoh penjelasan yang dapat digunakan dalam laporan:

> Sistem face recognition pada project ini menggunakan pendekatan machine learning berbasis pre-trained model melalui library `face_recognition`. Model tersebut digunakan untuk mendeteksi wajah dan mengekstraksi fitur wajah ke dalam bentuk embedding numerik. Pada proses enrollment, sistem tidak melakukan pelatihan model baru, melainkan menyimpan embedding wajah siswa sebagai data referensi. Pada proses verifikasi absensi, embedding dari foto baru dibandingkan dengan embedding yang telah tersimpan menggunakan metode Euclidean distance. Hasil perbandingan kemudian ditentukan berdasarkan nilai threshold, yaitu apabila nilai distance lebih kecil atau sama dengan threshold, maka wajah dianggap cocok.

Versi yang lebih sederhana:

> Project ini menggunakan machine learning, tetapi bukan dengan cara melatih model sendiri. Sistem memakai model face recognition yang sudah dilatih sebelumnya untuk mengubah wajah menjadi data angka atau embedding. Data embedding tersebut kemudian disimpan dan dibandingkan dengan foto absensi menggunakan jarak Euclidean. Jika jaraknya berada di bawah batas threshold, maka wajah dianggap cocok.

---

## 23. Kesimpulan

Project ini adalah **Face Recognition API berbasis Flask** yang menggunakan **machine learning melalui pre-trained model** dari library `face_recognition`.

Metode utama yang digunakan adalah:

1. **Face Detection** untuk mendeteksi wajah dalam gambar.
2. **Face Encoding atau Face Embedding** untuk mengubah wajah menjadi data numerik.
3. **Enrollment** untuk menyimpan embedding wajah siswa sebagai data referensi.
4. **Face Verification 1:1** untuk membandingkan wajah absensi dengan wajah siswa yang sudah terdaftar.
5. **Euclidean Distance** untuk menghitung jarak antar embedding wajah.
6. **Threshold Matching** untuk menentukan apakah wajah cocok atau tidak.

Project ini **bukan sistem training model dari nol**, tetapi sistem implementasi face recognition yang memanfaatkan model machine learning yang sudah dilatih sebelumnya.

Dengan demikian, kalimat paling tepat untuk menjelaskan project ini adalah:

> Project ini menerapkan face recognition berbasis pre-trained machine learning model. Sistem mengekstraksi embedding wajah siswa, menyimpannya sebagai data referensi, lalu membandingkan embedding wajah baru dengan embedding yang tersimpan menggunakan Euclidean distance dan threshold matching untuk menentukan validitas absensi.
