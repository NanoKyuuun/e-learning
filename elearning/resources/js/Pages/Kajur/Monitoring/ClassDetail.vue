<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, GraduationCap, LayoutDashboard, Presentation } from 'lucide-vue-next';

defineProps({
    classGroup: Object,
    assignments: Array,
});
</script>

<template>
    <Head :title="'Detail Progres - ' + classGroup.name" />

    <KajurLayout>
        <div class="mb-8">
            <Link :href="route('kajur.monitoring.progress')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali
            </Link>
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Detail Progres Kelas</h1>
            <p class="text-base-content/60 font-medium mt-1">{{ classGroup.name }} • {{ classGroup.academic_year.name }}</p>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-base-content/80">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70 text-[11px] uppercase tracking-widest font-black">
                            <th class="w-16">No</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th class="text-center">Total Pertemuan</th>
                            <th class="text-center">Status Kurikulum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in assignments" :key="item.id" class="hover group">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary/5 text-primary p-2 rounded-lg group-hover:bg-primary group-hover:text-primary-content transition-colors">
                                        <BookOpen class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-base-content">{{ item.subject.name }}</div>
                                        <div class="text-[10px] font-mono opacity-40 uppercase">{{ item.subject.code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <GraduationCap class="w-4 h-4 opacity-30" />
                                    <span class="font-medium text-sm">{{ item.teacher.user.full_name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-xl font-black text-primary">{{ item.meetings_count }}</span>
                                    <span class="text-[10px] uppercase font-bold opacity-30">Sesi</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div v-if="item.meetings_count >= 16" class="badge badge-success bg-success/10 text-success border-none font-bold text-[10px] px-3">SELESAI</div>
                                <div v-else-if="item.meetings_count > 0" class="badge badge-info bg-info/10 text-info border-none font-bold text-[10px] px-3">BERJALAN</div>
                                <div v-else class="badge badge-error bg-error/10 text-error border-none font-bold text-[10px] px-3">BELUM DIMULAI</div>
                            </td>
                        </tr>
                        <tr v-if="assignments.length === 0">
                            <td colspan="5" class="text-center py-16 opacity-40 italic">
                                Belum ada mata pelajaran yang di-plotting ke kelas ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </KajurLayout>
</template>
