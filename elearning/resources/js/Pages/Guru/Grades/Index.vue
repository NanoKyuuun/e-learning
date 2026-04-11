<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Award, Book, Search, School, User, ArrowRight, BarChart3 } from 'lucide-vue-next';
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
    router.get(route('guru.grades.index'), { class_id: value }, {
        preserveState: true,
        replace: true,
    });
});

// Helper untuk mengambil nilai rata-rata siswa untuk tugas milik guru ini
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

    <GuruLayout>
        <div class="mb-8 text-base-content">
            <h1 class="text-3xl font-black tracking-tight uppercase text-primary">Rekapitulasi Nilai</h1>
            <p class="text-base-content/60 font-medium mt-1">Pantau nilai akhir siswa berdasarkan kelas pengampu.</p>
        </div>

        <div class="card bg-base-100 shadow-xl border border-base-200 mb-8 overflow-hidden">
            <div class="h-1 bg-primary"></div>
            <div class="card-body p-6">
                <div class="max-w-md">
                    <SelectInput 
                        label="Pilih Kelas Pengampu"
                        v-model="selectedClass"
                        :options="classOptions"
                    />
                </div>
            </div>
        </div>

        <div v-if="reportData" class="space-y-6">
            <div class="alert bg-primary/5 border-none shadow-sm flex justify-between items-center px-8 py-6 rounded-3xl border border-primary/10">
                <div>
                    <h2 class="font-black text-2xl uppercase tracking-tight text-primary">{{ reportData.name }}</h2>
                    <p class="text-xs font-bold opacity-50 uppercase tracking-widest mt-1">{{ reportData.enrollments.length }} Siswa Aktif</p>
                </div>
                <div class="bg-primary text-primary-content p-4 rounded-2xl shadow-lg shadow-primary/20">
                    <BarChart3 class="w-8 h-8" />
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
                                <th class="text-center">Tugas Kumpul</th>
                                <th class="text-center">Rata-rata (Internal)</th>
                                <th class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(enrollment, index) in reportData.enrollments" :key="enrollment.id" class="hover group">
                                <td class="text-center font-mono opacity-40 text-xs">{{ index + 1 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary/10 text-primary rounded-xl w-10 font-bold border border-primary/20">
                                                <span>{{ enrollment.student.user.full_name.charAt(0) }}</span>
                                            </div>
                                        </div>
                                        <div class="font-bold text-sm">{{ enrollment.student.user.full_name }}</div>
                                    </div>
                                </td>
                                <td class="font-mono text-sm opacity-50 uppercase">{{ enrollment.student.student_number }}</td>
                                <td class="text-center">
                                    <span class="font-bold text-lg">{{ enrollment.student.submissions.length }}</span>
                                    <span class="text-[9px] opacity-30 ml-1 uppercase font-black">Tugas</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span :class="['text-2xl font-black', getStudentAverage(enrollment.student) >= 75 ? 'text-success' : 'text-primary']">
                                            {{ getStudentAverage(enrollment.student) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-ghost btn-sm btn-square hover:bg-primary/10 hover:text-primary">
                                        <ArrowRight class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="reportData.enrollments.length === 0">
                                <td colspan="6" class="text-center py-20 opacity-40 italic">
                                    Belum ada siswa di kelas ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-else class="bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-200 shadow-inner">
            <div class="bg-base-200 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <BarChart3 class="w-10 h-10 opacity-20" />
            </div>
            <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Silakan Pilih Kelas</h3>
            <p class="opacity-40 text-sm italic mt-2">Pilih salah satu kelas pengampu Anda untuk melihat rekapitulasi nilai.</p>
        </div>
    </GuruLayout>
</template>
