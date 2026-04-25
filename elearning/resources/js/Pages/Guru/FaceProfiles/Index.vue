<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Users, RefreshCw, CheckCircle, XCircle, AlertTriangle,
    ArrowLeft, Camera, ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    teachingAssignment: Object,
    students:           Array,
});

// Resync satu siswa
const resync = (studentId) => {
    router.post(route('guru.face-profiles.resync', {
        teachingAssignment: props.teachingAssignment.id,
        student:            studentId,
    }));
};

// Resync semua siswa di kelas ini
const resyncAll = () => {
    if (!confirm('Sync ulang semua siswa di kelas ini yang memiliki data wajah?')) return;
    router.post(route('guru.face-profiles.resync-class', {
        teachingAssignment: props.teachingAssignment.id,
    }));
};

const syncBadge = (status) => {
    const map = {
        synced:   { label: 'Siap',     cls: 'badge-success', icon: 'check' },
        pending:  { label: 'Pending',  cls: 'badge-warning', icon: 'clock' },
        syncing:  { label: 'Syncing',  cls: 'badge-info',    icon: 'clock' },
        failed:   { label: 'Gagal',    cls: 'badge-error',   icon: 'x' },
        disabled: { label: 'Nonaktif', cls: 'badge-ghost',   icon: 'none' },
        none:     { label: 'Belum Ada', cls: 'badge-ghost',  icon: 'none' },
    };
    return map[status] ?? { label: status, cls: 'badge-ghost', icon: 'none' };
};

// Statistik
const totalStudents = props.students?.length ?? 0;
const synced   = props.students?.filter(s => s.face_profile?.sync_status === 'synced').length ?? 0;
const noFace   = props.students?.filter(s => !s.face_profile || !s.face_profile.is_active).length ?? 0;
const failed   = props.students?.filter(s => s.face_profile?.sync_status === 'failed').length ?? 0;
</script>

<template>
    <Head :title="'Status Wajah - ' + teachingAssignment.class_group.name" />

    <GuruLayout>
        <!-- Header -->
        <div class="mb-8">
            <Link :href="route('guru.courses.index')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar Kelas
            </Link>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                        <Camera class="w-6 h-6 text-primary" /> Status Wajah Siswa
                    </h1>
                    <p class="text-sm opacity-60 mt-1">
                        {{ teachingAssignment.subject.name }} · Kelas {{ teachingAssignment.class_group.name }}
                    </p>
                </div>
                <button @click="resyncAll" class="btn btn-outline btn-warning gap-2 rounded-2xl">
                    <RefreshCw class="w-4 h-4" /> Resync Semua Kelas
                </button>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert alert-success mb-6 rounded-2xl font-bold bg-success/10 text-success border-none">
            <CheckCircle class="w-5 h-5" /> {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="alert alert-error mb-6 rounded-2xl font-bold bg-error/10 text-error border-none">
            <XCircle class="w-5 h-5" /> {{ $page.props.flash.error }}
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="stat bg-base-100 rounded-2xl border border-base-200 shadow-sm p-4">
                <div class="stat-desc font-bold uppercase tracking-wider text-xs">Total Siswa</div>
                <div class="stat-value text-3xl font-black">{{ totalStudents }}</div>
            </div>
            <div class="stat bg-success/10 rounded-2xl border border-success/20 shadow-sm p-4">
                <div class="stat-desc font-bold uppercase tracking-wider text-xs text-success">Siap Absen</div>
                <div class="stat-value text-3xl font-black text-success">{{ synced }}</div>
            </div>
            <div class="stat bg-error/10 rounded-2xl border border-error/20 shadow-sm p-4">
                <div class="stat-desc font-bold uppercase tracking-wider text-xs text-error">Gagal Sync</div>
                <div class="stat-value text-3xl font-black text-error">{{ failed }}</div>
            </div>
            <div class="stat bg-warning/10 rounded-2xl border border-warning/20 shadow-sm p-4">
                <div class="stat-desc font-bold uppercase tracking-wider text-xs text-warning">Belum/Nonaktif</div>
                <div class="stat-value text-3xl font-black text-warning">{{ noFace }}</div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="text-xs uppercase tracking-widest opacity-50">
                            <th>#</th>
                            <th>Nama Siswa</th>
                            <th>Status Wajah</th>
                            <th>Terakhir Sync</th>
                            <th>Keterangan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, idx) in students" :key="student.id"
                            class="hover:bg-base-200/40 transition-colors">
                            <td class="font-bold opacity-40 text-sm">{{ idx + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-full w-10 h-10 font-black">
                                            <span>{{ student.user?.full_name?.charAt(0) ?? '?' }}</span>
                                        </div>
                                    </div>
                                    <span class="font-bold">{{ student.user?.full_name }}</span>
                                </div>
                            </td>
                            <td>
                                <div v-if="student.face_profile">
                                    <span :class="['badge font-bold',
                                        syncBadge(student.face_profile.sync_status).cls]">
                                        {{ syncBadge(student.face_profile.sync_status).label }}
                                    </span>
                                </div>
                                <span v-else class="badge badge-ghost text-xs font-bold">Belum Terdaftar</span>
                            </td>
                            <td class="text-xs font-mono opacity-60">
                                {{ student.face_profile?.last_synced_at
                                    ? new Date(student.face_profile.last_synced_at).toLocaleString('id-ID')
                                    : '—' }}
                            </td>
                            <td class="max-w-[180px]">
                                <span v-if="student.face_profile?.sync_error"
                                      class="text-xs text-error line-clamp-1"
                                      :title="student.face_profile.sync_error">
                                    {{ student.face_profile.sync_error }}
                                </span>
                                <span v-else-if="student.face_profile?.sync_status === 'synced'"
                                      class="text-xs text-success flex items-center gap-1">
                                    <CheckCircle class="w-3 h-3" /> Siap untuk absensi
                                </span>
                                <span v-else class="text-xs opacity-30">—</span>
                            </td>
                            <td>
                                <div class="flex justify-end">
                                    <button v-if="student.face_profile?.is_active"
                                            @click="resync(student.id)"
                                            class="btn btn-xs btn-outline btn-warning gap-1 font-bold">
                                        <RefreshCw class="w-3 h-3" /> Resync
                                    </button>
                                    <span v-else class="text-xs opacity-30 italic">Tidak ada aksi</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!students || students.length === 0"
                     class="p-16 text-center opacity-40 italic">
                    Belum ada siswa terdaftar di kelas ini.
                </div>
            </div>

            <!-- Catatan -->
            <div class="p-4 border-t border-base-200 flex items-start gap-2 text-xs opacity-50">
                <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                <span>
                    Untuk mendaftarkan atau mengganti foto wajah siswa, gunakan menu
                    <strong>Kelola Wajah Siswa</strong> di panel admin.
                    Guru hanya dapat melakukan resync ulang jika data wajah sudah terdaftar.
                </span>
            </div>
        </div>
    </GuruLayout>
</template>
