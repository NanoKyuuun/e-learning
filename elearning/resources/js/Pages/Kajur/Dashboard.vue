<script setup>
import KajurLayout from '../../Layouts/KajurLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { MonitorCheck, FileText, ArrowRight, Megaphone } from 'lucide-vue-next';

defineProps({
    stats: Object,
    managedDepartments: Array,
    error: String,
});
</script>

<template>
    <Head title="Kajur Dashboard" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Dashboard Kajur</h1>
            <p class="text-base-content/60 italic text-sm">Selamat datang, Kepala Jurusan. Pantau progres akademik jurusan Anda di sini.</p>
        </div>

        <div v-if="error" class="alert alert-warning mb-6 border-none bg-warning/10 text-warning-content/90">
            <span>{{ error }}</span>
        </div>

        <div v-else-if="managedDepartments?.length" class="mb-8 rounded-3xl border border-base-200 bg-base-100 p-6 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">Jurusan Yang Dikelola</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <span
                    v-for="department in managedDepartments"
                    :key="department.id"
                    class="badge badge-primary badge-lg font-bold"
                >
                    {{ department.name }} ({{ department.code }})
                </span>
            </div>
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
                        <MonitorCheck class="w-6 h-6" />
                    </div>
                    <h2 class="card-title text-2xl font-black tracking-tight uppercase">Monitoring Progres</h2>
                    <p class="text-base-content/60 italic text-sm">Pantau keterlaksanaan pertemuan dan materi di setiap kelas yang ada di bawah jurusan Anda.</p>
                    <div class="card-actions justify-end mt-8">
                        <Link :href="route('kajur.monitoring.progress')" class="btn btn-primary shadow-lg shadow-primary/20 font-black uppercase text-xs">
                            Lihat Progres <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-secondary/30 transition-all group overflow-hidden">
                <div class="bg-secondary h-1"></div>
                <div class="card-body p-8">
                    <div class="bg-secondary/10 w-12 h-12 rounded-2xl flex items-center justify-center text-secondary mb-2 group-hover:bg-secondary group-hover:text-white transition-all">
                        <FileText class="w-6 h-6" />
                    </div>
                    <h2 class="card-title text-2xl font-black tracking-tight uppercase">Rekap Nilai</h2>
                    <p class="text-base-content/60 italic text-sm">Lihat dan unduh rekapitulasi nilai tugas siswa untuk seluruh mata pelajaran di jurusan Anda.</p>
                    <div class="card-actions justify-end mt-8">
                        <Link :href="route('kajur.monitoring.grades')" class="btn btn-secondary shadow-lg shadow-secondary/20 font-black uppercase text-xs text-white">
                            Lihat Nilai <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcut Links -->
        <div class="mt-12 bg-base-100 p-8 rounded-[2.5rem] border border-base-200 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] opacity-30 mb-8 text-center">Informasi & Pengumuman</h3>
            <div class="flex flex-wrap justify-center gap-6">
                <Link :href="route('kajur.announcements.index')" class="flex items-center gap-2 group">
                    <div class="bg-base-200 p-3 rounded-xl group-hover:bg-primary group-hover:text-white transition-all shadow-sm"><Megaphone class="w-5 h-5" /></div>
                    <span class="font-bold text-sm uppercase tracking-tight group-hover:text-primary transition-colors">Kelola Pengumuman</span>
                </Link>
            </div>
        </div>
    </KajurLayout>
</template>
