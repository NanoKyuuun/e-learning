<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ClipboardList, ArrowLeft, Download, Clock, CheckCircle, AlertCircle, FileUp, Send, FileText } from 'lucide-vue-next';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';
import { computed } from 'vue';

const props = defineProps({
    assignment: Object,
    submission: Object,
});

const flash = computed(() => usePage().props.flash);

const form = useForm({
    submission_text: props.submission?.submission_text || '',
    file: null,
    student_notes: props.submission?.student_notes || '',
});

const submit = () => {
    form.post(route('siswa.assignments.submit', props.assignment.id), {
        onSuccess: () => {
            form.reset('file');
        },
    });
};
</script>

<template>
    <Head :title="'Tugas: ' + assignment.title" />

    <SiswaLayout>
        <div class="mb-8">
            <Link :href="route('siswa.meetings.show', assignment.meeting_id)" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Pertemuan
            </Link>
            
            <div class="flex flex-col md:flex-row gap-8 text-base-content">
                <!-- Sisi Kiri: Detail Tugas -->
                <div class="flex-1 space-y-6">
                    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                        <div class="bg-secondary p-1"></div>
                        <div class="card-body p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h1 class="text-3xl font-black tracking-tight uppercase">{{ assignment.title }}</h1>
                                    <p class="opacity-60 font-medium mt-1">{{ assignment.meeting.teaching_assignment.subject.name }}</p>
                                </div>
                                <div class="badge badge-secondary font-bold px-4 py-3">{{ assignment.max_score }} POIN</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                <div class="bg-base-200 p-4 rounded-2xl flex items-center gap-4 border border-base-300 shadow-inner">
                                    <div class="bg-error/10 text-error p-3 rounded-xl">
                                        <Clock class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-black opacity-40">Batas Waktu</p>
                                        <p class="font-bold text-sm leading-tight">{{ assignment.formatted_due_date }}</p>
                                    </div>
                                </div>
                                <div class="bg-base-200 p-4 rounded-2xl flex items-center gap-4 border border-base-300 shadow-inner">
                                    <div class="bg-info/10 text-info p-3 rounded-xl">
                                        <ClipboardList class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-black opacity-40">Metode Pengumpulan</p>
                                        <p class="font-bold text-sm leading-tight uppercase">{{ assignment.submission_type }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="prose max-w-none mb-8">
                                <h3 class="text-lg font-bold mb-2 flex items-center gap-2">
                                    <div class="w-1.5 h-5 bg-secondary rounded-full"></div>
                                    Instruksi Tugas
                                </h3>
                                <div class="bg-base-50 p-6 rounded-2xl border border-base-100 italic text-base-content/80 whitespace-pre-line leading-relaxed">
                                    {{ assignment.instructions }}
                                </div>
                            </div>

                            <div v-if="assignment.file_url" class="mt-8 pt-8 border-t border-base-100">
                                <h3 class="text-sm font-black uppercase tracking-widest opacity-40 mb-4">Lampiran Soal / Materi Tugas</h3>
                                <a :href="'/storage/' + assignment.file_url" target="_blank" class="btn btn-outline btn-primary gap-2 rounded-2xl hover:shadow-lg transition-all shadow-primary/10">
                                    <Download class="w-5 h-5" /> Unduh File Soal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Status & Pengumpulan -->
                <div class="w-full md:w-96 space-y-6">
                    <!-- Flash Message -->
                    <div v-if="flash.success" class="alert alert-success shadow-lg border-none font-bold text-sm bg-success/10 text-success">
                        <CheckCircle class="w-5 h-5" />
                        <span>{{ flash.success }}</span>
                    </div>

                    <!-- Status Card -->
                    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                        <div class="card-body p-6">
                            <h3 class="font-black text-[10px] uppercase tracking-widest opacity-40 mb-4">Status Pengumpulan</h3>
                            
                            <div v-if="submission" class="space-y-4">
                                <div class="flex items-center gap-3 text-success p-3 bg-success/5 rounded-xl border border-success/10">
                                    <CheckCircle class="w-6 h-6" />
                                    <div>
                                        <p class="font-black text-sm uppercase leading-none">Terkirim</p>
                                        <p class="text-[10px] opacity-60 leading-none mt-1 italic">{{ submission.status.toUpperCase() }}</p>
                                    </div>
                                </div>
                                <div v-if="submission.grade" class="bg-primary/5 p-5 rounded-2xl border border-primary/20 shadow-inner">
                                    <p class="text-[10px] uppercase font-black text-primary/60 tracking-widest mb-1">Nilai Anda</p>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-5xl font-black text-primary">{{ Math.round(submission.grade.score) }}</span>
                                        <span class="text-sm opacity-30 font-bold">/ {{ assignment.max_score }}</span>
                                    </div>
                                    <div v-if="submission.grade.feedback" class="mt-4 pt-4 border-t border-primary/10">
                                        <p class="text-[10px] uppercase font-black opacity-40 mb-1">Feedback Guru:</p>
                                        <p class="text-xs italic leading-relaxed">"{{ submission.grade.feedback }}"</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 space-y-2">
                                    <div v-if="submission.file_url" class="p-3 bg-base-200 rounded-xl flex items-center justify-between group">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <FileText class="w-4 h-4 opacity-40 shrink-0" />
                                            <span class="text-[10px] font-bold truncate opacity-60">File Terkirim</span>
                                        </div>
                                        <a :href="'/storage/' + submission.file_url" target="_blank" class="btn btn-ghost btn-xs text-primary font-bold">Buka</a>
                                    </div>
                                    <p class="text-[10px] opacity-40 text-center italic">Dikumpulkan pada: {{ submission.submitted_at }}</p>
                                </div>
                            </div>
                            <div v-else class="flex items-center gap-3 text-warning p-3 bg-warning/5 rounded-xl border border-warning/10">
                                <AlertCircle class="w-6 h-6" />
                                <span class="font-black text-sm uppercase">Belum Dikumpulkan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Form (Hanya muncul jika belum dinilai) -->
                    <div v-if="!submission?.grade" class="card bg-base-100 shadow-xl border border-base-200">
                        <div class="card-body p-6">
                            <h3 class="font-black text-[10px] uppercase tracking-widest opacity-40 mb-6">Panel Pengiriman</h3>
                            
                            <form @submit.prevent="submit" class="space-y-6">
                                <div v-if="assignment.submission_type !== 'file'" class="form-control">
                                    <TextareaInput 
                                        label="Jawaban Teks Online"
                                        v-model="form.submission_text"
                                        placeholder="Ketik atau tempel jawaban Anda di sini..."
                                        :rows="8"
                                        :error="form.errors.submission_text"
                                    />
                                </div>

                                <div v-if="assignment.submission_type !== 'text'" class="form-control">
                                    <label class="label"><span class="label-text font-bold">File Lampiran Jawaban</span></label>
                                    <div class="flex items-center justify-center w-full">
                                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-base-300 rounded-2xl cursor-pointer hover:bg-base-200 hover:border-primary/50 transition-all bg-base-50/50">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <FileUp class="w-8 h-8 opacity-20 mb-2" />
                                                <p class="text-xs opacity-50"><span class="font-black text-primary uppercase">Klik untuk upload</span></p>
                                                <p class="text-[10px] opacity-30 mt-1 uppercase font-bold text-center px-4 leading-tight">Seret file jawaban Anda ke sini</p>
                                            </div>
                                            <input type="file" class="hidden" @input="form.file = $event.target.files[0]" />
                                        </label>
                                    </div>
                                    <div v-if="form.file" class="mt-2 p-2 bg-primary/5 rounded-lg border border-primary/10 flex items-center gap-2">
                                        <CheckCircle class="w-3 h-3 text-primary" />
                                        <span class="text-[10px] font-bold text-primary truncate italic">{{ form.file.name }}</span>
                                    </div>
                                    <label v-if="form.errors.file" class="label"><span class="label-text-alt text-error font-bold">{{ form.errors.file }}</span></label>
                                </div>

                                <div class="divider text-[10px] opacity-20 uppercase font-black tracking-[0.2em]">Kirim</div>

                                <button class="btn btn-primary btn-block shadow-lg shadow-primary/20 gap-2 text-white font-black uppercase tracking-widest" :disabled="form.processing">
                                    <Send class="w-4 h-4" /> {{ submission ? 'Perbarui Jawaban' : 'Kirim Sekarang' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SiswaLayout>
</template>
