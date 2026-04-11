<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { UserPlus, Pencil, Trash2, Search, Plus, BookOpen, School, UserCircle } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    assignments: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const flash = computed(() => usePage().props.flash);

const deleteAssignment = (id) => {
    if (confirm('Hapus plotting pengampu ini?')) {
        router.delete(route('kajur.teaching-assignments.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('kajur.teaching-assignments.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Plotting Pengampu" />

    <KajurLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase text-primary">Plotting Pengampu</h1>
                <p class="text-base-content/60">Hubungkan Guru, Mata Pelajaran, dan Kelas.</p>
            </div>
            <Link :href="route('kajur.teaching-assignments.create')" class="btn btn-primary shadow-lg shadow-primary/20">
                <Plus class="w-5 h-5 mr-2" /> Plotting Baru
            </Link>
        </div>

        <div v-if="flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ flash.success }}</span>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="p-4 border-b border-base-200 bg-base-50/50">
                <div class="relative max-w-md">
                    <TextInput 
                        v-model="search"
                        placeholder="Cari guru, kelas, atau mapel..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70 text-[11px] uppercase tracking-widest font-black">
                            <th class="w-16">No</th>
                            <th>Guru Pengampu</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Semester</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-base-content/80">
                        <tr v-for="(item, index) in assignments.data" :key="item.id" class="hover">
                            <td class="font-mono text-xs opacity-50">{{ (assignments.current_page - 1) * assignments.per_page + index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-xl w-10 font-bold shadow-sm">
                                            <span>{{ item.teacher.user.full_name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-base-content leading-tight">{{ item.teacher.user.full_name }}</div>
                                        <div class="text-[10px] opacity-50 font-mono tracking-tighter">{{ item.teacher.employee_number || 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <BookOpen class="w-4 h-4 opacity-30" />
                                    <div>
                                        <div class="font-bold leading-tight">{{ item.subject.name }}</div>
                                        <div class="text-[10px] opacity-50 font-mono">{{ item.subject.code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <School class="w-4 h-4 opacity-30" />
                                    <div class="font-black text-secondary">{{ item.class_group.name }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-neutral badge-sm font-bold opacity-70">{{ item.semester.name }}</div>
                            </td>
                            <td class="text-center">
                                <button @click="deleteAssignment(item.id)" class="btn btn-sm btn-square btn-ghost hover:bg-error/10 hover:text-error">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="assignments.data.length === 0">
                            <td colspan="6" class="text-center py-12 opacity-50 italic">Belum ada plotting pengampu.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="assignments.links" />
        </div>
    </KajurLayout>
</template>
