<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import { ref } from 'vue';

const props = defineProps({
    teachers: Array,
    classGroups: Array,
    subjects: Array,
    semesters: Array,
});

const form = useForm({
    teacher_id: '',
    class_group_id: '',
    subject_id: '',
    semester_id: props.semesters.length > 0 ? props.semesters[0].id : '',
});

const teacherOptions = props.teachers.map(t => ({ value: t.id, label: t.user.full_name }));
const classOptions = props.classGroups.map(c => ({ value: c.id, label: c.name }));
const subjectOptions = props.subjects.map(s => ({ value: s.id, label: s.name + ' (' + s.code + ')' }));
const semesterOptions = props.semesters.map(s => ({ value: s.id, label: s.name + ' - ' + s.academic_year.name }));

const submit = () => {
    form.post(route('kajur.teaching-assignments.store'));
};
</script>

<template>
    <Head title="Plotting Pengampu Baru" />

    <KajurLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Plotting Pengampu Baru</h1>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <div class="alert bg-info/5 border-none mb-6">
                    <div class="text-xs opacity-70">
                        Pilih kombinasi Guru, Mata Pelajaran, dan Kelas untuk periode semester aktif.
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <SelectInput 
                        label="Pilih Guru"
                        v-model="form.teacher_id"
                        :options="teacherOptions"
                        :error="form.errors.teacher_id"
                        required
                    />

                    <SelectInput 
                        label="Mata Pelajaran"
                        v-model="form.subject_id"
                        :options="subjectOptions"
                        :error="form.errors.subject_id"
                        required
                    />

                    <SelectInput 
                        label="Kelas"
                        v-model="form.class_group_id"
                        :options="classOptions"
                        :error="form.errors.class_group_id"
                        required
                    />

                    <SelectInput 
                        label="Periode Semester"
                        v-model="form.semester_id"
                        :options="semesterOptions"
                        :error="form.errors.semester_id"
                        required
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('kajur.teaching-assignments.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Plotting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KajurLayout>
</template>
