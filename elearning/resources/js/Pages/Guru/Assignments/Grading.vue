<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ClipboardList, BookOpen, School, ChevronRight, CheckCircle, Clock } from 'lucide-vue-next';

defineProps({
    assignments: Array,
});
</script>

<template>
    <Head title="Pusat Penilaian Tugas" />

    <GuruLayout>
        <div class="mb-8 text-base-content">
            <h1 class="text-3xl font-black tracking-tight uppercase text-secondary">Pusat Penilaian</h1>
            <p class="text-base-content/60 font-medium mt-1">Pantau progres koreksi dan penilaian tugas siswa Anda.</p>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div v-for="item in assignments" :key="item.id" class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden hover:border-secondary/40 transition-all group">
                <div class="flex flex-col md:flex-row items-stretch">
                    <!-- Sisi Kiri: Info Progres -->
                    <div class="bg-secondary/5 p-8 flex flex-col items-center justify-center min-w-[200px] border-r border-base-100">
                        <div class="radial-progress text-secondary font-black text-xl" 
                             :style="`--value:${Math.round((item.graded_submissions / (item.total_submissions || 1)) * 100)}; --size:6rem; --thickness: 8px;`" 
                             role="progressbar">
                            {{ Math.round((item.graded_submissions / (item.total_submissions || 1)) * 100) }}%
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-40 mt-4 leading-none">Ter-koreksi</p>
                        <p class="text-sm font-bold text-secondary mt-1">{{ item.graded_submissions }} / {{ item.total_submissions }}</p>
                    </div>

                    <!-- Sisi Kanan: Detail -->
                    <div class="card-body p-8 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <div class="badge badge-secondary font-bold px-3 uppercase text-[10px]">{{ item.meeting.teaching_assignment.class_group.name }}</div>
                            <div class="text-[10px] opacity-40 font-mono tracking-tighter uppercase font-black">{{ item.meeting.teaching_assignment.subject.name }}</div>
                        </div>

                        <h2 class="card-title text-2xl font-black tracking-tight text-base-content group-hover:text-secondary transition-colors uppercase leading-tight">
                            {{ item.title }}
                        </h2>
                        
                        <div class="flex flex-wrap items-center gap-6 mt-6">
                            <div class="flex items-center gap-2 text-xs font-bold opacity-50 uppercase">
                                <Clock class="w-4 h-4 text-error" /> Deadline: {{ item.formatted_due_date }}
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold opacity-50 uppercase">
                                <ClipboardList class="w-4 h-4 text-primary" /> Sesi: {{ item.meeting.meeting_number }}
                            </div>
                        </div>

                        <div class="card-actions justify-end mt-8 pt-4 border-t border-base-50">
                            <Link :href="route('guru.assignments.submissions', item.id)" class="btn btn-secondary shadow-lg shadow-secondary/20 gap-2 font-black uppercase tracking-widest text-white px-8">
                                Mulai Menilai <ChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="assignments.length === 0" class="bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-200 shadow-inner">
                <div class="bg-base-200 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <GraduationCap class="w-10 h-10 opacity-20" />
                </div>
                <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Belum Ada Tugas Dibuat</h3>
                <p class="opacity-40 text-sm italic mt-2">Buat tugas di dalam sesi pertemuan untuk mulai melakukan penilaian.</p>
            </div>
        </div>
    </GuruLayout>
</template>
