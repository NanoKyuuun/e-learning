<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Presentation, FileText, ClipboardList, ChevronRight, ArrowLeft } from 'lucide-vue-next';

defineProps({
    teachingAssignment: Object,
    meetings: Array,
});
</script>

<template>
    <Head :title="'Pertemuan - ' + teachingAssignment.subject.name" />

    <SiswaLayout>
        <div class="mb-8">
            <Link :href="route('siswa.dashboard')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Dashboard
            </Link>
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight">{{ teachingAssignment.subject.name }}</h1>
                <p class="text-base-content/60 font-bold uppercase text-xs tracking-widest mt-1">
                    Guru: <span class="text-primary">{{ teachingAssignment.teacher.user.full_name }}</span>
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <div v-for="meeting in meetings" :key="meeting.id" class="card bg-base-100 shadow-md border border-base-200 overflow-hidden hover:border-primary/30 transition-all group">
                <div class="flex flex-col md:flex-row">
                    <div class="bg-base-200 p-6 flex flex-col items-center justify-center min-w-[100px] group-hover:bg-primary/5 transition-colors border-r border-base-100">
                        <span class="text-xs opacity-60 font-black uppercase tracking-tighter">Sesi</span>
                        <span class="text-4xl font-black text-base-content">{{ meeting.meeting_number }}</span>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold mb-1 group-hover:text-primary transition-colors">{{ meeting.title }}</h2>
                                <p class="text-base-content/70 text-sm line-clamp-2 italic">{{ meeting.topic || 'Buka untuk melihat detail pertemuan.' }}</p>
                            </div>
                            <div class="flex gap-6 shrink-0">
                                <div class="flex items-center gap-2 text-xs font-bold opacity-40">
                                    <FileText class="w-4 h-4 text-primary" /> MATERI
                                </div>
                                <div class="flex items-center gap-2 text-xs font-bold opacity-40">
                                    <ClipboardList class="w-4 h-4 text-secondary" /> TUGAS
                                </div>
                            </div>
                            <div class="shrink-0">
                                <Link :href="route('siswa.meetings.show', meeting.id)" class="btn btn-primary btn-sm md:btn-md shadow-lg shadow-primary/20">
                                    Buka Sesi Belajar <ChevronRight class="w-4 h-4 ml-1" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="meetings.length === 0" class="bg-base-100 p-16 text-center rounded-3xl shadow-sm border border-base-200 border-dashed">
                <div class="bg-base-200 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Presentation class="w-8 h-8 opacity-20" />
                </div>
                <p class="opacity-50 italic text-sm font-medium">Belum ada pertemuan yang dipublikasikan oleh guru.</p>
            </div>
        </div>
    </SiswaLayout>
</template>
