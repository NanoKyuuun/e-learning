<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ClipboardList, ArrowLeft, User, CheckCircle, Clock, Download, ExternalLink, MessageSquare, FileText, X } from 'lucide-vue-next';
import { ref } from 'vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';

const props = defineProps({
    assignment: Object,
    submissions: Array,
});

const isGradeModalOpen = ref(false);
const selectedSubmission = ref(null);

const gradeForm = useForm({
    score: '',
    feedback: '',
});

const openGradeModal = (submission) => {
    selectedSubmission.value = submission;
    gradeForm.score = submission.grade?.score || '';
    gradeForm.feedback = submission.grade?.feedback || '';
    isGradeModalOpen.value = true;
};

const submitGrade = () => {
    gradeForm.post(route('guru.submissions.grade', selectedSubmission.value.id), {
        onSuccess: () => {
            isGradeModalOpen.value = false;
            selectedSubmission.value = null;
            gradeForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="'Submission: ' + assignment.title" />

    <GuruLayout>
        <div class="mb-8 text-base-content">
            <Link :href="route('guru.meetings.show', assignment.meeting_id)" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Detail Sesi
            </Link>
            
            <div class="bg-base-100 p-6 rounded-3xl border border-base-200 shadow-sm flex flex-col md:flex-row justify-between gap-6 overflow-hidden relative">
                <div class="absolute top-0 right-0 p-6 opacity-5 rotate-12">
                    <ClipboardList class="w-32 h-32" />
                </div>
                
                <div class="relative z-10">
                    <h1 class="text-2xl font-black tracking-tight uppercase">{{ assignment.title }}</h1>
                    <div class="flex flex-wrap items-center gap-4 mt-2">
                        <span class="badge badge-primary font-bold px-3 uppercase text-[10px]">{{ assignment.meeting.teaching_assignment.class_group.name }}</span>
                        <span class="text-xs font-bold opacity-50 flex items-center gap-1 uppercase tracking-tighter">
                            <Clock class="w-3 h-3" /> Deadline: {{ assignment.formatted_due_date }}
                        </span>
                    </div>
                </div>
                <div class="stats bg-base-200 rounded-2xl px-6 py-2 self-start relative z-10 border border-base-300 shadow-inner">
                    <div class="stat p-0">
                        <div class="stat-title text-[10px] uppercase font-black opacity-40 leading-none mb-1">Sudah Kumpul</div>
                        <div class="stat-value text-3xl font-black text-primary">{{ submissions.filter(s => s.submitted_at).length }}<span class="text-sm opacity-20">/{{ submissions.length }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold text-sm">
            <CheckCircle class="w-5 h-5" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70 text-[11px] uppercase tracking-widest font-black">
                            <th class="w-16 text-center">No</th>
                            <th>Info Siswa</th>
                            <th>Status & Waktu</th>
                            <th>Lampiran & Jawaban</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-base-content/80 text-sm">
                        <tr v-for="(sub, index) in submissions" :key="sub.id" class="hover group">
                            <td class="text-center font-mono opacity-40 text-xs">{{ index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-xl w-10 font-bold border border-primary/20">
                                            <span>{{ sub.student.user.full_name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-base-content leading-tight group-hover:text-primary transition-colors">{{ sub.student.user.full_name }}</div>
                                        <div class="text-[10px] opacity-50 font-mono tracking-tighter uppercase">{{ sub.student.student_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="sub.submitted_at" class="flex flex-col gap-1">
                                    <div :class="['badge badge-xs font-black border-none px-2', sub.status === 'submitted' ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">
                                        {{ sub.status.toUpperCase() }}
                                    </div>
                                    <span class="text-[10px] opacity-50 font-mono italic">{{ sub.submitted_at }}</span>
                                </div>
                                <span v-else class="text-xs italic opacity-30">Belum mengumpulkan</span>
                            </td>
                            <td>
                                <div v-if="sub.submitted_at" class="flex flex-col gap-2">
                                    <a v-if="sub.file_url" :href="'/storage/' + sub.file_url" target="_blank" class="btn btn-xs btn-outline btn-primary gap-1 lowercase font-mono">
                                        <Download class="w-3 h-3" /> file_tugas.bin
                                    </a>
                                    <div v-if="sub.submission_text" class="text-[10px] opacity-60 line-clamp-1 italic max-w-[200px]">
                                        "{{ sub.submission_text }}"
                                    </div>
                                </div>
                                <span v-else class="text-[10px] opacity-20">-</span>
                            </td>
                            <td class="text-center">
                                <div v-if="sub.grade" class="flex flex-col items-center justify-center">
                                    <span class="text-2xl font-black text-primary leading-none">{{ Math.round(sub.grade.score) }}</span>
                                    <span class="text-[9px] opacity-30 font-black uppercase tracking-tighter">per {{ assignment.max_score }}</span>
                                </div>
                                <div v-else-if="sub.submitted_at" class="badge badge-ghost badge-sm opacity-40 italic">Belum Dinilai</div>
                                <span v-else class="text-xs opacity-20">-</span>
                            </td>
                            <td class="text-center">
                                <button 
                                    @click="openGradeModal(sub)" 
                                    class="btn btn-sm shadow-sm gap-2"
                                    :class="sub.grade ? 'btn-ghost text-primary hover:bg-primary/5' : 'btn-primary'"
                                    :disabled="!sub.submitted_at"
                                >
                                    <MessageSquare class="w-4 h-4" /> {{ sub.grade ? 'Ubah Nilai' : 'Beri Nilai' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="submissions.length === 0">
                            <td colspan="6" class="text-center py-20 opacity-50 italic">
                                Belum ada siswa yang mengumpulkan tugas ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grade Submission Modal -->
        <input type="checkbox" id="grade-modal" class="modal-toggle" :checked="isGradeModalOpen" @change="isGradeModalOpen = $event.target.checked" />
        <div class="modal modal-bottom sm:modal-middle">
            <div v-if="selectedSubmission" class="modal-box rounded-3xl max-w-lg border border-base-200 shadow-2xl">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="font-black text-2xl tracking-tight text-primary uppercase">Penilaian Tugas</h3>
                        <p class="text-xs font-bold opacity-50 uppercase tracking-widest mt-1">{{ selectedSubmission.student.user.full_name }}</p>
                    </div>
                    <button @click="isGradeModalOpen = false" class="btn btn-ghost btn-sm btn-circle"><X class="w-5 h-5" /></button>
                </div>

                <!-- Review Jawaban Siswa -->
                <div class="bg-base-200/50 p-5 rounded-2xl mb-8 border border-base-200">
                    <h4 class="text-[10px] font-black uppercase opacity-40 tracking-[0.2em] mb-3">Tinjau Jawaban</h4>
                    <div v-if="selectedSubmission.submission_text" class="mb-4">
                        <p class="text-xs font-bold opacity-50 mb-1">Teks Jawaban:</p>
                        <p class="text-sm bg-base-100 p-4 rounded-xl italic border border-base-200">{{ selectedSubmission.submission_text }}</p>
                    </div>
                    <div v-if="selectedSubmission.file_url">
                        <a :href="'/storage/' + selectedSubmission.file_url" target="_blank" class="btn btn-block btn-outline btn-primary gap-2 rounded-xl">
                            <Download class="w-4 h-4" /> Download File Jawaban
                        </a>
                    </div>
                </div>
                
                <form @submit.prevent="submitGrade" class="space-y-6">
                    <TextInput 
                        label="Skor / Nilai (0 - 100)" 
                        type="number" 
                        v-model="gradeForm.score" 
                        :error="gradeForm.errors.score" 
                        required 
                        class="text-2xl font-black"
                    />
                    <TextareaInput 
                        label="Feedback / Catatan Guru" 
                        v-model="gradeForm.feedback" 
                        :error="gradeForm.errors.feedback" 
                        placeholder="Beri masukan membangun untuk siswa..."
                        :rows="4"
                    />

                    <div class="modal-action gap-3">
                        <button type="button" @click="isGradeModalOpen = false" class="btn btn-ghost flex-1">Batal</button>
                        <button type="submit" class="btn btn-primary flex-1 shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-white" :disabled="gradeForm.processing">
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
            <label class="modal-backdrop" @click="isGradeModalOpen = false">Close</label>
        </div>
    </GuruLayout>
</template>
