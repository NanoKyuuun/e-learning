<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, ClipboardList, Download, ArrowLeft, Calendar, User } from 'lucide-vue-next';

defineProps({
    meeting: Object,
});
</script>

<template>
    <Head :title="meeting.title" />

    <SiswaLayout>
        <div class="mb-8">
            <Link :href="route('siswa.meetings.index', meeting.teaching_assignment_id)" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar Sesi
            </Link>
            
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
                    <div v-for="material in meeting.materials" :key="material.id" class="card bg-base-100 shadow-md border border-base-200 hover:border-primary/30 transition-all group">
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
                            <div class="card-actions justify-end mt-4 pt-4 border-t border-base-50">
                                <a v-if="material.file_url" :href="'/storage/' + material.file_url" target="_blank" class="btn btn-sm btn-ghost gap-2 text-primary hover:bg-primary/10">
                                    <Download class="w-4 h-4" /> Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.materials.length === 0" class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic">
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

                <div v-for="assignment in meeting.assignments" :key="assignment.id" class="card bg-base-100 shadow-md border-l-4 border-l-secondary border border-base-200">
                    <div class="card-body p-5">
                        <h3 class="font-bold text-base-content">{{ assignment.title }}</h3>
                        <p class="text-xs opacity-60 mt-1">Batas Waktu: <span class="font-bold">{{ assignment.due_at }}</span></p>
                        <div class="card-actions mt-4">
                            <Link :href="route('siswa.assignments.show', assignment.id)" class="btn btn-secondary btn-sm btn-block shadow-lg shadow-secondary/20">
                                Lihat Tugas
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.assignments.length === 0" class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic text-sm">
                    Tidak ada tugas untuk sesi ini.
                </div>
            </div>
        </div>
    </SiswaLayout>
</template>
