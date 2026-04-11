<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { GraduationCap, Pencil, Search, X } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    teachers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

watch(search, debounce((value) => {
    router.get(route('kajur.teachers.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <Head title="Manajemen Guru" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Data Guru</h1>
            <p class="text-base-content/60">Kelola identitas akademik dan penempatan guru.</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ flash.success }}</span>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="p-4 border-b border-base-200 bg-base-50/50">
                <div class="relative max-w-md">
                    <TextInput 
                        v-model="search"
                        placeholder="Cari nama atau NIP guru..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th class="w-16">No</th>
                            <th>Nama Lengkap</th>
                            <th>NIP / No. Pegawai</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-base-content/80">
                        <tr v-for="(teacher, index) in teachers.data" :key="teacher.id" class="hover">
                            <td>{{ (teachers.current_page - 1) * teachers.per_page + index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span>{{ teacher.user.full_name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div class="font-bold text-base-content">{{ teacher.user.full_name }}</div>
                                </div>
                            </td>
                            <td class="font-mono text-sm">{{ teacher.employee_number || '-' }}</td>
                            <td>
                                <div v-if="teacher.department" class="badge badge-sm badge-outline">{{ teacher.department.name }}</div>
                                <span v-else class="text-xs opacity-40 italic">Belum diset</span>
                            </td>
                            <td>
                                <div :class="['badge badge-sm font-bold', teacher.is_active ? 'badge-success bg-success/10 text-success border-none' : 'badge-error bg-error/10 text-error border-none']">
                                    {{ teacher.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <Link :href="route('kajur.teachers.edit', teacher.id)" class="btn btn-sm btn-square btn-ghost hover:bg-info/10 hover:text-info">
                                    <Pencil class="w-4 h-4" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="teachers.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50 italic">Belum ada data guru.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="teachers.links" />
        </div>
    </KajurLayout>
</template>
