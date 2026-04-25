<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Users, RefreshCw, Upload, Trash2, CheckCircle, XCircle,
    Clock, AlertTriangle, Wifi, WifiOff, Search, Camera
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    students:  Object,  // Paginated
    filters:   Object,
    apiStatus: Object,
});

// ─── Upload Modal ─────────────────────────────────────────────────────────────
const showUploadModal   = ref(false);
const selectedStudent   = ref(null);
const previewUrl        = ref(null);

const uploadForm = useForm({ image: null });

const openUpload = (student) => {
    selectedStudent.value = student;
    previewUrl.value      = null;
    uploadForm.reset();
    showUploadModal.value  = true;
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    uploadForm.image = file;
    previewUrl.value = URL.createObjectURL(file);
};

const submitUpload = () => {
    const hasFace = !!selectedStudent.value?.face_profile;
    const routeName = hasFace ? 'admin.face-profiles.update' : 'admin.face-profiles.store';

    uploadForm.post(route(routeName, selectedStudent.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            previewUrl.value      = null;
        },
    });
};

// ─── Resync ───────────────────────────────────────────────────────────────────
const resync = (studentId) => {
    router.post(route('admin.face-profiles.resync', studentId));
};

const resyncAll = () => {
    if (!confirm('Sync ulang semua siswa yang memiliki data wajah?')) return;
    router.post(route('admin.face-profiles.resync-all'));
};

// ─── Disable ──────────────────────────────────────────────────────────────────
const disableFace = (studentId) => {
    if (!confirm('Nonaktifkan data wajah siswa ini? Siswa tidak akan bisa absen sampai di-enroll ulang.')) return;
    router.delete(route('admin.face-profiles.destroy', studentId));
};

// ─── Search ───────────────────────────────────────────────────────────────────
const search = ref(props.filters?.search ?? '');
const doSearch = () => {
    router.get(route('admin.face-profiles.index'), { search: search.value }, { preserveState: true });
};

// ─── Helpers ─────────────────────────────────────────────────────────────────
const syncBadge = (status) => {
    const map = {
        synced:   { label: 'Synced',    cls: 'badge-success' },
        pending:  { label: 'Pending',   cls: 'badge-warning' },
        syncing:  { label: 'Syncing',   cls: 'badge-info' },
        failed:   { label: 'Gagal',     cls: 'badge-error' },
        disabled: { label: 'Nonaktif',  cls: 'badge-ghost' },
    };
    return map[status] ?? { label: status, cls: 'badge-ghost' };
};
</script>

<template>
    <Head title="Kelola Data Wajah Siswa" />
    <AdminLayout>
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black tracking-tight uppercase flex items-center gap-3">
                    <Camera class="w-8 h-8 text-primary" /> Manajemen Wajah Siswa
                </h1>
                <p class="text-base-content/50 text-sm mt-1">Daftar siswa, status sync wajah ke Python Face API, dan enkollment foto.</p>
            </div>

            <!-- API Status -->
            <div :class="['flex items-center gap-2 px-4 py-2 rounded-2xl border font-bold text-sm',
                         apiStatus.online ? 'bg-success/10 border-success/30 text-success' : 'bg-error/10 border-error/30 text-error']">
                <component :is="apiStatus.online ? Wifi : WifiOff" class="w-4 h-4" />
                <span>Face API: {{ apiStatus.online ? 'Online' : 'Offline' }}</span>
                <span v-if="apiStatus.online" class="opacity-60 text-xs">
                    · {{ apiStatus.embeddings_count }} embedding · v{{ apiStatus.version }}
                </span>
            </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="alert alert-success mb-6 rounded-2xl font-bold shadow-sm border-none bg-success/10 text-success">
            <CheckCircle class="w-5 h-5" /> {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="alert alert-error mb-6 rounded-2xl font-bold shadow-sm border-none bg-error/10 text-error">
            <XCircle class="w-5 h-5" /> {{ $page.props.flash.error }}
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <!-- Search -->
            <div class="flex-1 flex gap-2">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 opacity-40" />
                    <input v-model="search" @keydown.enter="doSearch" type="text"
                           placeholder="Cari nama siswa..."
                           class="input input-bordered w-full pl-9 rounded-2xl" />
                </div>
                <button @click="doSearch" class="btn btn-primary rounded-2xl gap-2">
                    <Search class="w-4 h-4" /> Cari
                </button>
            </div>
            <!-- Resync All -->
            <button @click="resyncAll" class="btn btn-outline btn-warning rounded-2xl gap-2">
                <RefreshCw class="w-4 h-4" /> Resync Semua
            </button>
        </div>

        <!-- Table -->
        <div class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="text-xs uppercase tracking-widest opacity-50">
                            <th>#</th>
                            <th>Siswa</th>
                            <th>Status Wajah</th>
                            <th>Terakhir Sync</th>
                            <th>Error</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, idx) in students.data" :key="student.id"
                            class="hover:bg-base-200/50 transition-colors">
                            <td class="font-bold opacity-40 text-sm">
                                {{ (students.current_page - 1) * students.per_page + idx + 1 }}
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-full w-10 h-10 font-black">
                                            <span>{{ student.user?.full_name?.charAt(0) ?? '?' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ student.user?.full_name }}</p>
                                        <p class="text-xs opacity-50">{{ student.user?.username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="student.face_profile">
                                    <span :class="['badge font-bold', syncBadge(student.face_profile.sync_status).cls]">
                                        {{ syncBadge(student.face_profile.sync_status).label }}
                                    </span>
                                </div>
                                <span v-else class="badge badge-ghost font-bold text-xs">Belum Didaftarkan</span>
                            </td>
                            <td class="text-xs font-mono opacity-60">
                                {{ student.face_profile?.last_synced_at
                                    ? new Date(student.face_profile.last_synced_at).toLocaleString('id-ID')
                                    : '—' }}
                            </td>
                            <td class="max-w-[200px]">
                                <span v-if="student.face_profile?.sync_error"
                                      class="text-xs text-error truncate block"
                                      :title="student.face_profile.sync_error">
                                    {{ student.face_profile.sync_error }}
                                </span>
                                <span v-else class="text-xs opacity-30">—</span>
                            </td>
                            <td>
                                <div class="flex justify-end items-center gap-1">
                                    <!-- Upload / Ganti Foto -->
                                    <button @click="openUpload(student)"
                                            class="btn btn-xs btn-primary gap-1 font-bold">
                                        <Upload class="w-3 h-3" />
                                        {{ student.face_profile ? 'Ganti Foto' : 'Daftarkan' }}
                                    </button>

                                    <!-- Resync (jika sudah ada profile) -->
                                    <button v-if="student.face_profile?.is_active"
                                            @click="resync(student.id)"
                                            class="btn btn-xs btn-outline btn-warning gap-1 font-bold">
                                        <RefreshCw class="w-3 h-3" /> Sync
                                    </button>

                                    <!-- Nonaktifkan -->
                                    <button v-if="student.face_profile?.is_active"
                                            @click="disableFace(student.id)"
                                            class="btn btn-xs btn-ghost btn-square text-error">
                                        <Trash2 class="w-3 h-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="students.data.length === 0"
                     class="p-16 text-center opacity-40 italic">
                    Tidak ada siswa ditemukan.
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="students.last_page > 1"
                 class="flex justify-center gap-2 p-6 border-t border-base-200">
                <Link v-for="link in students.links" :key="link.label"
                      :href="link.url ?? '#'"
                      :class="['btn btn-sm rounded-xl', link.active ? 'btn-primary' : 'btn-ghost', !link.url ? 'btn-disabled opacity-30' : '']"
                      v-html="link.label" />
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MODAL UPLOAD FOTO
        ══════════════════════════════════════════════════════════ -->
        <div v-if="showUploadModal"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-base-100 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-base-200">
                    <h3 class="font-black text-xl flex items-center gap-2">
                        <Camera class="w-5 h-5 text-primary" />
                        {{ selectedStudent?.face_profile ? 'Ganti Foto Wajah' : 'Daftarkan Wajah' }}
                    </h3>
                    <button @click="showUploadModal = false" class="btn btn-ghost btn-sm btn-circle">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="text-sm font-bold opacity-60">
                        Siswa: <span class="text-base-content">{{ selectedStudent?.user?.full_name }}</span>
                    </div>

                    <!-- Preview foto -->
                    <div v-if="previewUrl" class="rounded-2xl overflow-hidden border border-base-200">
                        <img :src="previewUrl" class="w-full object-cover max-h-60" alt="Preview foto" />
                    </div>
                    <div v-else class="rounded-2xl border-2 border-dashed border-base-300 h-40 flex flex-col items-center justify-center opacity-40 gap-2">
                        <Camera class="w-10 h-10" />
                        <span class="text-sm">Belum ada foto dipilih</span>
                    </div>

                    <!-- File input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-bold">Pilih Foto Wajah</span>
                            <span class="label-text-alt opacity-50">JPG/PNG, maks 5 MB, min 100×100px</span>
                        </label>
                        <input type="file" accept="image/jpeg,image/png"
                               @change="onFileChange"
                               class="file-input file-input-bordered file-input-primary w-full rounded-2xl" />
                        <p v-if="uploadForm.errors.image" class="text-error text-xs mt-1">{{ uploadForm.errors.image }}</p>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showUploadModal = false" class="btn btn-ghost flex-1">Batal</button>
                        <button @click="submitUpload"
                                :disabled="!uploadForm.image || uploadForm.processing"
                                class="btn btn-primary flex-1 gap-2 shadow-lg shadow-primary/20">
                            <Upload class="w-4 h-4" />
                            {{ uploadForm.processing ? 'Mengunggah...' : 'Simpan & Sync' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ══════════════════════════════════════════════════════════ -->
    </AdminLayout>
</template>
