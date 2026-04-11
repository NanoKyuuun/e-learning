<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import DateInput from '@/Components/forms/input/DateInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    status: 'active',
});

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
    { value: 'archived', label: 'Arsip (Selesai)' },
];

const submit = () => {
    form.post(route('admin.academic-years.store'));
};
</script>

<template>
    <Head title="Tambah Tahun Ajaran" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Tambah Tahun Ajaran</h1>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Nama Tahun Ajaran"
                        v-model="form.name"
                        :error="form.errors.name"
                        placeholder="Contoh: 2026/2027"
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
                            Simpan Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
