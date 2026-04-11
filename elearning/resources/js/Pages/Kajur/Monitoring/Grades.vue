<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Award, Book, Search, School, User, ArrowRight } from 'lucide-vue-next';
import SelectInput from '@/Components/forms/input/SelectInput.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    classes: Array,
    reportData: Object,
    filters: Object,
});

const selectedClass = ref(props.filters.class_id || '');

const classOptions = props.classes.map(c => ({ value: c.id, label: c.name }));

watch(selectedClass, (value) => {
    router.get(route('kajur.monitoring.grades'), { class_id: value }, {
        preserveState: true,
        replace: true,
    });
});

// Helper untuk mengambil nilai rata-rata siswa
const getStudentAverage = (student) => {
    if (!student.submissions || student.submissions.length === 0) return 0;
    
    let total = 0;
    let gradedCount = 0;
    
    student.submissions.forEach(sub => {
        if (sub.grade) {
            total += parseFloat(sub.grade.score);
            gradedCount++;
        }
    });
    
    return gradedCount > 0 ? (total / gradedCount).toFixed(1) : 0;
};
</script>

<template>
    <Head title="Rekap Nilai Siswa" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase text-secondary">Rekap Nilai Siswa</h1>
            <p class="text-base-content/60 font-medium mt-1">Pantau prestasi akademik seluruh siswa per kelas.</p>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 mb-8">
            <div class="card-body p-6">
                <div class="max-w-md">
                    <SelectInput 
                        label="Pilih Kelas untuk Dipantau"
                        v-model="selectedClass"
                        :options="classOptions"
                    />
                </div>
            </div>
        </div>

        <div v-if="reportData" class="space-y-6">
            <div class="alert bg-secondary/5 border-none shadow-sm flex justify-between items-center px-8 py-6 rounded-3xl">
                <div>
                    <h2 class="font-black text-2xl uppercase tracking-tight text-secondary">{{ reportData.name }}</h2>
                    <p class="text-xs font-bold opacity-50 uppercase tracking-widest mt-1">{{ reportData.enrollments.length }} Siswa Terdaftar</p>
                </div>
                <div class="bg-secondary text-secondary-content p-4 rounded-2xl shadow-lg shadow-secondary/20">
                    <Award class="w-8 h-8" />
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full text-base-content/80">
                        <thead>
                            <tr class="bg-base-200/50 text-base-content/70 text-[11px] uppercase tracking-widest font-black">
                                <th class="w-16 text-center">No</th>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th class="text-center">Tugas Selesai</th>
                                <th class="text-center">Rata-rata Nilai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(enrollment, index) in reportData.enrollments" :key="enrollment.id" class="hover group">
                                <td class="text-center font-mono opacity-40 text-xs">{{ index + 1 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-secondary/10 text-secondary rounded-xl w-10 font-bold border border-secondary/20">
                                                <span>{{ enrollment.student.user.full_name.charAt(0) }}</span>
                                            </div>
                                        </div>
                                        <div class="font-bold text-base-content">{{ enrollment.student.user.full_name }}</div>
                                    </div>
                                </td>
                                <td class="font-mono text-sm opacity-50">{{ enrollment.student.student_number }}</td>
                                <td class="text-center">
                                    <span class="font-bold">{{ enrollment.student.submissions.length }}</span>
                                    <span class="text-[10px] opacity-30 ml-1 uppercase">Tugas</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span :class="['text-2xl font-black', getStudentAverage(enrollment.student) >= 75 ? 'text-success' : 'text-error']">
                                            {{ getStudentAverage(enrollment.student) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-ghost btn-sm btn-square hover:bg-secondary/10 hover:text-secondary">
                                        <ArrowRight class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="reportData.enrollments.length === 0">
                                <td colspan="6" class="text-center py-16 opacity-40 italic">
                                    Belum ada siswa di kelas ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-else class="bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-300 shadow-inner">
            <div class="bg-base-200 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <Search class="w-10 h-10 opacity-20" />
            </div>
            <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Silakan Pilih Kelas</h3>
            <p class="opacity-40 text-sm italic mt-2">Pilih salah satu kelas di atas untuk melihat rekapitulasi nilai siswa.</p>
        </div>
    </KajurLayout>
</template>
