<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const form = useForm({
    code: '',
    name: '',
    description: '',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.departments.store'));
};
</script>

<template>
    <Head title="Tambah Jurusan" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Tambah Jurusan</h1>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Kode Jurusan"
                        v-model="form.code"
                        :error="form.errors.code"
                        placeholder="Contoh: RPL, TKJ, dsb."
                        required
                    />

                    <TextInput 
                        label="Nama Jurusan"
                        v-model="form.name"
                        :error="form.errors.name"
                        placeholder="Contoh: Rekayasa Perangkat Lunak"
                        required
                    />

                    <TextareaInput 
                        label="Deskripsi"
                        v-model="form.description"
                        :error="form.errors.description"
                        placeholder="Penjelasan singkat mengenai jurusan ini..."
                    />

                    <CheckboxInput 
                        label="Status Aktif"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('admin.departments.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Jurusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
