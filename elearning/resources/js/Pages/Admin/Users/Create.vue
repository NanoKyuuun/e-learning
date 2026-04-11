<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    full_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    roles: [],
});

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="Tambah User" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <h1 class="text-3xl font-bold text-base-content">Tambah User</h1>
        </div>

        <div class="card bg-base-100 shadow-xl max-w-4xl">
            <div class="card-body">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <TextInput
                            label="Nama Lengkap"
                            v-model="form.full_name"
                            :error="form.errors.full_name"
                            required
                        />

                        <TextInput
                            label="Username"
                            v-model="form.username"
                            :error="form.errors.username"
                        />

                        <TextInput
                            label="Email"
                            v-model="form.email"
                            type="email"
                            :error="form.errors.email"
                            required
                        />

                        <SelectInput
                            label="Status"
                            v-model="form.status"
                            :options="statusOptions"
                            :error="form.errors.status"
                            required
                        />

                        <TextInput
                            label="Password"
                            v-model="form.password"
                            type="password"
                            :error="form.errors.password"
                            required
                        />

                        <TextInput
                            label="Konfirmasi Password"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                        />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold text-lg">Pilih Role</span>
                        </label>
                        <div class="flex flex-wrap gap-4 mt-2">
                            <label v-for="role in roles" :key="role.id" class="label cursor-pointer flex gap-2 bg-base-200 px-4 py-2 rounded-lg hover:bg-base-300 transition-colors">
                                <input type="checkbox" :value="role.name" v-model="form.roles" class="checkbox checkbox-primary" />
                                <span class="label-text font-medium">{{ role.name }}</span>
                            </label>
                        </div>
                        <label v-if="form.errors.roles" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.roles }}</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-base-200">
                        <Link :href="route('admin.users.index')" class="btn btn-ghost">Batal</Link>
                        <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
                            Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
