<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Presentation, FileText, ClipboardList, ChevronRight,
    ArrowLeft, CheckCircle, Camera, AlertTriangle, Clock
} from 'lucide-vue-next';

const props = defineProps({
    teachingAssignment: Object,
    meetings:           Array,
});

// Status badge untuk setiap pertemuan (dari sudut pandang siswa)
const meetingBadge = (meeting) => {
    const status = meeting.status;
    if (status === 'active')    return { label: 'Absensi Terbuka', cls: 'badge-success animate-pulse' };
    if (status === 'published') return { label: 'Sedang Berlangsung', cls: 'badge-info' };
    if (status === 'draft')     return { label: 'Draft', cls: 'badge-ghost' };
    return { label: status, cls: 'badge-ghost' };
};

// Cek apakah meeting sedang aktif (absensi terbuka)
const isActive = (meeting) => meeting.status === 'active';
</script>

<template>
    <Head :title="'Pertemuan - ' + teachingAssignment.subject.name" />

    <SiswaLayout>
        <div class="mb-8">
            <Link :href="route('siswa.dashboard')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Dashboard
            </Link>
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight">
                    {{ teachingAssignment.subject.name }}
                </h1>
                <p class="text-base-content/60 font-bold uppercase text-xs tracking-widest mt-1">
                    Guru: <span class="text-primary">{{ teachingAssignment.teacher.user.full_name }}</span>
                    · Kelas: <span class="text-primary">{{ teachingAssignment.class_group?.name }}</span>
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <div v-for="meeting in meetings" :key="meeting.id"
                 :class="['card bg-base-100 shadow-md border overflow-hidden transition-all group',
                          isActive(meeting) ? 'border-success/40 shadow-success/10' : 'border-base-200 hover:border-primary/30']">
                <div class="flex flex-col md:flex-row">
                    <!-- Nomor sesi -->
                    <div :class="['p-6 flex flex-col items-center justify-center min-w-[100px] border-r transition-colors',
                                  isActive(meeting) ? 'bg-success/10 border-success/20' : 'bg-base-200 border-base-100 group-hover:bg-primary/5']">
                        <span class="text-xs opacity-60 font-black uppercase tracking-tighter">Sesi</span>
                        <span :class="['text-4xl font-black', isActive(meeting) ? 'text-success' : 'text-base-content']">
                            {{ meeting.meeting_number }}
                        </span>
                    </div>

                    <!-- Konten -->
                    <div class="p-6 flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-1">
                                <!-- Title + Badge Status -->
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h2 class="text-xl font-bold group-hover:text-primary transition-colors">
                                        {{ meeting.title }}
                                    </h2>
                                    <span :class="['badge badge-sm font-bold', meetingBadge(meeting).cls]">
                                        {{ meetingBadge(meeting).label }}
                                    </span>
                                </div>
                                <p class="text-base-content/70 text-sm line-clamp-2 italic">
                                    {{ meeting.topic || 'Buka untuk melihat detail pertemuan.' }}
                                </p>
                            </div>

                            <!-- Action area -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
                                <!-- Info materi & tugas -->
                                <div class="flex gap-4">
                                    <div class="flex items-center gap-1 text-xs font-bold opacity-40">
                                        <FileText class="w-4 h-4 text-primary" /> MATERI
                                    </div>
                                    <div class="flex items-center gap-1 text-xs font-bold opacity-40">
                                        <ClipboardList class="w-4 h-4 text-secondary" /> TUGAS
                                    </div>
                                </div>

                                <!-- Tombol Buka -->
                                <Link :href="route('siswa.meetings.show', meeting.id)"
                                      :class="['btn btn-sm md:btn-md gap-2 shadow-lg',
                                               isActive(meeting) ? 'btn-success shadow-success/20 text-white' : 'btn-primary shadow-primary/20']">
                                    <Camera v-if="isActive(meeting)" class="w-4 h-4" />
                                    {{ isActive(meeting) ? 'Absen Sekarang' : 'Buka Sesi' }}
                                    <ChevronRight class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="meetings.length === 0"
                 class="bg-base-100 p-16 text-center rounded-3xl shadow-sm border border-base-200 border-dashed">
                <div class="bg-base-200 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Presentation class="w-8 h-8 opacity-20" />
                </div>
                <p class="opacity-50 italic text-sm font-medium">Belum ada pertemuan yang dipublikasikan oleh guru.</p>
            </div>
        </div>
    </SiswaLayout>
</template>
