<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { UserPlus, Pencil, Trash2, Search, X } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const flash = computed(() => usePage().props.flash);

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        router.delete(route('admin.users.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('admin.users.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <Head title="Manajemen User" />

    <AdminLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight">MANAJEMEN USER</h1>
                <p class="text-base-content/60">Kelola akses dan akun pengguna sistem.</p>
            </div>
            <Link :href="route('admin.users.create')" class="btn btn-primary shadow-lg shadow-primary/20">
                <UserPlus class="w-5 h-5 mr-2" /> Tambah User Baru
            </Link>
        </div>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <div class="flex-1 flex items-center gap-2">
                <span>{{ flash.success }}</span>
            </div>
        </div>
        <div v-if="flash.error" class="alert alert-error mb-6 shadow-sm border-none bg-error/10 text-error font-bold">
            <div class="flex-1 flex items-center gap-2">
                <span>{{ flash.error }}</span>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <!-- Filter Area -->
            <div class="p-4 border-b border-base-200 bg-base-50/50">
                <div class="relative max-w-md">
                    <TextInput 
                        v-model="search"
                        placeholder="Cari nama, email, atau username..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                    <button 
                        v-if="search" 
                        @click="clearSearch"
                        class="btn btn-ghost btn-xs btn-circle absolute right-2 top-1/2 -translate-y-1/2"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th class="w-16">No</th>
                            <th>Info Pengguna</th>
                            <th>Role & Hak Akses</th>
                            <th>Status Akun</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-base-content/80">
                        <tr v-for="(user, index) in users.data" :key="user.id" class="hover">
                            <td class="font-mono text-xs opacity-50">{{ (users.current_page - 1) * users.per_page + index + 1 }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-base-content">{{ user.full_name }}</span>
                                    <span class="text-xs opacity-60 italic">{{ user.email }}</span>
                                    <span v-if="user.username" class="text-[10px] uppercase font-black tracking-wider opacity-40">@{{ user.username }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in user.roles" :key="role.id" class="badge badge-sm badge-outline badge-primary font-bold">
                                        {{ role.name.replace('-', ' ') }}
                                    </span>
                                    <span v-if="user.roles.length === 0" class="badge badge-sm badge-ghost opacity-50 italic text-[10px]">No Role Assigned</span>
                                </div>
                            </td>
                            <td>
                                <div :class="['badge badge-sm font-bold', user.status === 'active' ? 'badge-success bg-success/10 text-success border-none' : 'badge-error bg-error/10 text-error border-none']">
                                    {{ user.status.toUpperCase() }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('admin.users.edit', user.id)" class="btn btn-sm btn-square btn-ghost hover:bg-info/10 hover:text-info transition-colors" title="Edit User">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button 
                                        @click="deleteUser(user.id)" 
                                        class="btn btn-sm btn-square btn-ghost hover:bg-error/10 hover:text-error transition-colors" 
                                        title="Hapus User"
                                        :disabled="user.id === $page.props.auth.user.id"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="text-center py-12 opacity-50">
                                <div class="flex flex-col items-center">
                                    <Search class="w-12 h-12 opacity-10 mb-4" />
                                    <p class="font-bold">Tidak menemukan hasil untuk "{{ search }}"</p>
                                    <button @click="clearSearch" class="btn btn-link btn-sm mt-2 text-primary">Bersihkan Pencarian</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="users.links" />
        </div>
    </AdminLayout>
</template>
