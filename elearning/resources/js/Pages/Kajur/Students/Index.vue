<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { UsersRound, Pencil, Search, X } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    students: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

watch(search, debounce((value) => {
    router.get(route('kajur.students.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <Head title="Manajemen Siswa" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Data Siswa</h1>
            <p class="text-base-content/60">Kelola identitas akademik dan status aktif siswa.</p>
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
                        placeholder="Cari nama atau NIS siswa..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-base-content/80">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th class="w-16">No</th>
                            <th>Nama Lengkap</th>
                            <th>NIS / No. Induk</th>
                            <th>Kelas Saat Ini</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, index) in students.data" :key="student.id" class="hover">
                            <td>{{ (students.current_page - 1) * students.per_page + index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar" :class="student.user.avatar ? '' : 'placeholder'">
                                        <div v-if="student.user.avatar" class="w-8 h-8 rounded-full shadow-inner overflow-hidden border border-base-200">
                                            <img :src="'/storage/' + student.user.avatar" :alt="student.user.full_name" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center font-bold">
                                            <span>{{ student.user.full_name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                    </div>
                                    <div class="font-bold text-base-content">{{ student.user.full_name }}</div>
                                </div>
                            </td>
                            <td class="font-mono text-sm">{{ student.student_number || '-' }}</td>
                            <td>
                                <div v-if="student.enrollments && student.enrollments.length > 0" class="badge badge-sm badge-primary font-bold">
                                    {{ student.enrollments[0].class_group.name }}
                                </div>
                                <span v-else class="text-xs opacity-40 italic">Belum masuk kelas</span>
                            </td>
                            <td>
                                <div :class="['badge badge-sm font-bold', student.is_active ? 'badge-success bg-success/10 text-success border-none' : 'badge-error bg-error/10 text-error border-none']">
                                    {{ student.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <Link :href="route('kajur.students.edit', student.id)" class="btn btn-sm btn-square btn-ghost hover:text-info">
                                    <Pencil class="w-4 h-4" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="students.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50 italic">Belum ada data siswa.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="students.links" />
        </div>
    </KajurLayout>
</template>
