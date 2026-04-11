<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Building2, Pencil, Trash2, Search, X, Plus } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    departments: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

const deleteDepartment = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jurusan ini?')) {
        router.delete(route('admin.departments.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('admin.departments.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <Head title="Manajemen Jurusan" />

    <AdminLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Manajemen Jurusan</h1>
                <p class="text-base-content/60">Kelola data program keahlian / jurusan.</p>
            </div>
            <Link :href="route('admin.departments.create')" class="btn btn-primary">
                <Plus class="w-5 h-5 mr-2" /> Tambah Jurusan
            </Link>
        </div>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ flash.success }}</span>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="p-4 border-b border-base-200">
                <div class="relative max-w-md">
                    <TextInput 
                        v-model="search"
                        placeholder="Cari nama atau kode jurusan..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-base-content/80">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th class="w-16">No</th>
                            <th>Kode</th>
                            <th>Nama Jurusan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(dept, index) in departments.data" :key="dept.id" class="hover">
                            <td>{{ (departments.current_page - 1) * departments.per_page + index + 1 }}</td>
                            <td class="font-mono font-bold text-primary">{{ dept.code }}</td>
                            <td>
                                <div class="font-bold text-base-content">{{ dept.name }}</div>
                                <div class="text-xs opacity-50 line-clamp-1">{{ dept.description || 'Tidak ada deskripsi' }}</div>
                            </td>
                            <td>
                                <div :class="['badge badge-sm font-bold', dept.is_active ? 'badge-success bg-success/10 text-success border-none' : 'badge-error bg-error/10 text-error border-none']">
                                    {{ dept.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('admin.departments.edit', dept.id)" class="btn btn-sm btn-square btn-ghost hover:text-info">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="deleteDepartment(dept.id)" class="btn btn-sm btn-square btn-ghost hover:text-error">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="departments.data.length === 0">
                            <td colspan="5" class="text-center py-12 opacity-50">Belum ada data jurusan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="departments.links" />
        </div>
    </AdminLayout>
</template>
