<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    teacher: Object,
    departments: Array,
});

const form = useForm({
    department_id: props.teacher.department_id || '',
    employee_number: props.teacher.employee_number || '',
    phone: props.teacher.phone || '',
    is_active: props.teacher.is_active === 1 || props.teacher.is_active === true,
});

const deptOptions = [
    { value: '', label: 'Pilih Jurusan...' },
    ...props.departments.map(d => ({ value: d.id, label: d.name }))
];

const submit = () => {
    form.put(route('kajur.teachers.update', props.teacher.id));
};
</script>

<template>
    <Head :title="'Edit Profil Guru: ' + teacher.user.full_name" />

    <KajurLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Edit Profil Guru</h1>
            <div class="badge badge-lg badge-outline opacity-50">{{ teacher.user.full_name }}</div>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <div class="alert bg-base-200 border-none mb-6">
                    <div class="flex flex-col">
                        <span class="text-xs opacity-50 uppercase font-black tracking-widest">Informasi Akun (ReadOnly)</span>
                        <span class="font-bold">{{ teacher.user.email }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Nomor Induk Pegawai (NIP)"
                        v-model="form.employee_number"
                        :error="form.errors.employee_number"
                        placeholder="Contoh: 19850101..."
                    />

                    <SelectInput 
                        label="Penempatan Jurusan"
                        v-model="form.department_id"
                        :options="deptOptions"
                        :error="form.errors.department_id"
                    />

                    <TextInput 
                        label="Nomor WhatsApp/Telepon"
                        v-model="form.phone"
                        :error="form.errors.phone"
                        placeholder="Contoh: 0812..."
                    />

                    <CheckboxInput 
                        label="Status Aktif Mengajar"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('kajur.teachers.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KajurLayout>
</template>
