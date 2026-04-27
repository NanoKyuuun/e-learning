<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/forms/input/TextInput.vue';
import SelectInput from '@/Components/forms/input/SelectInput.vue';

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    full_name: props.user.full_name,
    username: props.user.username,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    status: props.user.status,
    roles: props.user.roles.map(r => r.name),
});

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="'Edit User: ' + user.full_name" />

    <AdminLayout>
        <div class="mb-8 flex items-center gap-4">
            <!-- Avatar -->
            <div class="avatar" :class="user.avatar ? '' : 'placeholder'">
                <div v-if="user.avatar" class="w-14 h-14 rounded-full shadow-inner overflow-hidden border-2 border-base-200">
                    <img :src="'/storage/' + user.avatar" :alt="user.full_name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="bg-primary text-primary-content rounded-full w-14 h-14 shadow-inner font-bold text-xl flex items-center justify-center">
                    <span>{{ user.full_name.charAt(0).toUpperCase() }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-base-content">Edit User</h1>
                <div class="badge badge-lg badge-outline opacity-50 mt-1">{{ user.full_name }}</div>
            </div>
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
                            placeholder="Kosongkan jika tidak ingin ganti"
                        />

                        <TextInput
                            label="Konfirmasi Password"
                            v-model="form.password_confirmation"
                            type="password"
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
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
