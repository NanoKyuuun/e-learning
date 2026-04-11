<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Calendar, Pencil, Trash2, Search, Plus } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    academicYears: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

const deleteAcademicYear = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?')) {
        router.delete(route('admin.academic-years.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('admin.academic-years.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Manajemen Tahun Ajaran" />

    <AdminLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Tahun Ajaran</h1>
                <p class="text-base-content/60">Kelola periode akademik sistem.</p>
            </div>
            <Link :href="route('admin.academic-years.create')" class="btn btn-primary">
                <Plus class="w-5 h-5 mr-2" /> Tambah Tahun Ajaran
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
                        placeholder="Cari tahun ajaran (misal: 2026)..."
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
                            <th>Tahun Ajaran</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(ay, index) in academicYears.data" :key="ay.id" class="hover">
                            <td>{{ (academicYears.current_page - 1) * academicYears.per_page + index + 1 }}</td>
                            <td class="font-bold text-base-content text-lg">{{ ay.name }}</td>
                            <td>{{ ay.start_date }}</td>
                            <td>{{ ay.end_date }}</td>
                            <td>
                                <div :class="[
                                    'badge badge-sm font-bold', 
                                    ay.status === 'active' ? 'badge-success bg-success/10 text-success border-none' : 
                                    (ay.status === 'archived' ? 'badge-neutral bg-base-300 text-base-content border-none' : 'badge-error bg-error/10 text-error border-none')
                                ]">
                                    {{ ay.status.toUpperCase() }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('admin.academic-years.edit', ay.id)" class="btn btn-sm btn-square btn-ghost hover:text-info">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="deleteAcademicYear(ay.id)" class="btn btn-sm btn-square btn-ghost hover:text-error">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="academicYears.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50">Belum ada data tahun ajaran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="academicYears.links" />
        </div>
    </AdminLayout>
</template>
