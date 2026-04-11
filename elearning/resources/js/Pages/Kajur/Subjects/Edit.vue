<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    subject: Object,
    departments: Array,
});

const form = useForm({
    department_id: props.subject.department_id || '',
    code: props.subject.code,
    name: props.subject.name,
    grade_level: props.subject.grade_level || '',
    description: props.subject.description || '',
    is_active: props.subject.is_active === 1 || props.subject.is_active === true,
});

const deptOptions = [
    { value: '', label: 'Umum (Lintas Jurusan)' },
    ...props.departments.map(d => ({ value: d.id, label: d.name }))
];

const gradeOptions = [
    { value: 10, label: 'Kelas 10' },
    { value: 11, label: 'Kelas 11' },
    { value: 12, label: 'Kelas 12' },
    { value: 13, label: 'Kelas 13' },
];

const submit = () => {
    form.put(route('kajur.subjects.update', props.subject.id));
};
</script>

<template>
    <Head :title="'Edit Mapel: ' + subject.name" />

    <KajurLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Edit Mapel</h1>
            <div class="badge badge-lg badge-outline opacity-50">{{ subject.code }}</div>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <SelectInput 
                        label="Jurusan / Program Keahlian"
                        v-model="form.department_id"
                        :options="deptOptions"
                        :error="form.errors.department_id"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <TextInput 
                            label="Kode Mapel"
                            v-model="form.code"
                            :error="form.errors.code"
                            required
                        />

                        <SelectInput 
                            label="Tingkat / Grade"
                            v-model="form.grade_level"
                            :options="gradeOptions"
                            :error="form.errors.grade_level"
                        />
                    </div>

                    <TextInput 
                        label="Nama Mata Pelajaran"
                        v-model="form.name"
                        :error="form.errors.name"
                        required
                    />

                    <TextareaInput 
                        label="Deskripsi Mapel"
                        v-model="form.description"
                        :error="form.errors.description"
                    />

                    <CheckboxInput 
                        label="Aktifkan Mata Pelajaran"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('kajur.subjects.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KajurLayout>
</template>
