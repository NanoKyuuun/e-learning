<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Book, ClipboardList, Clock, ArrowRight, Award } from 'lucide-vue-next';

defineProps({
    stats: Object,
    pendingAssignments: Array,
});
</script>

<template>
    <Head title="Dashboard Siswa" />

    <SiswaLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Ruang Belajar</h1>
            <p class="text-base-content/60 italic">Halo, siap untuk melanjutkan pembelajaran hari ini?</p>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stats shadow bg-primary text-primary-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><Book class="w-8 h-8" /></div>
                    <div class="stat-title text-primary-content/70 font-bold uppercase text-[10px] tracking-widest">Mata Pelajaran</div>
                    <div class="stat-value">{{ stats.subjects_count }}</div>
                </div>
            </div>
            <div class="stats shadow bg-secondary text-secondary-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><ClipboardList class="w-8 h-8" /></div>
                    <div class="stat-title text-secondary-content/70 font-bold uppercase text-[10px] tracking-widest">Tugas Belum Selesai</div>
                    <div class="stat-value">{{ stats.pending_assignments }}</div>
                </div>
            </div>
            <div class="stats shadow bg-accent text-accent-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><Award class="w-8 h-8" /></div>
                    <div class="stat-title text-accent-content/70 font-bold uppercase text-[10px] tracking-widest">Progres Belajar</div>
                    <div class="stat-value">Aktif</div>
                </div>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sisi Kiri: Tugas Mendatang -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-3 text-base-content uppercase tracking-tight">
                        <div class="bg-secondary w-2 h-6 rounded-full"></div>
                        Tugas Mendatang
                    </h2>
                </div>

                <div v-if="pendingAssignments.length > 0" class="space-y-4">
                    <div v-for="task in pendingAssignments" :key="task.id" class="card bg-base-100 shadow-lg border border-base-200 hover:border-secondary/40 transition-all group">
                        <div class="card-body p-6 flex-row items-center gap-6">
                            <div class="bg-secondary/10 text-secondary p-4 rounded-2xl hidden md:block">
                                <Clock class="w-6 h-6" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg leading-tight group-hover:text-secondary transition-colors">{{ task.title }}</h3>
                                <p class="text-[10px] font-black opacity-40 uppercase mt-1">Deadline: {{ task.formatted_due_date }}</p>
                            </div>
                            <Link :href="route('siswa.assignments.show', task.id)" class="btn btn-secondary btn-sm shadow-md shadow-secondary/20 font-black uppercase text-[10px] tracking-widest text-white">
                                Kerjakan <ArrowRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="bg-base-100 p-16 text-center rounded-[3rem] border-2 border-dashed border-base-300 opacity-40 italic">
                    Hebat! Semua tugas sudah kamu kerjakan.
                </div>
            </div>

            <!-- Sisi Kanan: Akses Cepat -->
            <div class="lg:col-span-1 space-y-6">
                <h2 class="text-xl font-black flex items-center gap-3 text-base-content uppercase tracking-tight px-2">
                    <div class="bg-primary w-2 h-6 rounded-full"></div>
                    Akses Cepat
                </h2>
                <div class="grid grid-cols-1 gap-4">
                    <Link :href="route('siswa.subjects.index')" class="btn btn-outline btn-block h-auto py-4 justify-between group hover:bg-primary hover:text-white transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-primary/10 p-2 rounded-lg group-hover:bg-white/20"><Book class="w-5 h-5" /></div>
                            <div class="text-left">
                                <p class="font-black text-sm uppercase tracking-tight leading-none">Mata Pelajaran</p>
                                <p class="text-[10px] opacity-50 lowercase italic mt-1 font-bold">Buka daftar materi</p>
                            </div>
                        </div>
                        <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all" />
                    </Link>

                    <Link :href="route('siswa.grades.index')" class="btn btn-outline btn-block h-auto py-4 justify-between group hover:bg-accent hover:text-white transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-accent/10 p-2 rounded-lg group-hover:bg-white/20"><Award class="w-5 h-5" /></div>
                            <div class="text-left">
                                <p class="font-black text-sm uppercase tracking-tight leading-none">Hasil Belajar</p>
                                <p class="text-[10px] opacity-50 lowercase italic mt-1 font-bold">Lihat rekap nilai</p>
                            </div>
                        </div>
                        <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all" />
                    </Link>
                </div>
            </div>
        </div>
    </SiswaLayout>
</template>
