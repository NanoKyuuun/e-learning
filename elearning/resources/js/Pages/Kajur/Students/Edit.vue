<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    student: Object,
});

const form = useForm({
    student_number: props.student.student_number || '',
    phone: props.student.phone || '',
    gender: props.student.gender || '',
    is_active: props.student.is_active === 1 || props.student.is_active === true,
});

const genderOptions = [
    { value: 'laki-laki', label: 'Laki-laki' },
    { value: 'perempuan', label: 'Perempuan' },
];

const submit = () => {
    form.put(route('kajur.students.update', props.student.id));
};
</script>

<template>
    <Head :title="'Edit Profil Siswa: ' + student.user.full_name" />

    <KajurLayout>
        <div class="mb-8 flex items-center gap-4">
            <!-- Avatar -->
            <div class="avatar" :class="student.user.avatar ? '' : 'placeholder'">
                <div v-if="student.user.avatar" class="w-14 h-14 rounded-full shadow-inner overflow-hidden border-2 border-base-200">
                    <img :src="'/storage/' + student.user.avatar" :alt="student.user.full_name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="bg-primary text-primary-content rounded-full w-14 h-14 shadow-inner font-bold text-xl flex items-center justify-center">
                    <span>{{ student.user.full_name.charAt(0).toUpperCase() }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Edit Profil Siswa</h1>
                <div class="badge badge-lg badge-outline opacity-50 mt-1">{{ student.user.full_name }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <div class="alert bg-base-200 border-none mb-6">
                    <div class="flex flex-col">
                        <span class="text-xs opacity-50 uppercase font-black tracking-widest">Informasi Akun (ReadOnly)</span>
                        <span class="font-bold">{{ student.user.email }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Nomor Induk Siswa (NIS)"
                        v-model="form.student_number"
                        :error="form.errors.student_number"
                        placeholder="Contoh: 2425..."
                    />

                    <SelectInput 
                        label="Jenis Kelamin"
                        v-model="form.gender"
                        :options="genderOptions"
                        :error="form.errors.gender"
                    />

                    <TextInput 
                        label="Nomor WhatsApp/Telepon"
                        v-model="form.phone"
                        :error="form.errors.phone"
                        placeholder="Contoh: 08..."
                    />

                    <CheckboxInput 
                        label="Status Aktif Siswa"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('kajur.students.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KajurLayout>
</template>
