<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Presentation, Inbox, ArrowRight, Clock, User, CheckCircle } from 'lucide-vue-next';

defineProps({
    stats: Object,
    recentSubmissions: Array,
});
</script>

<template>
    <Head title="Dashboard Guru" />

    <GuruLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Ringkasan Aktivitas</h1>
            <p class="text-base-content/60 italic">Pantau progres pengajaran Anda secara real-time.</p>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stats shadow bg-primary text-primary-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><BookOpen class="w-8 h-8" /></div>
                    <div class="stat-title text-primary-content/70 font-bold uppercase text-[10px] tracking-widest">Mapel Pengampu</div>
                    <div class="stat-value">{{ stats.assignments_count }}</div>
                </div>
            </div>
            <div class="stats shadow bg-secondary text-secondary-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><Presentation class="w-8 h-8" /></div>
                    <div class="stat-title text-secondary-content/70 font-bold uppercase text-[10px] tracking-widest">Sesi Pertemuan</div>
                    <div class="stat-value">{{ stats.meetings_count }}</div>
                </div>
            </div>
            <div class="stats shadow bg-accent text-accent-content">
                <div class="stat">
                    <div class="stat-figure opacity-30"><Inbox class="w-8 h-8" /></div>
                    <div class="stat-title text-accent-content/70 font-bold uppercase text-[10px] tracking-widest">Submission Baru</div>
                    <div class="stat-value">{{ recentSubmissions.filter(s => !s.grade).length }}</div>
                </div>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sisi Kiri: Submission Terbaru -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-3 text-base-content uppercase tracking-tight">
                        <div class="bg-primary w-2 h-6 rounded-full"></div>
                        Submission Terbaru
                    </h2>
                    <Link :href="route('guru.assignments.all-submissions')" class="btn btn-ghost btn-xs text-primary font-bold">Lihat Semua</Link>
                </div>

                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="overflow-x-auto text-base-content">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-base-200/50 text-[10px] uppercase font-black opacity-40">
                                    <th>Siswa</th>
                                    <th>Tugas</th>
                                    <th>Waktu</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-medium">
                                <tr v-for="sub in recentSubmissions" :key="sub.id" class="hover">
                                    <td>{{ sub.student.user.full_name }}</td>
                                    <td class="font-bold">{{ sub.assignment.title }}</td>
                                    <td class="opacity-50 font-mono">{{ sub.submitted_at }}</td>
                                    <td class="text-center">
                                        <div v-if="sub.grade" class="badge badge-success badge-xs font-black">DINILAI</div>
                                        <div v-else class="badge badge-warning badge-xs font-black">PENDING</div>
                                    </td>
                                </tr>
                                <tr v-if="recentSubmissions.length === 0">
                                    <td colspan="4" class="text-center py-12 opacity-30 italic">Belum ada pengumpulan terbaru.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Pintasan Cepat -->
            <div class="lg:col-span-1 space-y-6">
                <h2 class="text-xl font-black flex items-center gap-3 text-base-content uppercase tracking-tight px-2">
                    <div class="bg-secondary w-2 h-6 rounded-full"></div>
                    Akses Cepat
                </h2>
                <div class="grid grid-cols-1 gap-4">
                    <Link :href="route('guru.courses.index')" class="btn btn-outline btn-block h-auto py-4 justify-between group hover:bg-primary hover:text-white transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-primary/10 p-2 rounded-lg group-hover:bg-white/20"><BookOpen class="w-5 h-5" /></div>
                            <div class="text-left">
                                <p class="font-black text-sm uppercase tracking-tight leading-none">Mulai Mengajar</p>
                                <p class="text-[10px] opacity-50 lowercase italic mt-1 font-bold">Buka daftar pengampu</p>
                            </div>
                        </div>
                        <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all" />
                    </Link>

                    <Link :href="route('guru.assignments.grading')" class="btn btn-outline btn-block h-auto py-4 justify-between group hover:bg-secondary hover:text-white transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-secondary/10 p-2 rounded-lg group-hover:bg-white/20"><CheckCircle class="w-5 h-5" /></div>
                            <div class="text-left">
                                <p class="font-black text-sm uppercase tracking-tight leading-none">Pusat Penilaian</p>
                                <p class="text-[10px] opacity-50 lowercase italic mt-1 font-bold">Koreksi tugas siswa</p>
                            </div>
                        </div>
                        <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all" />
                    </Link>
                </div>
            </div>
        </div>
    </GuruLayout>
</template>
