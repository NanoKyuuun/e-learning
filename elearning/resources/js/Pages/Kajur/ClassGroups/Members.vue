<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Users, UserPlus, Trash2, Search, ArrowLeft, CheckCircle } from 'lucide-vue-next';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    classGroup: Object,
    members: Array,
    availableStudents: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedStudents = ref([]);

const form = useForm({
    student_ids: [],
});

watch(search, debounce((value) => {
    router.get(route('kajur.class-groups.members.index', props.classGroup.id), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const toggleSelection = (id) => {
    const index = selectedStudents.value.indexOf(id);
    if (index > -1) {
        selectedStudents.value.splice(index, 1);
    } else {
        selectedStudents.value.push(id);
    }
};

const enroll = () => {
    if (selectedStudents.value.length === 0) return;
    
    form.student_ids = selectedStudents.value;
    form.post(route('kajur.class-groups.members.store', props.classGroup.id), {
        onSuccess: () => {
            selectedStudents.value = [];
        }
    });
};

const removeStudent = (enrollmentId) => {
    if (confirm('Keluarkan siswa ini dari kelas?')) {
        router.delete(route('kajur.class-enrollments.destroy', enrollmentId));
    }
};
</script>

<template>
    <Head :title="'Anggota Kelas - ' + classGroup.name" />

    <KajurLayout>
        <div class="mb-8">
            <Link :href="route('kajur.class-groups.index')" class="btn btn-ghost btn-sm gap-2 mb-4">
                <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar Kelas
            </Link>
            <div class="flex items-center gap-4">
                <div class="bg-primary text-primary-content p-3 rounded-2xl">
                    <Users class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Anggota Kelas</h1>
                    <p class="text-base-content/60">{{ classGroup.name }} • {{ classGroup.academic_year.name }}</p>
                </div>
            </div>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold">
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Daftar Anggota Saat Ini -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="p-6 border-b border-base-200 flex justify-between items-center">
                        <h2 class="font-bold text-xl flex items-center gap-2 text-primary">
                            <Users class="w-5 h-5" /> Daftar Siswa <span class="badge badge-primary">{{ members.length }}</span>
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="w-16">No</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(member, index) in members" :key="member.id" class="hover">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="font-bold">{{ member.student.user.full_name }}</div>
                                    </td>
                                    <td class="font-mono text-sm opacity-60">{{ member.student.student_number }}</td>
                                    <td class="text-center">
                                        <button @click="removeStudent(member.id)" class="btn btn-ghost btn-xs text-error">
                                            <Trash2 class="w-4 h-4" /> Keluarkan
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="members.length === 0">
                                    <td colspan="4" class="text-center py-12 opacity-40 italic font-medium">
                                        Belum ada siswa di kelas ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tambah Siswa Baru -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl border border-base-200 sticky top-24">
                    <div class="p-6 border-b border-base-200">
                        <h2 class="font-bold text-xl flex items-center gap-2 text-secondary">
                            <UserPlus class="w-5 h-5" /> Tambah Siswa
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="relative mb-4">
                            <TextInput 
                                v-model="search"
                                placeholder="Cari NIS atau Nama..."
                                class="pl-10"
                            />
                            <Search class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 opacity-30" />
                        </div>

                        <div class="max-h-[400px] overflow-y-auto space-y-2 px-1">
                            <div 
                                v-for="student in availableStudents" 
                                :key="student.id"
                                @click="toggleSelection(student.id)"
                                :class="[
                                    'p-3 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between',
                                    selectedStudents.includes(student.id) 
                                        ? 'border-primary bg-primary/5 shadow-md shadow-primary/10' 
                                        : 'border-base-200 hover:border-base-300'
                                ]"
                            >
                                <div>
                                    <div class="font-bold text-sm">{{ student.user.full_name }}</div>
                                    <div class="text-[10px] opacity-50 font-mono tracking-tighter">{{ student.student_number }}</div>
                                </div>
                                <CheckCircle v-if="selectedStudents.includes(student.id)" class="w-5 h-5 text-primary" />
                            </div>
                            
                            <div v-if="availableStudents.length === 0" class="text-center py-8 opacity-40 italic text-sm">
                                Tidak ada siswa tersedia.
                            </div>
                        </div>

                        <div class="mt-6 border-t pt-4">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold opacity-60 uppercase">{{ selectedStudents.length }} Terpilih</span>
                                <button v-if="selectedStudents.length > 0" @click="selectedStudents = []" class="btn btn-ghost btn-xs">Reset</button>
                            </div>
                            <button 
                                @click="enroll"
                                class="btn btn-primary btn-block shadow-lg shadow-primary/20"
                                :disabled="selectedStudents.length === 0 || form.processing"
                            >
                                <UserPlus class="w-4 h-4 mr-2" /> Masukkan ke Kelas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </KajurLayout>
</template>
