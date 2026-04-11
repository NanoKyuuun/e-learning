<script setup>
import KajurLayout from '../../Layouts/KajurLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { School, Book, UserPlus, GraduationCap, ArrowRight, UserCheck, Users } from 'lucide-vue-next';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Kajur Dashboard" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Dashboard Kajur</h1>
            <p class="text-base-content/60 italic text-sm">Selamat datang, Kepala Jurusan. Kelola operasional akademik di sini.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
            <div class="stats shadow border border-base-200">
                <div class="stat">
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Total Kelas</div>
                    <div class="stat-value text-primary">{{ stats.total_classes }}</div>
                    <div class="stat-desc text-[10px] font-bold">Rombel aktif</div>
                </div>
            </div>
            <div class="stats shadow border border-base-200">
                <div class="stat">
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Mata Pelajaran</div>
                    <div class="stat-value text-secondary">{{ stats.total_subjects }}</div>
                    <div class="stat-desc text-[10px] font-bold">Kurikulum aktif</div>
                </div>
            </div>
            <div class="stats shadow border border-base-200">
                <div class="stat">
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Guru</div>
                    <div class="stat-value text-accent">{{ stats.total_teachers }}</div>
                    <div class="stat-desc text-[10px] font-bold">Staff pengajar</div>
                </div>
            </div>
            <div class="stats shadow border border-base-200">
                <div class="stat">
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Siswa</div>
                    <div class="stat-value text-base-content">{{ stats.total_students }}</div>
                    <div class="stat-desc text-[10px] font-bold">Peserta didik</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-base-content">
            <!-- Action Cards -->
            <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary/30 transition-all group overflow-hidden">
                <div class="bg-primary h-1"></div>
                <div class="card-body p-8">
                    <div class="bg-primary/10 w-12 h-12 rounded-2xl flex items-center justify-center text-primary mb-2 group-hover:bg-primary group-hover:text-white transition-all">
                        <UserCheck class="w-6 h-6" />
                    </div>
                    <h2 class="card-title text-2xl font-black tracking-tight uppercase">Plotting Pengampu</h2>
                    <p class="text-base-content/60 italic text-sm">Tentukan guru mana yang mengajar mapel tertentu di setiap kelas untuk periode semester aktif.</p>
                    <div class="card-actions justify-end mt-8">
                        <Link :href="route('kajur.teaching-assignments.index')" class="btn btn-primary shadow-lg shadow-primary/20 font-black uppercase text-xs">
                            Mulai Plotting <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-secondary/30 transition-all group overflow-hidden">
                <div class="bg-secondary h-1"></div>
                <div class="card-body p-8">
                    <div class="bg-secondary/10 w-12 h-12 rounded-2xl flex items-center justify-center text-secondary mb-2 group-hover:bg-secondary group-hover:text-white transition-all">
                        <Users class="w-6 h-6" />
                    </div>
                    <h2 class="card-title text-2xl font-black tracking-tight uppercase">Anggota Kelas</h2>
                    <p class="text-base-content/60 italic text-sm">Kelola daftar siswa di setiap rombongan belajar agar mereka dapat mengakses materi & tugas.</p>
                    <div class="card-actions justify-end mt-8">
                        <Link :href="route('kajur.class-groups.index')" class="btn btn-secondary shadow-lg shadow-secondary/20 font-black uppercase text-xs text-white">
                            Kelola Siswa <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcut Links -->
        <div class="mt-12 bg-base-100 p-8 rounded-[2.5rem] border border-base-200 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] opacity-30 mb-8 text-center">Infrastruktur Kurikulum & SDM</h3>
            <div class="flex flex-wrap justify-center gap-6">
                <Link :href="route('kajur.subjects.index')" class="flex items-center gap-2 group">
                    <div class="bg-base-200 p-3 rounded-xl group-hover:bg-primary group-hover:text-white transition-all shadow-sm"><Book class="w-5 h-5" /></div>
                    <span class="font-bold text-sm uppercase tracking-tight group-hover:text-primary transition-colors">Mata Pelajaran</span>
                </Link>
                <Link :href="route('kajur.teachers.index')" class="flex items-center gap-2 group">
                    <div class="bg-base-200 p-3 rounded-xl group-hover:bg-primary group-hover:text-white transition-all shadow-sm"><GraduationCap class="w-5 h-5" /></div>
                    <span class="font-bold text-sm uppercase tracking-tight group-hover:text-primary transition-colors">Data Guru</span>
                </Link>
                <Link :href="route('kajur.students.index')" class="flex items-center gap-2 group">
                    <div class="bg-base-200 p-3 rounded-xl group-hover:bg-primary group-hover:text-white transition-all shadow-sm"><School class="w-5 h-5" /></div>
                    <span class="font-bold text-sm uppercase tracking-tight group-hover:text-primary transition-colors">Data Siswa</span>
                </Link>
            </div>
        </div>
    </KajurLayout>
</template>
