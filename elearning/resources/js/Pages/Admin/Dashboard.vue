<script setup>
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, ShieldCheck, Settings, ArrowRight, Building2, Calendar, School, BookOpen, GraduationCap } from 'lucide-vue-next';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">ADMIN SYSTEM</h1>
            <p class="text-base-content/60 italic">Pusat kendali konfigurasi dan akses sistem secara global.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-primary opacity-30"><GraduationCap class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Total Guru</div>
                    <div class="stat-value text-primary">{{ stats.total_teachers }}</div>
                    <div class="stat-desc text-[10px] font-bold">Tenaga pendidik</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-secondary opacity-30"><Users class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Total Siswa</div>
                    <div class="stat-value text-secondary">{{ stats.total_students }}</div>
                    <div class="stat-desc text-[10px] font-bold">Peserta didik aktif</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-accent opacity-30"><School class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Total Kelas</div>
                    <div class="stat-value text-accent">{{ stats.total_classes }}</div>
                    <div class="stat-desc text-[10px] font-bold">Rombongan belajar</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-info opacity-30"><BookOpen class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Mata Pelajaran</div>
                    <div class="stat-value text-info">{{ stats.total_subjects }}</div>
                    <div class="stat-desc text-[10px] font-bold">Kurikulum aktif</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-neutral opacity-30"><Building2 class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Jurusan</div>
                    <div class="stat-value text-neutral">{{ stats.total_departments }}</div>
                    <div class="stat-desc text-[10px] font-bold">Program keahlian</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-info opacity-30"><Calendar class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">TA Aktif</div>
                    <div class="stat-value text-info text-2xl font-black">{{ stats.active_academic_year }}</div>
                    <div class="stat-desc text-[10px] font-bold uppercase">{{ stats.active_semester }}</div>
                </div>
            </div>

            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-success opacity-30"><Settings class="w-8 h-8" /></div>
                    <div class="stat-title text-[10px] font-black uppercase opacity-50 tracking-widest">Status Sistem</div>
                    <div class="stat-value text-success text-2xl font-black uppercase tracking-tighter">Online</div>
                    <div class="stat-desc text-[10px] font-bold">Server Berjalan Normal</div>
                </div>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-8 text-base-content">
            <div class="card bg-primary text-primary-content shadow-2xl shadow-primary/20">
                <div class="card-body p-8">
                    <h2 class="card-title text-3xl font-black uppercase tracking-tight">Kelola Pengguna</h2>
                    <p class="opacity-80 mt-2 italic">Lakukan pendaftaran akun untuk Guru, Siswa, dan Staf. Anda juga bisa me-reset password akun yang bermasalah.</p>
                    <div class="card-actions justify-end mt-8">
                        <Link :href="route('admin.users.index')" class="btn btn-secondary shadow-lg shadow-secondary/20 font-black uppercase px-8">
                            Manajemen User <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div class="bg-base-200 h-1"></div>
                <div class="card-body p-8">
                    <h2 class="card-title text-3xl font-black uppercase tracking-tight">Akademik Global</h2>
                    <p class="opacity-60 mt-2 italic">Atur data master mata pelajaran, kelas, dan plotting pengampu yang akan digunakan oleh seluruh sistem.</p>
                    <div class="card-actions justify-end mt-8 gap-2">
                        <Link :href="route('admin.subjects.index')" class="btn btn-ghost border-base-300 font-bold text-xs uppercase">Mapel</Link>
                        <Link :href="route('admin.class-groups.index')" class="btn btn-ghost border-base-300 font-bold text-xs uppercase">Kelas</Link>
                        <Link :href="route('admin.teaching-assignments.index')" class="btn btn-ghost border-base-300 font-bold text-xs uppercase">Plotting</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
