<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Calendar, Plus, Trash2, ArrowLeft, Clock, MapPin, CheckCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    teachingAssignment: Object,
    schedules: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    teaching_assignment_id: props.teachingAssignment.id,
    day: '',
    start_time: '',
    end_time: '',
    room_name: '',
    is_active: true,
});

const dayOptions = [
    { value: 'monday', label: 'Senin' },
    { value: 'tuesday', label: 'Selasa' },
    { value: 'wednesday', label: 'Rabu' },
    { value: 'thursday', label: 'Kamis' },
    { value: 'friday', label: 'Jumat' },
    { value: 'saturday', label: 'Sabtu' },
    { value: 'sunday', label: 'Minggu' },
];

const submit = () => {
    form.post(route('kajur.schedules.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('day', 'start_time', 'end_time', 'room_name');
        },
    });
};

const deleteSchedule = (id) => {
    if (confirm('Hapus jadwal ini?')) {
        router.delete(route('kajur.schedules.destroy', id));
    }
};

const formatDay = (day) => {
    const d = dayOptions.find(o => o.value === day);
    return d ? d.label : day;
};
</script>

<template>
    <Head :title="'Jadwal - ' + teachingAssignment.subject.name" />

    <KajurLayout>
        <div class="mb-8">
            <Link :href="route('kajur.teaching-assignments.index')" class="btn btn-ghost btn-sm gap-2 mb-4 font-bold uppercase text-[10px] tracking-widest">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Pengampu
            </Link>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-base-100 p-8 rounded-[2.5rem] border border-base-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <Calendar class="w-32 h-32" />
                </div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">{{ teachingAssignment.subject.name }}</h1>
                    <p class="text-base-content/60 font-bold uppercase text-xs tracking-widest mt-1">
                        Kelas: <span class="text-primary">{{ teachingAssignment.class_group.name }}</span> • Guru: {{ teachingAssignment.teacher.user.full_name }}
                    </p>
                </div>
                <button @click="isModalOpen = true" class="btn btn-primary shadow-lg shadow-primary/20 px-8 font-black uppercase tracking-widest text-xs relative z-10">
                    <Plus class="w-4 h-4 mr-2" /> Tambah Jadwal
                </button>
            </div>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold text-sm">
            <CheckCircle class="w-5 h-5" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="schedule in schedules" :key="schedule.id" class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary/30 transition-all group overflow-hidden">
                <div class="bg-primary h-1"></div>
                <div class="card-body p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="badge badge-primary font-black px-3 uppercase text-[10px]">{{ formatDay(schedule.day) }}</div>
                        <button @click="deleteSchedule(schedule.id)" class="btn btn-ghost btn-xs btn-circle text-error opacity-0 group-hover:opacity-100 transition-opacity">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-base-200 p-2 rounded-lg"><Clock class="w-4 h-4 opacity-40" /></div>
                            <span class="font-black text-xl tracking-tight text-base-content">{{ schedule.start_time }} - {{ schedule.end_time }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-base-200 p-2 rounded-lg"><MapPin class="w-4 h-4 opacity-40" /></div>
                            <span class="font-bold text-sm opacity-60 uppercase">{{ schedule.room_name || 'Ruangan Belum Diatur' }}</span>
                        </div>
                    </div>

                    <div v-if="!schedule.is_active" class="mt-4 badge badge-error badge-xs font-bold uppercase tracking-tighter opacity-50">Tidak Aktif</div>
                </div>
            </div>

            <div v-if="schedules.length === 0" class="col-span-full bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-200 opacity-40 italic">
                <div class="bg-base-200 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Calendar class="w-8 h-8 opacity-20" />
                </div>
                <p class="font-bold">Belum ada jadwal yang diatur.</p>
                <button @click="isModalOpen = true" class="btn btn-link btn-sm text-primary font-black uppercase mt-2">Atur Sekarang</button>
            </div>
        </div>

        <!-- Add Schedule Modal -->
        <input type="checkbox" id="schedule-modal" class="modal-toggle" :checked="isModalOpen" @change="isModalOpen = $event.target.checked" />
        <div class="modal">
            <div class="modal-box rounded-3xl max-w-md">
                <h3 class="font-black text-2xl mb-2 tracking-tight text-primary uppercase">Tambah Jadwal</h3>
                <p class="text-xs opacity-50 mb-8 italic border-b pb-4">Tentukan hari dan jam belajar untuk mapel ini.</p>
                
                <form @submit.prevent="submit" class="space-y-5">
                    <SelectInput 
                        label="Pilih Hari"
                        v-model="form.day"
                        :options="dayOptions"
                        :error="form.errors.day"
                        required
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <TextInput 
                            label="Jam Mulai"
                            type="time"
                            v-model="form.start_time"
                            :error="form.errors.start_time"
                            required
                        />
                        <TextInput 
                            label="Jam Selesai"
                            type="time"
                            v-model="form.end_time"
                            :error="form.errors.end_time"
                            required
                        />
                    </div>

                    <TextInput 
                        label="Nama Ruangan (Opsional)"
                        v-model="form.room_name"
                        :error="form.errors.room_name"
                        placeholder="Contoh: Lab Komputer 1"
                    />

                    <CheckboxInput 
                        label="Aktifkan Jadwal"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="modal-action gap-2 mt-8">
                        <label @click="isModalOpen = false" class="btn btn-ghost flex-1">Batal</label>
                        <button type="submit" class="btn btn-primary flex-1 shadow-lg shadow-primary/20 font-black uppercase" :disabled="form.processing">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
            <label class="modal-backdrop" @click="isModalOpen = false">Close</label>
        </div>
    </KajurLayout>
</template>
