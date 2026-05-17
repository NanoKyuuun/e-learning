<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, XCircle, Clock, Users, Calendar, Download } from 'lucide-vue-next';

const props = defineProps({
    teachingAssignment: Object,
    meetings: Array,
    recapData: Array,
});

const getStatusClass = (status) => {
    switch (status) {
        case 'present': return 'text-success';
        case 'late':    return 'text-warning';
        case 'manual':  return 'text-info';
        case 'absent':  return 'text-error opacity-30';
        default:        return 'text-base-content/20';
    }
};

const getStatusIcon = (status) => {
    if (['present', 'late', 'manual'].includes(status)) return CheckCircle;
    return XCircle;
};
</script>

<template>
    <Head :title="'Rekap Kehadiran - ' + teachingAssignment.subject.name" />

    <GuruLayout>
        <div class="mb-8">
            <Link :href="route('guru.meetings.index', teachingAssignment.id)" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Pertemuan
            </Link>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Rekap Kehadiran</h1>
                    <p class="text-base-content/60">{{ teachingAssignment.subject.name }} • {{ teachingAssignment.class_group.name }}</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline btn-sm gap-2">
                        <Download class="w-4 h-4" /> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-[10px] uppercase tracking-widest font-black">
                            <th class="sticky left-0 bg-base-100 z-20 border-r border-base-200">Siswa</th>
                            <th v-for="meeting in meetings" :key="meeting.id" class="text-center min-w-[60px]">
                                P{{ meeting.meeting_number }}
                                <div class="text-[8px] opacity-40 font-normal">{{ meeting.date }}</div>
                            </th>
                            <th class="text-center bg-base-200/30 border-l border-base-200">Hadir</th>
                            <th class="text-center bg-base-200/30">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in recapData" :key="student.student_id" class="hover">
                            <td class="sticky left-0 bg-base-100 z-10 border-r border-base-200">
                                <div class="font-bold text-xs truncate max-w-[150px]">{{ student.name }}</div>
                                <div class="text-[9px] opacity-50 font-mono">{{ student.student_number }}</div>
                            </td>
                            <td v-for="mStat in student.meetings" :key="mStat.meeting_id" class="text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <component :is="getStatusIcon(mStat.status)" :class="['w-4 h-4', getStatusClass(mStat.status)]" />
                                    <span v-if="mStat.check_in_at" class="text-[8px] opacity-50 mt-0.5">{{ mStat.check_in_at }}</span>
                                </div>
                            </td>
                            <td class="text-center font-black text-success bg-success/5 border-l border-base-200">
                                {{ student.summary.present }}
                            </td>
                            <td class="text-center font-black text-error bg-error/5">
                                {{ student.summary.absent }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="alert bg-base-100 border border-base-200 shadow-sm">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase opacity-40 tracking-widest">Legenda</span>
                    <div class="flex flex-wrap gap-4 mt-2">
                        <div class="flex items-center gap-1 text-[10px] font-bold">
                            <CheckCircle class="w-3 h-3 text-success" /> Hadir
                        </div>
                        <div class="flex items-center gap-1 text-[10px] font-bold">
                            <CheckCircle class="w-3 h-3 text-warning" /> Terlambat
                        </div>
                        <div class="flex items-center gap-1 text-[10px] font-bold">
                            <CheckCircle class="w-3 h-3 text-info" /> Manual
                        </div>
                        <div class="flex items-center gap-1 text-[10px] font-bold">
                            <XCircle class="w-3 h-3 text-error opacity-30" /> Alpa
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-2 alert bg-info/5 border-none">
                <div class="text-xs opacity-70 italic">
                    * P1, P2, dst adalah nomor pertemuan. Arahkan kursor ke ikon untuk melihat jam absensi siswa.
                </div>
            </div>
        </div>
    </GuruLayout>
</template>

<style scoped>
.table-sm th, .table-sm td {
    padding: 0.5rem;
}
</style>
