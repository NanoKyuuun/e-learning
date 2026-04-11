<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { School, Pencil, Trash2, Users, Search, Plus } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    classGroups: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const flash = computed(() => usePage().props.flash);

const deleteClass = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
        router.delete(route('kajur.class-groups.destroy', id));
    }
};

watch(search, debounce((value) => {
    router.get(route('kajur.class-groups.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Manajemen Kelas" />

    <KajurLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Manajemen Kelas</h1>
                <p class="text-base-content/60">Kelola data kelas dan daftar siswa di dalamnya.</p>
            </div>
            <Link :href="route('kajur.class-groups.create')" class="btn btn-primary shadow-lg shadow-primary/20">
                <Plus class="w-5 h-5 mr-2" /> Tambah Kelas Baru
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
                        placeholder="Cari nama atau kode kelas..."
                        class="pl-10"
                    />
                    <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th>Kelas</th>
                            <th>Jurusan & TA</th>
                            <th>Wali Kelas</th>
                            <th class="text-center">Siswa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-base-content/80">
                        <tr v-for="item in classGroups.data" :key="item.id" class="hover">
                            <td>
                                <div class="font-black text-lg text-base-content leading-tight">{{ item.name }}</div>
                                <div class="font-mono text-[10px] uppercase opacity-50">{{ item.code }} • Tingkat {{ item.grade_level }}</div>
                            </td>
                            <td>
                                <div class="font-bold text-xs">{{ item.department.name }}</div>
                                <div class="badge badge-ghost badge-xs opacity-60 uppercase">{{ item.academic_year.name }}</div>
                            </td>
                            <td>
                                <div v-if="item.homeroom_teacher" class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8 font-bold">
                                            <span>{{ item.homeroom_teacher.user.full_name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium">{{ item.homeroom_teacher.user.full_name }}</div>
                                </div>
                                <span v-else class="text-xs italic opacity-30">Belum ada wali kelas</span>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-black text-xl">{{ item.enrollments_count }}</span>
                                    <span class="text-[10px] opacity-40 uppercase font-bold">Orang</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('kajur.class-groups.members.index', item.id)" class="btn btn-sm btn-primary gap-2 shadow-sm" title="Kelola Anggota Kelas">
                                        <Users class="w-4 h-4" /> Kelola Siswa
                                    </Link>
                                    <div class="join shadow-sm border border-base-200">
                                        <Link :href="route('kajur.class-groups.edit', item.id)" class="btn btn-sm btn-ghost join-item border-r border-base-200 hover:text-info">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button @click="deleteClass(item.id)" class="btn btn-sm btn-ghost join-item hover:text-error">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="classGroups.data.length === 0">
                            <td colspan="5" class="text-center py-16 opacity-50 italic">
                                Belum ada data kelas untuk ditampilkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <Pagination :links="classGroups.links" />
        </div>
    </KajurLayout>
</template>
