<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, Book, CheckCircle, Clock, AlertCircle, ChevronRight, MessageSquare } from 'lucide-vue-next';

const props = defineProps({
    subjects: Array,
    classGroup: Object,
});

// Helper untuk menghitung rata-rata nilai per mata pelajaran
const calculateAverage = (meetings) => {
    let totalScore = 0;
    let count = 0;
    
    meetings.forEach(meeting => {
        meeting.assignments.forEach(assignment => {
            if (assignment.submissions.length > 0 && assignment.submissions[0].grade) {
                totalScore += parseFloat(assignment.submissions[0].grade.score);
                count++;
            }
        });
    });
    
    return count > 0 ? (totalScore / count).toFixed(1) : '-';
};
</script>

<template>
    <Head title="Rekap Nilai & Feedback" />

    <SiswaLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Rekap Nilai & Feedback</h1>
            <p class="text-base-content/60 font-medium mt-1">Pantau pencapaian akademik kamu di kelas <span class="text-primary font-bold">{{ classGroup?.class_group.name }}</span>.</p>
        </div>

        <div class="space-y-8">
            <div v-for="subject in subjects" :key="subject.id" class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div class="bg-primary/5 p-6 border-b border-base-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-primary text-primary-content p-3 rounded-2xl shadow-lg shadow-primary/20">
                            <Book class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-base-content uppercase tracking-tight">{{ subject.subject.name }}</h2>
                            <p class="text-xs opacity-50 font-bold uppercase tracking-widest">{{ subject.subject.code }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-base-100 px-4 py-2 rounded-xl border border-base-200 shadow-sm">
                        <span class="text-[10px] font-black opacity-40 uppercase tracking-widest">Rata-rata</span>
                        <span class="text-2xl font-black text-primary">{{ calculateAverage(subject.meetings) }}</span>
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-[0.2em] opacity-40">
                                <th class="pl-8">Tugas / Pertemuan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Nilai</th>
                                <th>Feedback Guru</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-medium">
                            <template v-for="meeting in subject.meetings" :key="meeting.id">
                                <tr v-for="assignment in meeting.assignments" :key="assignment.id" class="hover">
                                    <td class="pl-8 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-base-content font-bold">{{ assignment.title }}</span>
                                            <span class="text-[10px] opacity-50">Sesi {{ meeting.meeting_number }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div v-if="assignment.submissions.length > 0" class="badge badge-success bg-success/10 text-success border-none font-black text-[10px]">
                                            TERKIRIM
                                        </div>
                                        <div v-else class="badge badge-warning bg-warning/10 text-warning border-none font-black text-[10px]">
                                            BELUM
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div v-if="assignment.submissions.length > 0 && assignment.submissions[0].grade" class="flex flex-col items-center">
                                            <span class="text-xl font-black text-primary">{{ Math.round(assignment.submissions[0].grade.score) }}</span>
                                            <span class="text-[9px] opacity-30 font-black">/ {{ assignment.max_score }}</span>
                                        </div>
                                        <span v-else class="opacity-20">-</span>
                                    </td>
                                    <td>
                                        <div v-if="assignment.submissions.length > 0 && assignment.submissions[0].grade?.feedback" class="flex items-start gap-2 max-w-xs">
                                            <MessageSquare class="w-4 h-4 mt-0.5 opacity-30 shrink-0" />
                                            <p class="text-xs italic opacity-70 leading-relaxed line-clamp-2">"{{ assignment.submissions[0].grade.feedback }}"</p>
                                        </div>
                                        <span v-else class="opacity-20 italic text-xs">Tidak ada catatan.</span>
                                    </td>
                                    <td class="text-center pr-8">
                                        <Link :href="route('siswa.assignments.show', assignment.id)" class="btn btn-ghost btn-sm btn-circle hover:bg-primary/10 hover:text-primary transition-colors">
                                            <ChevronRight class="w-5 h-5" />
                                        </Link>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="subject.meetings.every(m => m.assignments.length === 0)">
                                <td colspan="5" class="text-center py-8 opacity-30 italic text-xs">Belum ada tugas untuk mata pelajaran ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="subjects.length === 0" class="bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-300">
                <div class="bg-base-200 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Award class="w-10 h-10 opacity-20" />
                </div>
                <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Belum Ada Data Nilai</h3>
                <p class="opacity-40 text-sm italic mt-2">Data nilai akan muncul di sini setelah kamu mengerjakan tugas.</p>
            </div>
        </div>
    </SiswaLayout>
</template>
