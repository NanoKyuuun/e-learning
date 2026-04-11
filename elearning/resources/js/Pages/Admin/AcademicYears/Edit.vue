<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import DateInput from '@/Components/forms/input/DateInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';

const props = defineProps({
    academicYear: Object,
});

const form = useForm({
    name: props.academicYear.name,
    start_date: props.academicYear.start_date,
    end_date: props.academicYear.end_date,
    status: props.academicYear.status,
});

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
    { value: 'archived', label: 'Arsip (Selesai)' },
];

const submit = () => {
    form.put(route('admin.academic-years.update', props.academicYear.id));
};
</script>

<template>
    <Head :title="'Edit Tahun Ajaran: ' + academicYear.name" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Edit Tahun Ajaran</h1>
            <div class="badge badge-lg badge-outline opacity-50">{{ academicYear.name }}</div>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Nama Tahun Ajaran"
                        v-model="form.name"
                        :error="form.errors.name"
                        required
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <DateInput 
                            label="Tanggal Mulai"
                            v-model="form.start_date"
                            :error="form.errors.start_date"
                            required
                        />

                        <DateInput 
                            label="Tanggal Selesai"
                            v-model="form.end_date"
                            :error="form.errors.end_date"
                            required
                        />
                    </div>

                    <SelectInput 
                        label="Status"
                        v-model="form.status"
                        :options="statusOptions"
                        :error="form.errors.status"
                        required
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('admin.academic-years.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
