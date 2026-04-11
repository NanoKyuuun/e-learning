<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { BookOpen, Pencil, Trash2, Search, Plus } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    semesters: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

const deleteSemester = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus semester ini?')) {
        router.delete(route('admin.semesters.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('admin.semesters.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Manajemen Semester" />

    <AdminLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Data Semester</h1>
                <p class="text-base-content/60">Kelola periode semester aktif per tahun ajaran.</p>
            </div>
            <Link :href="route('admin.semesters.create')" class="btn btn-primary">
                <Plus class="w-5 h-5 mr-2" /> Tambah Semester
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
                        placeholder="Cari semester atau tahun ajaran..."
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
                            <th>Semester</th>
                            <th>Thn Ajaran</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(sem, index) in semesters.data" :key="sem.id" class="hover">
                            <td>{{ (semesters.current_page - 1) * semesters.per_page + index + 1 }}</td>
                            <td>
                                <div class="font-bold text-base-content">{{ sem.name }}</div>
                                <div class="text-[10px] uppercase font-black opacity-40">{{ sem.code }}</div>
                            </td>
                            <td>
                                <div class="badge badge-neutral badge-outline font-mono text-xs">{{ sem.academic_year.name }}</div>
                            </td>
                            <td class="text-xs">
                                <div>{{ sem.start_date }}</div>
                                <div class="opacity-40">sampai</div>
                                <div>{{ sem.end_date }}</div>
                            </td>
                            <td>
                                <div :class="['badge badge-sm font-bold', sem.status === 'active' ? 'badge-success bg-success/10 text-success border-none' : 'badge-error bg-error/10 text-error border-none']">
                                    {{ sem.status.toUpperCase() }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('admin.semesters.edit', sem.id)" class="btn btn-sm btn-square btn-ghost hover:text-info">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="deleteSemester(sem.id)" class="btn btn-sm btn-square btn-ghost hover:text-error">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="semesters.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50">Belum ada data semester.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="semesters.links" />
        </div>
    </AdminLayout>
</template>
