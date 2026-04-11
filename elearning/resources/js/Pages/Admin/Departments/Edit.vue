<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import TextareaInput from '@/Components/forms/input/TextareaInput.vue';
import CheckboxInput from '@/Components/forms/input/CheckboxInput.vue';

const props = defineProps({
    department: Object,
});

const form = useForm({
    code: props.department.code,
    name: props.department.name,
    description: props.department.description || '',
    is_active: props.department.is_active === 1 || props.department.is_active === true,
});

const submit = () => {
    form.put(route('admin.departments.update', props.department.id));
};
</script>

<template>
    <Head :title="'Edit Jurusan: ' + department.name" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Edit Jurusan</h1>
            <div class="badge badge-lg badge-outline opacity-50">{{ department.code }}</div>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-2xl border border-base-200">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput 
                        label="Kode Jurusan"
                        v-model="form.code"
                        :error="form.errors.code"
                        required
                    />

                    <TextInput 
                        label="Nama Jurusan"
                        v-model="form.name"
                        :error="form.errors.name"
                        required
                    />

                    <TextareaInput 
                        label="Deskripsi"
                        v-model="form.description"
                        :error="form.errors.description"
                    />

                    <CheckboxInput 
                        label="Status Aktif"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                    />

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('admin.departments.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
