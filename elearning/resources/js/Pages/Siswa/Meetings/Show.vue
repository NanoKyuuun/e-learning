<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    FileText, ClipboardList, Download, ArrowLeft, Calendar, User,
    Camera, CheckCircle, XCircle, Clock, AlertTriangle, Loader2, ShieldCheck
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    meeting:           Object,
    myAttendance:      Object,
    faceProfileStatus: Object,
    isAttendanceOpen:  Boolean,
});

// ─── Camera State ───────────────────────────────────────────────────────────
const showCameraModal   = ref(false);
const stream            = ref(null);
const videoRef          = ref(null);
const canvasRef         = ref(null);
const capturedImage     = ref(null);
const cameraError       = ref(null);
const isSubmitting      = ref(false);
const attendanceResult  = ref(null); // { success, message, data }

const attendanceForm = useForm({ image: null });

// ─── Computed ────────────────────────────────────────────────────────────────
const canAttend = computed(() => {
    return props.isAttendanceOpen
        && !props.myAttendance
        && props.faceProfileStatus?.is_ready;
});

const attendanceBlockReason = computed(() => {
    if (props.myAttendance)             return 'Anda sudah melakukan absensi.';
    if (!props.isAttendanceOpen)        return 'Absensi belum dibuka oleh guru.';
    if (!props.faceProfileStatus?.exists) return 'Wajah Anda belum terdaftar. Hubungi admin/guru.';
    if (!props.faceProfileStatus?.is_active) return 'Data wajah Anda dinonaktifkan. Hubungi admin.';
    const s = props.faceProfileStatus?.sync_status;
    if (s === 'pending' || s === 'syncing') return 'Data wajah Anda sedang diproses. Coba lagi nanti.';
    if (s === 'failed') return 'Data wajah Anda gagal disinkronkan. Hubungi guru atau admin.';
    return null;
});

const statusBadge = computed(() => {
    if (!props.faceProfileStatus?.exists)    return { label: 'Belum Daftar', cls: 'badge-error' };
    const map = {
        synced:   { label: 'Siap', cls: 'badge-success' },
        pending:  { label: 'Menunggu', cls: 'badge-warning' },
        syncing:  { label: 'Memproses', cls: 'badge-info' },
        failed:   { label: 'Gagal Sync', cls: 'badge-error' },
        disabled: { label: 'Nonaktif', cls: 'badge-ghost' },
    };
    return map[props.faceProfileStatus?.sync_status] ?? { label: 'Tidak Diketahui', cls: 'badge-ghost' };
});

// ─── Camera Methods ──────────────────────────────────────────────────────────
const openCamera = async () => {
    capturedImage.value  = null;
    attendanceResult.value = null;
    cameraError.value    = null;
    showCameraModal.value = true;

    try {
        stream.value = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' },
            audio: false,
        });
        // Beri waktu DOM render dulu
        setTimeout(() => {
            if (videoRef.value) videoRef.value.srcObject = stream.value;
        }, 100);
    } catch (err) {
        cameraError.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
    }
};

const stopCamera = () => {
    stream.value?.getTracks().forEach(t => t.stop());
    stream.value = null;
};

const closeModal = () => {
    stopCamera();
    showCameraModal.value  = false;
    capturedImage.value    = null;
    attendanceResult.value = null;
};

const capturePhoto = () => {
    if (!videoRef.value || !canvasRef.value) return;
    const ctx = canvasRef.value.getContext('2d');
    canvasRef.value.width  = videoRef.value.videoWidth;
    canvasRef.value.height = videoRef.value.videoHeight;
    ctx.drawImage(videoRef.value, 0, 0);
    capturedImage.value = canvasRef.value.toDataURL('image/jpeg', 0.9);
    stopCamera();
};

const retakePhoto = async () => {
    capturedImage.value = null;
    await openCamera();
};

const submitAttendance = () => {
    if (!capturedImage.value) return;

    // Konversi dataURL ke Blob/File
    const byteString   = atob(capturedImage.value.split(',')[1]);
    const ab           = new ArrayBuffer(byteString.length);
    const ia           = new Uint8Array(ab);
    for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
    const blob = new Blob([ab], { type: 'image/jpeg' });
    const file = new File([blob], 'attendance.jpg', { type: 'image/jpeg' });

    isSubmitting.value = true;
    attendanceResult.value = null;

    const formData = new FormData();
    formData.append('image', file);

    axios.post(route('siswa.attendance.face.store', props.meeting.id), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        attendanceResult.value = {
            success: true,
            message: response.data.message || 'Absensi berhasil dicatat!',
        };
        // Reload data absensi di background agar tampilan utama terupdate
        router.reload({ only: ['myAttendance'] });
    })
    .catch(error => {
        attendanceResult.value = {
            success: false,
            message: error.response?.data?.message ?? 'Absensi gagal. Coba lagi.',
        };
    })
    .finally(() => {
        isSubmitting.value = false;
    });
};
</script>

<template>
    <Head :title="meeting.title" />

    <SiswaLayout>
        <!-- Back -->
        <div class="mb-8">
            <Link :href="route('siswa.meetings.index', meeting.teaching_assignment_id)"
                  class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar Sesi
            </Link>

            <!-- Header Card -->
            <div class="bg-base-100 p-8 rounded-3xl border border-base-200 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <FileText class="w-32 h-32" />
                </div>
                <div class="flex items-center gap-6 relative z-10">
                    <div class="bg-primary text-primary-content w-20 h-20 rounded-2xl flex flex-col items-center justify-center font-black shadow-lg shadow-primary/20">
                        <span class="text-xs opacity-70 uppercase">Sesi</span>
                        <span class="text-3xl">{{ meeting.meeting_number }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">{{ meeting.title }}</h1>
                        <p class="text-base-content/60 font-medium mt-1">{{ meeting.teaching_assignment.subject.name }}</p>
                    </div>
                </div>

                <div class="divider my-8"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-2">Topik Pembelajaran</h3>
                        <p class="text-base-content leading-relaxed italic">
                            {{ meeting.topic || 'Guru belum memberikan deskripsi topik untuk sesi ini.' }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-3 justify-end md:items-end">
                        <span class="text-sm font-bold opacity-50 flex items-center gap-2">
                            <Calendar class="w-4 h-4" /> {{ meeting.meeting_date || 'Tanggal fleksibel' }}
                        </span>
                        <span class="text-sm font-bold opacity-50 flex items-center gap-2">
                            <User class="w-4 h-4" /> {{ meeting.teaching_assignment.teacher.user.full_name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             SEKSI ABSENSI KAMERA
        ════════════════════════════════════════════════════════════ -->
        <div class="mb-8">
            <div class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
                <!-- Header seksi absensi -->
                <div class="flex items-center justify-between p-6 border-b border-base-200">
                    <h2 class="text-lg font-black flex items-center gap-2 uppercase tracking-tight">
                        <ShieldCheck class="w-5 h-5 text-success" /> Absensi Kamera
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold opacity-50">Status Wajah:</span>
                        <span :class="['badge badge-sm font-bold', statusBadge.cls]">{{ statusBadge.label }}</span>
                        <span :class="['badge badge-sm font-bold', isAttendanceOpen ? 'badge-success' : 'badge-ghost']">
                            {{ isAttendanceOpen ? 'Absensi Terbuka' : 'Belum Dibuka' }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Sudah absen -->
                    <div v-if="myAttendance" class="flex items-center gap-4 p-5 bg-success/10 rounded-2xl border border-success/20">
                        <CheckCircle class="w-10 h-10 text-success flex-shrink-0" />
                        <div>
                            <p class="font-black text-success text-lg">Absensi Berhasil Dicatat</p>
                            <p class="text-sm opacity-70 mt-1">
                                Status: <span class="font-bold uppercase">{{ myAttendance.status }}</span>
                                <span v-if="myAttendance.check_in_at" class="ml-2">
                                    · Pukul {{ new Date(myAttendance.check_in_at).toLocaleTimeString('id-ID') }}
                                </span>
                            </p>
                            <p v-if="myAttendance.face_verified" class="text-xs text-success/70 mt-1 flex items-center gap-1">
                                <CheckCircle class="w-3 h-3" /> Wajah terverifikasi (jarak: {{ myAttendance.face_distance?.toFixed(3) }})
                            </p>
                        </div>
                    </div>

                    <!-- Bisa absen -->
                    <div v-else-if="canAttend" class="flex flex-col sm:flex-row items-center gap-4 p-5 bg-primary/5 rounded-2xl border border-primary/20">
                        <Camera class="w-10 h-10 text-primary flex-shrink-0" />
                        <div class="flex-1">
                            <p class="font-black text-base-content">Silakan lakukan absensi sekarang</p>
                            <p class="text-sm opacity-60 mt-1">Pastikan wajah Anda terlihat jelas dan hanya Anda sendiri yang ada di kamera.</p>
                        </div>
                        <button @click="openCamera"
                                class="btn btn-primary gap-2 shadow-lg shadow-primary/20 flex-shrink-0">
                            <Camera class="w-4 h-4" /> Buka Kamera
                        </button>
                    </div>

                    <!-- Tidak bisa absen (dengan alasan) -->
                    <div v-else class="flex items-center gap-4 p-5 bg-base-200/50 rounded-2xl border border-base-300">
                        <AlertTriangle class="w-8 h-8 text-warning flex-shrink-0" />
                        <div>
                            <p class="font-bold text-base-content/70">Absensi tidak tersedia</p>
                            <p class="text-sm text-base-content/50 mt-0.5">{{ attendanceBlockReason }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ════════════════════════════════════════════════════════════ -->

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sisi Kiri: Materi -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-2 text-primary uppercase tracking-tight">
                        <FileText class="w-6 h-6" /> Materi Belajar
                    </h2>
                    <span class="badge badge-primary font-bold">{{ meeting.materials.length }} File</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="material in meeting.materials" :key="material.id"
                         class="card bg-base-100 shadow-md border border-base-200 hover:border-primary/30 transition-all group">
                        <div class="card-body p-5">
                            <div class="flex items-start gap-4">
                                <div class="bg-base-200 p-3 rounded-xl text-primary group-hover:bg-primary group-hover:text-primary-content transition-colors">
                                    <FileText class="w-6 h-6" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-base leading-tight truncate">{{ material.title }}</h3>
                                    <p class="text-xs opacity-50 mt-1 line-clamp-1 italic">{{ material.description || 'Tidak ada deskripsi.' }}</p>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4 pt-4 border-t border-base-200">
                                <a v-if="material.file_url" :href="'/storage/' + material.file_url" target="_blank"
                                   class="btn btn-sm btn-ghost gap-2 text-primary hover:bg-primary/10">
                                    <Download class="w-4 h-4" /> Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.materials.length === 0"
                     class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic">
                    Belum ada materi yang diunggah untuk sesi ini.
                </div>
            </div>

            <!-- Sisi Kanan: Tugas -->
            <div class="lg:col-span-1 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-2 text-secondary uppercase tracking-tight">
                        <ClipboardList class="w-6 h-6" /> Tugas
                    </h2>
                    <span class="badge badge-secondary font-bold">{{ meeting.assignments.length }}</span>
                </div>

                <div v-for="assignment in meeting.assignments" :key="assignment.id"
                     class="card bg-base-100 shadow-md border-l-4 border-l-secondary border border-base-200">
                    <div class="card-body p-5">
                        <h3 class="font-bold text-base-content">{{ assignment.title }}</h3>
                        <p class="text-xs opacity-60 mt-1">Batas Waktu: <span class="font-bold">{{ assignment.due_at }}</span></p>
                        <div class="card-actions mt-4">
                            <Link :href="route('siswa.assignments.show', assignment.id)"
                                  class="btn btn-secondary btn-sm btn-block shadow-lg shadow-secondary/20">
                                Lihat Tugas
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.assignments.length === 0"
                     class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic text-sm">
                    Tidak ada tugas untuk sesi ini.
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             MODAL KAMERA ABSENSI
        ════════════════════════════════════════════════════════════ -->
        <div v-if="showCameraModal"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-base-100 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                <!-- Header modal -->
                <div class="flex items-center justify-between p-6 border-b border-base-200">
                    <h3 class="font-black text-xl flex items-center gap-2">
                        <Camera class="w-5 h-5 text-primary" /> Absensi Kamera
                    </h3>
                    <button @click="closeModal" class="btn btn-ghost btn-sm btn-circle">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Error kamera -->
                    <div v-if="cameraError"
                         class="alert alert-error rounded-2xl text-sm font-medium">
                        <XCircle class="w-5 h-5" /> {{ cameraError }}
                    </div>

                    <!-- Hasil absensi -->
                    <div v-if="attendanceResult"
                         :class="['alert rounded-2xl font-bold text-base', attendanceResult.success ? 'alert-success' : 'alert-error']">
                        <component :is="attendanceResult.success ? CheckCircle : XCircle" class="w-6 h-6" />
                        {{ attendanceResult.message }}
                    </div>

                    <!-- Preview kamera live -->
                    <div v-if="!capturedImage && !attendanceResult" class="relative">
                        <video ref="videoRef" autoplay muted playsinline
                               class="w-full rounded-2xl bg-black aspect-[4/3] object-cover" />
                        <!-- Panduan frame wajah -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-48 h-60 border-4 border-primary/50 rounded-full opacity-60"></div>
                        </div>
                        <p class="text-center text-xs opacity-50 mt-2">Posisikan wajah Anda di dalam lingkaran</p>
                    </div>

                    <!-- Preview foto yang sudah diambil -->
                    <div v-if="capturedImage && !attendanceResult">
                        <img :src="capturedImage" class="w-full rounded-2xl object-cover aspect-[4/3]" alt="Foto absensi" />
                        <p class="text-center text-xs opacity-50 mt-2">Pastikan wajah Anda terlihat jelas</p>
                    </div>

                    <!-- Canvas (hidden) untuk capture -->
                    <canvas ref="canvasRef" class="hidden" />

                    <!-- Tombol aksi -->
                    <div v-if="!attendanceResult" class="flex gap-3">
                        <button v-if="!capturedImage" @click="capturePhoto"
                                :disabled="!!cameraError"
                                class="btn btn-primary flex-1 gap-2 shadow-lg shadow-primary/20">
                            <Camera class="w-4 h-4" /> Ambil Foto
                        </button>

                        <template v-if="capturedImage">
                            <button @click="retakePhoto"
                                    class="btn btn-ghost flex-1 gap-2">
                                Ambil Ulang
                            </button>
                            <button @click="submitAttendance"
                                    :disabled="isSubmitting"
                                    class="btn btn-success flex-1 gap-2 shadow-lg shadow-success/20 text-white">
                                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                                <CheckCircle v-else class="w-4 h-4" />
                                {{ isSubmitting ? 'Memproses...' : 'Kirim Absensi' }}
                            </button>
                        </template>
                    </div>

                    <!-- Setelah hasil: tombol tutup -->
                    <button v-if="attendanceResult" @click="closeModal"
                            class="btn btn-block btn-ghost">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        <!-- ════════════════════════════════════════════════════════════ -->
    </SiswaLayout>
</template>
