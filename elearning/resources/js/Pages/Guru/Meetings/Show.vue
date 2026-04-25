<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    FileText, ClipboardList, Plus, Trash2, ArrowLeft, Download, ExternalLink,
    Calendar, Clock, FileUp, Users, CheckCircle, XCircle, ShieldCheck, AlertCircle
} from 'lucide-vue-next';
import { ref } from 'vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import DateInput from '@/Components/forms/input/DateInput.vue';

const props = defineProps({
    meeting:           Object,
    enrolledStudents:  Array,
    attendanceSummary: Object,
});

const isMaterialModalOpen = ref(false);
const isAssignmentModalOpen = ref(false);

const materialForm = useForm({
    title: '',
    description: '',
    file: null,
});

const assignmentForm = useForm({
    title: '',
    instructions: '',
    due_at: '',
    max_score: 100,
    submission_type: 'file',
    file: null, // File soal dari guru
});

const submissionOptions = [
    { value: 'file', label: 'Hanya File' },
    { value: 'text', label: 'Hanya Teks Online' },
    { value: 'both', label: 'File dan Teks' },
];

const submitMaterial = () => {
    materialForm.post(route('guru.materials.store', props.meeting.id), {
        onSuccess: () => {
            isMaterialModalOpen.value = false;
            materialForm.reset();
        },
    });
};

const submitAssignment = () => {
    assignmentForm.post(route('guru.assignments.store', props.meeting.id), {
        onSuccess: () => {
            isAssignmentModalOpen.value = false;
            assignmentForm.reset();
        },
    });
};

const deleteMaterial = (id) => {
    if (confirm('Hapus materi ini?')) {
        router.delete(route('guru.materials.destroy', id));
    }
};

const deleteAssignment = (id) => {
    if (confirm('Hapus tugas ini beserta seluruh pengumpulan siswa?')) {
        router.delete(route('guru.assignments.destroy', id));
    }
};
</script>

<template>
    <Head :title="meeting.title + ' - Kelola Konten'" />

    <GuruLayout>
        <div class="mb-8">
            <Link :href="route('guru.meetings.index', meeting.teaching_assignment_id)" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar Sesi
            </Link>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-200 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="bg-primary text-primary-content w-16 h-16 rounded-2xl flex flex-col items-center justify-center font-black">
                        <span class="text-[10px] opacity-70 uppercase">Sesi</span>
                        <span class="text-2xl">{{ meeting.meeting_number }}</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-base-content tracking-tight uppercase">{{ meeting.title }}</h1>
                        <div class="flex items-center gap-4 mt-1">
                            <span class="text-xs font-bold opacity-50 flex items-center gap-1">
                                <Calendar class="w-3 h-3" /> {{ meeting.meeting_date || 'Tgl belum diatur' }}
                            </span>
                            <span :class="['badge badge-xs font-bold', meeting.status === 'published' ? 'badge-success' : 'badge-warning']">
                                {{ meeting.status.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="isMaterialModalOpen = true" class="btn btn-primary btn-sm md:btn-md gap-2 shadow-lg shadow-primary/20">
                        <Plus class="w-4 h-4" /> Tambah Materi
                    </button>
                    <button @click="isAssignmentModalOpen = true" class="btn btn-secondary btn-sm md:btn-md gap-2 shadow-lg shadow-secondary/20">
                        <Plus class="w-4 h-4" /> Tambah Tugas
                    </button>
                </div>
            </div>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Section Materi -->
            <div class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-2 text-primary uppercase tracking-tight">
                        <FileText class="w-6 h-6" /> Daftar Materi
                    </h2>
                    <span class="badge badge-primary font-bold">{{ meeting.materials.length }}</span>
                </div>

                <div v-for="material in meeting.materials" :key="material.id" class="card bg-base-100 shadow-md border border-base-200 hover:shadow-lg transition-all">
                    <div class="card-body p-5">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                <div class="bg-base-200 p-3 rounded-xl text-primary">
                                    <FileText class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-tight">{{ material.title }}</h3>
                                    <p class="text-xs opacity-50 mt-1 italic">{{ material.description || 'Tidak ada deskripsi.' }}</p>
                                </div>
                            </div>
                            <button @click="deleteMaterial(material.id)" class="btn btn-ghost btn-sm btn-square text-error">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="card-actions justify-end mt-4 pt-4 border-t border-base-50">
                            <a v-if="material.file_url" :href="'/storage/' + material.file_url" target="_blank" class="btn btn-xs btn-ghost gap-1 text-primary font-bold">
                                <Download class="w-3 h-3" /> Unduh File Materi
                            </a>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.materials.length === 0" class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic">
                    Belum ada materi yang diunggah.
                </div>
            </div>

            <!-- Section Tugas -->
            <div class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black flex items-center gap-2 text-secondary uppercase tracking-tight">
                        <ClipboardList class="w-6 h-6" /> Daftar Tugas
                    </h2>
                    <span class="badge badge-secondary font-bold">{{ meeting.assignments.length }}</span>
                </div>

                <div v-for="assignment in meeting.assignments" :key="assignment.id" class="card bg-base-100 shadow-md border-l-4 border-l-secondary border border-base-200 hover:shadow-lg transition-all">
                    <div class="card-body p-5">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg text-base-content">{{ assignment.title }}</h3>
                                <div class="flex items-center gap-3 mt-2">
                                    <div class="flex items-center gap-1 text-xs font-bold opacity-50 uppercase tracking-tighter">
                                        <Clock class="w-3 h-3 text-error" /> Deadline: {{ assignment.formatted_due_date }}
                                    </div>
                                    <div class="badge badge-ghost badge-xs uppercase font-bold">{{ assignment.submission_type }}</div>
                                </div>
                            </div>
                            <button @click="deleteAssignment(assignment.id)" class="btn btn-ghost btn-sm btn-square text-error">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        
                        <div v-if="assignment.file_url" class="mt-3">
                            <a :href="'/storage/' + assignment.file_url" target="_blank" class="text-xs text-primary font-bold flex items-center gap-1 hover:underline">
                                <FileText class="w-3 h-3" /> File Soal Terlampir
                            </a>
                        </div>

                        <div class="card-actions justify-end mt-4 pt-4 border-t border-base-50">
                            <Link :href="route('guru.assignments.submissions', assignment.id)" class="btn btn-xs btn-secondary font-bold gap-1 shadow-sm text-white">
                                <ExternalLink class="w-3 h-3" /> Lihat Submission ({{ assignment.submissions_count || 0 }})
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="meeting.assignments.length === 0" class="bg-base-100 p-12 text-center rounded-3xl border-2 border-dashed border-base-200 opacity-40 italic">
                    Belum ada tugas untuk sesi ini.
                </div>
            </div>
        </div>

        <!-- Add Material Modal -->
        <input type="checkbox" id="material-modal" class="modal-toggle" :checked="isMaterialModalOpen" @change="isMaterialModalOpen = $event.target.checked" />
        <div class="modal">
            <div class="modal-box rounded-3xl">
                <h3 class="font-black text-2xl mb-6 tracking-tight text-primary">Unggah Materi Baru</h3>
                <form @submit.prevent="submitMaterial" class="space-y-5">
                    <TextInput label="Judul Materi" v-model="materialForm.title" :error="materialForm.errors.title" required />
                    <TextareaInput label="Deskripsi" v-model="materialForm.description" :error="materialForm.errors.description" />
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold opacity-70">Pilih File</span></label>
                        <input type="file" @input="materialForm.file = $event.target.files[0]" class="file-input file-input-bordered file-input-primary w-full shadow-sm" />
                        <label class="label"><span class="label-text-alt opacity-50">Maksimal 10MB</span></label>
                    </div>

                    <div class="modal-action">
                        <label @click="isMaterialModalOpen = false" class="btn btn-ghost">Batal</label>
                        <button type="submit" class="btn btn-primary px-8" :disabled="materialForm.processing">Simpan Materi</button>
                    </div>
                </form>
            </div>
            <label class="modal-backdrop" @click="isMaterialModalOpen = false">Close</label>
        </div>

        <!-- Add Assignment Modal -->
        <input type="checkbox" id="assignment-modal" class="modal-toggle" :checked="isAssignmentModalOpen" @change="isAssignmentModalOpen = $event.target.checked" />
        <div class="modal">
            <div class="modal-box rounded-3xl max-w-2xl">
                <h3 class="font-black text-2xl mb-6 tracking-tight text-secondary">Buat Tugas Baru</h3>
                <form @submit.prevent="submitAssignment" class="space-y-5">
                    <TextInput label="Judul Tugas" v-model="assignmentForm.title" :error="assignmentForm.errors.title" required />
                    <TextareaInput label="Instruksi Pengerjaan" v-model="assignmentForm.instructions" :error="assignmentForm.errors.instructions" required placeholder="Tulis instruksi lengkap di sini..." />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <DateInput label="Batas Waktu (Due Date)" v-model="assignmentForm.due_at" :error="assignmentForm.errors.due_at" required />
                        <TextInput label="Skor Maksimal" v-model="assignmentForm.max_score" type="number" :error="assignmentForm.errors.max_score" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <SelectInput label="Metode Pengumpulan" v-model="assignmentForm.submission_type" :options="submissionOptions" :error="assignmentForm.errors.submission_type" required />
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold opacity-70">Upload File Soal (Opsional)</span></label>
                            <input type="file" @input="assignmentForm.file = $event.target.files[0]" class="file-input file-input-bordered file-input-secondary w-full" />
                        </div>
                    </div>

                    <div class="modal-action">
                        <label @click="isAssignmentModalOpen = false" class="btn btn-ghost">Batal</label>
                        <button type="submit" class="btn btn-secondary px-8 text-white shadow-lg shadow-secondary/20" :disabled="assignmentForm.processing">Publikasikan Tugas</button>
                    </div>
                </form>
            </div>
            <label class="modal-backdrop" @click="isAssignmentModalOpen = false">Close</label>
        </div>
        <!-- Section Rekap Absensi ──────────────────────────────────── -->
        <div class="mt-8">
            <div class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 border-b border-base-200">
                    <h2 class="text-xl font-black flex items-center gap-2 text-accent uppercase tracking-tight">
                        <Users class="w-6 h-6" /> Rekap Absensi Siswa
                    </h2>
                    <!-- Summary Badges -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="stat bg-base-200 rounded-2xl p-3 text-center min-w-[70px]">
                            <div class="stat-value text-2xl font-black">{{ attendanceSummary.total }}</div>
                            <div class="stat-desc font-bold">Total</div>
                        </div>
                        <div class="stat bg-success/10 rounded-2xl p-3 text-center min-w-[70px]">
                            <div class="stat-value text-2xl font-black text-success">{{ attendanceSummary.present }}</div>
                            <div class="stat-desc font-bold text-success">Hadir</div>
                        </div>
                        <div class="stat bg-error/10 rounded-2xl p-3 text-center min-w-[70px]">
                            <div class="stat-value text-2xl font-black text-error">{{ attendanceSummary.absent }}</div>
                            <div class="stat-desc font-bold text-error">Belum</div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Siswa -->
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="text-xs uppercase tracking-widest opacity-50">
                                <th>#</th>
                                <th>Nama Siswa</th>
                                <th>Status Wajah</th>
                                <th>Status Absensi</th>
                                <th>Pukul</th>
                                <th>Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, idx) in enrolledStudents" :key="student.id"
                                :class="student.attendance ? '' : 'opacity-60'">
                                <td class="font-bold opacity-40">{{ idx + 1 }}</td>
                                <td>
                                    <span class="font-bold">{{ student.name }}</span>
                                </td>
                                <td>
                                    <span :class="['badge badge-sm font-bold', {
                                        'badge-success': student.face_status === 'synced',
                                        'badge-warning': ['pending','syncing'].includes(student.face_status),
                                        'badge-error':   student.face_status === 'failed',
                                        'badge-ghost':   student.face_status === 'none' || student.face_status === 'disabled',
                                    }]">
                                        {{ student.face_status === 'synced' ? 'Siap'
                                         : student.face_status === 'pending' ? 'Pending'
                                         : student.face_status === 'syncing' ? 'Sync'
                                         : student.face_status === 'failed'  ? 'Gagal'
                                         : 'Belum' }}
                                    </span>
                                </td>
                                <td>
                                    <div v-if="student.attendance">
                                        <span :class="['badge font-bold', {
                                            'badge-success': student.attendance.status === 'present',
                                            'badge-warning': student.attendance.status === 'late',
                                            'badge-error':   student.attendance.status === 'failed',
                                            'badge-info':    student.attendance.status === 'manual',
                                            'badge-ghost':   ['excused','absent'].includes(student.attendance.status),
                                        }]">
                                            {{ {
                                                present: 'Hadir',
                                                late: 'Terlambat',
                                                failed: 'Gagal',
                                                manual: 'Manual',
                                                excused: 'Izin',
                                                absent: 'Tidak Hadir',
                                            }[student.attendance.status] ?? student.attendance.status }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs opacity-40 italic">Belum absen</span>
                                </td>
                                <td class="text-sm font-mono">
                                    {{ student.attendance?.check_in_at ?? '—' }}
                                </td>
                                <td>
                                    <div v-if="student.attendance?.face_verified" class="flex items-center gap-1 text-success text-xs font-bold">
                                        <CheckCircle class="w-4 h-4" />
                                        {{ student.attendance.face_distance?.toFixed(3) }}
                                    </div>
                                    <div v-else-if="student.attendance && !student.attendance.face_verified" class="flex items-center gap-1 text-warning text-xs font-bold">
                                        <AlertCircle class="w-4 h-4" /> Manual
                                    </div>
                                    <span v-else class="text-xs opacity-30">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!enrolledStudents || enrolledStudents.length === 0"
                         class="p-12 text-center opacity-40 italic">
                        Belum ada siswa terdaftar di kelas ini.
                    </div>
                </div>
            </div>
        </div>
        <!-- ──────────────────────────────────────────────────────────────── -->
    </GuruLayout>
</template>
