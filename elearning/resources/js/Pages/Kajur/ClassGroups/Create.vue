<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    departments: Array,
    academicYears: Array,
    teachers: Array,
});

const form = useForm({
    department_id: '',
    academic_year_id: '',
    homeroom_teacher_id: '',
    code: '',
    name: '',
    grade_level: '',
    capacity: '',
    is_active: true,
});

const deptOptions = props.departments.map(d => ({ value: d.id, label: d.name }));
const ayOptions = props.academicYears.map(ay => ({ value: ay.id, label: ay.name }));
const teacherOptions = [
    { value: '', label: 'Belum ditentukan' },
    ...props.teachers.map(t => ({ value: t.id, label: t.user.full_name }))
];

const gradeOptions = [
    { value: 10, label: 'Kelas 10' },
    { value: 11, label: 'Kelas 11' },
    { value: 12, label: 'Kelas 12' },
    { value: 13, label: 'Kelas 13' },
];

const submit = () => {
    form.post(route('kajur.class-groups.store'));
};
</script>

<template>
    <Head title="Tambah Kelas" />

    <KajurLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Tambah Kelas</h1>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-4xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <SelectInput 
                            label="Jurusan"
                            v-model="form.department_id"
                            :options="deptOptions"
                            :error="form.errors.department_id"
                            required
                        />

                        <SelectInput 
                            label="Tahun Ajaran"
                            v-model="form.academic_year_id"
                            :options="ayOptions"
                            :error="form.errors.academic_year_id"
                            required
                        />

                        <TextInput 
                            label="Kode Kelas"
                            v-model="form.code"
                            :error="form.errors.code"
                            placeholder="Contoh: X-RPL-1"
                            required
                        />

                        <TextInput 
                            label="Nama Kelas"
                            v-model="form.name"
                            :error="form.errors.name"
                            placeholder="Contoh: Kelas X RPL 1"
                            required
                        />

                        <SelectInput 
                            label="Tingkat"
                            v-model="form.grade_level"
                            :options="gradeOptions"
                            :error="form.errors.grade_level"
                            required
                        />

                        <TextInput 
                            label="Kapasitas Siswa"
                            v-model="form.capacity"
                            type="number"
                            :error="form.errors.capacity"
                            placeholder="Opsional"
                        />

                        <SelectInput 
                            label="Wali Kelas"
                            v-model="form.homeroom_teacher_id"
                            :options="teacherOptions"
                            :error="form.errors.homeroom_teacher_id"
                        />

                        <div class="flex items-center pt-8">
                            <CheckboxInput 
                                label="Kelas Aktif"
                                v-model="form.is_active"
                                :error="form.errors.is_active"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('kajur.class-groups.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KajurLayout>
</template>
