<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Inbox, ExternalLink, User, BookOpen, Clock, CheckCircle, MessageSquare } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';

defineProps({
    submissions: Object,
});
</script>

<template>
    <Head title="Semua Submission Tugas" />

    <GuruLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Semua Submission</h1>
            <p class="text-base-content/60 font-medium mt-1">Daftar terbaru pengumpulan tugas dari seluruh kelas.</p>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-base-content/80">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70 text-[11px] uppercase tracking-widest font-black">
                            <th class="w-16 text-center">No</th>
                            <th>Siswa</th>
                            <th>Tugas & Mapel</th>
                            <th>Waktu Kumpul</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(sub, index) in submissions.data" :key="sub.id" class="hover group">
                            <td class="text-center font-mono opacity-40 text-xs">
                                {{ (submissions.current_page - 1) * submissions.per_page + index + 1 }}
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-xl w-10 font-bold">
                                            <span>{{ sub.student.user.full_name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">{{ sub.student.user.full_name }}</div>
                                        <div class="text-[10px] opacity-50 uppercase">{{ sub.student.student_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm text-primary line-clamp-1">{{ sub.assignment.title }}</span>
                                    <span class="text-[10px] opacity-50 uppercase font-black">{{ sub.assignment.meeting.teaching_assignment.subject.name }} • {{ sub.assignment.meeting.teaching_assignment.class_group.name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span :class="['text-xs font-bold', sub.status === 'late' ? 'text-error' : 'text-success']">
                                        {{ sub.status === 'late' ? 'TERLAMBAT' : 'TEPAT WAKTU' }}
                                    </span>
                                    <span class="text-[10px] opacity-40 italic">{{ sub.submitted_at }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div v-if="sub.grade" class="flex flex-col items-center">
                                    <span class="text-xl font-black text-primary leading-none">{{ Math.round(sub.grade.score) }}</span>
                                </div>
                                <span v-else class="badge badge-ghost badge-xs opacity-40">BELUM</span>
                            </td>
                            <td class="text-center">
                                <Link :href="route('guru.assignments.submissions', sub.assignment_id)" class="btn btn-sm btn-ghost hover:bg-primary/10 hover:text-primary gap-2">
                                    <MessageSquare class="w-4 h-4" /> Detail & Nilai
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="submissions.data.length === 0">
                            <td colspan="6" class="text-center py-20 opacity-50 italic">Belum ada pengumpulan tugas terbaru.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="submissions.links" />
        </div>
    </GuruLayout>
</template>
