<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Book, Pencil, Trash2, Search, Plus } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    subjects: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

const deleteSubject = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?')) {
        router.delete(route('kajur.subjects.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('kajur.subjects.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Manajemen Mata Pelajaran" />

    <KajurLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Mata Pelajaran</h1>
                <p class="text-base-content/60">Kelola daftar mata pelajaran yang diajarkan.</p>
            </div>
            <Link :href="route('kajur.subjects.create')" class="btn btn-primary shadow-lg shadow-primary/20">
                <Plus class="w-5 h-5 mr-2" /> Tambah Mapel
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
                        placeholder="Cari nama atau kode mapel..."
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
                            <th>Kode Mapel</th>
                            <th>Nama Pelajaran</th>
                            <th>Jurusan</th>
                            <th>Level</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(subject, index) in subjects.data" :key="subject.id" class="hover">
                            <td>{{ (subjects.current_page - 1) * subjects.per_page + index + 1 }}</td>
                            <td class="font-mono font-bold text-primary">{{ subject.code }}</td>
                            <td>
                                <div class="font-bold text-base-content">{{ subject.name }}</div>
                                <div class="text-xs opacity-50 line-clamp-1">{{ subject.description || 'Tidak ada deskripsi' }}</div>
                            </td>
                            <td>
                                <div v-if="subject.department" class="badge badge-sm badge-outline">{{ subject.department.name }}</div>
                                <div v-else class="badge badge-sm badge-ghost opacity-50 italic text-[10px]">Umum</div>
                            </td>
                            <td>
                                <div class="badge badge-primary badge-sm font-bold">Tingkat {{ subject.grade_level || '-' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('kajur.subjects.edit', subject.id)" class="btn btn-sm btn-square btn-ghost hover:text-info">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="deleteSubject(subject.id)" class="btn btn-sm btn-square btn-ghost hover:text-error">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="subjects.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50">Belum ada data mata pelajaran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="subjects.links" />
        </div>
    </KajurLayout>
</template>
