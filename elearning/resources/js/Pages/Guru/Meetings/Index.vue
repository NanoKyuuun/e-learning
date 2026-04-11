<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { Presentation, Plus, Eye, Send, FileText, ClipboardList, Trash2, ArrowLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    teachingAssignment: Object,
    meetings: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    meeting_number: props.meetings.length + 1,
    title: '',
    topic: '',
    meeting_date: new Date().toISOString().substr(0, 10),
});

const submit = () => {
    form.post(route('guru.meetings.store', props.teachingAssignment.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('title', 'topic');
            form.meeting_number = props.meetings.length + 1;
        },
    });
};

const publish = (id) => {
    if (confirm('Publikasikan pertemuan ini agar bisa dilihat siswa?')) {
        router.patch(route('guru.meetings.publish', id));
    }
};

const deleteMeeting = (id) => {
    if (confirm('Hapus pertemuan ini beserta seluruh isinya?')) {
        router.delete(route('guru.meetings.destroy', id));
    }
};
</script>

<template>
    <Head :title="'Pertemuan - ' + teachingAssignment.subject.name" />

    <GuruLayout>
        <div class="mb-8">
            <Link :href="route('guru.dashboard')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Dashboard
            </Link>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-base-content tracking-tight">{{ teachingAssignment.subject.name }}</h1>
                    <p class="text-base-content/60 font-bold uppercase text-xs tracking-widest mt-1">
                        Kelas: <span class="text-primary">{{ teachingAssignment.class_group.name }}</span>
                    </p>
                </div>
                <button @click="isModalOpen = true" class="btn btn-primary shadow-lg shadow-primary/20">
                    <Plus class="w-5 h-5 mr-2" /> Buat Pertemuan Baru
                </button>
            </div>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="space-y-6">
            <div v-for="meeting in meetings" :key="meeting.id" class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden hover:border-primary/30 transition-all group">
                <div class="flex flex-col md:flex-row">
                    <div class="bg-primary text-primary-content p-8 flex flex-col items-center justify-center min-w-[140px] group-hover:bg-primary-focus transition-colors">
                        <span class="text-xs opacity-70 font-black uppercase tracking-tighter">Pertemuan</span>
                        <span class="text-5xl font-black">{{ meeting.meeting_number }}</span>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h2 class="text-2xl font-black text-base-content leading-tight group-hover:text-primary transition-colors">{{ meeting.title }}</h2>
                                <p class="text-sm opacity-50 font-mono mt-1 italic">{{ meeting.meeting_date || 'Tanggal belum diatur' }}</p>
                            </div>
                            <div class="flex gap-2">
                                <div v-if="meeting.status === 'published'" class="badge badge-success bg-success/10 text-success border-none font-bold gap-1 px-3">
                                    <Eye class="w-3 h-3" /> PUBLISHED
                                </div>
                                <div v-else class="badge badge-warning bg-warning/10 text-warning border-none font-bold gap-1 px-3">
                                    DRAFT
                                </div>
                            </div>
                        </div>

                        <p class="text-base-content/70 line-clamp-2 mb-6 text-sm italic">{{ meeting.topic || 'Tidak ada deskripsi topik untuk pertemuan ini.' }}</p>

                        <div class="mt-auto flex flex-wrap gap-4 justify-between items-center pt-4 border-t border-base-100">
                            <div class="flex gap-6">
                                <div class="flex items-center gap-2 text-xs font-bold opacity-40">
                                    <FileText class="w-4 h-4" /> 0 MATERI
                                </div>
                                <div class="flex items-center gap-2 text-xs font-bold opacity-40">
                                    <ClipboardList class="w-4 h-4" /> 0 TUGAS
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button v-if="meeting.status === 'draft'" @click="publish(meeting.id)" class="btn btn-sm btn-ghost text-success hover:bg-success/10" title="Publikasikan">
                                    <Send class="w-4 h-4 mr-1" /> Publish
                                </button>
                                <Link :href="route('guru.meetings.show', meeting.id)" class="btn btn-sm btn-primary shadow-md shadow-primary/10">
                                    Kelola Konten <ChevronRight class="w-4 h-4 ml-1" />
                                </Link>
                                <button @click="deleteMeeting(meeting.id)" class="btn btn-sm btn-square btn-ghost hover:bg-error/10 hover:text-error" title="Hapus">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="meetings.length === 0" class="bg-base-100 p-16 text-center rounded-3xl border-2 border-dashed border-base-300 shadow-inner">
                <div class="bg-base-200 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Presentation class="w-10 h-10 opacity-20" />
                </div>
                <h3 class="text-xl font-black text-base-content tracking-tight">Belum Ada Sesi Pertemuan</h3>
                <p class="opacity-50 text-sm max-w-xs mx-auto mt-2 italic">Mulailah dengan membuat pertemuan pertama Anda untuk mengisi kelas ini.</p>
                <button @click="isModalOpen = true" class="btn btn-primary mt-8 px-8 shadow-lg shadow-primary/20">Buat Pertemuan Pertama</button>
            </div>
        </div>

        <!-- Create Meeting Modal -->
        <input type="checkbox" id="create-modal" class="modal-toggle" :checked="isModalOpen" @change="isModalOpen = $event.target.checked" />
        <div class="modal modal-bottom sm:modal-middle">
            <div class="modal-box rounded-3xl">
                <h3 class="font-black text-2xl mb-2 tracking-tight text-primary">Buat Pertemuan Baru</h3>
                <p class="text-sm opacity-50 mb-8 italic text-base-content/60 border-b pb-4 border-base-100">Lengkapi data sesi belajar di bawah ini.</p>
                
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-bold opacity-70">Nomor Urut Pertemuan</span>
                        </label>
                        <input v-model="form.meeting_number" type="number" class="input input-bordered w-full font-black text-lg" required />
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-bold opacity-70">Judul Sesi / Materi Utama</span>
                        </label>
                        <input v-model="form.title" type="text" placeholder="Contoh: Pengenalan Dasar HTML" class="input input-bordered w-full font-medium" :class="{ 'input-error': form.errors.title }" required />
                        <label v-if="form.errors.title" class="label">
                            <span class="label-text-alt text-error font-bold">{{ form.errors.title }}</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-bold opacity-70 text-xs uppercase tracking-widest">Deskripsi Topik (Opsional)</span>
                        </label>
                        <textarea v-model="form.topic" class="textarea textarea-bordered h-28 italic text-sm" placeholder="Apa yang akan dibahas pada pertemuan ini?"></textarea>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-bold opacity-70">Tanggal Pelaksanaan</span>
                        </label>
                        <input v-model="form.meeting_date" type="date" class="input input-bordered w-full" />
                    </div>
                    
                    <div class="modal-action gap-2">
                        <label @click="isModalOpen = false" class="btn btn-ghost flex-1">Batal</label>
                        <button type="submit" class="btn btn-primary flex-1 shadow-lg shadow-primary/20" :disabled="form.processing">Simpan Sesi</button>
                    </div>
                </form>
            </div>
            <label class="modal-backdrop" @click="isModalOpen = false">Close</label>
        </div>
    </GuruLayout>
</template>
