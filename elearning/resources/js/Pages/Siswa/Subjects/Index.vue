<script setup>
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Book, GraduationCap, ArrowRight, School } from 'lucide-vue-next';

defineProps({
    subjects: Array,
    classGroup: Object,
});
</script>

<template>
    <Head title="Mata Pelajaran Saya" />

    <SiswaLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase text-primary">Mata Pelajaran</h1>
            <p v-if="classGroup" class="text-base-content/60 font-medium mt-1">Daftar pelajaran kamu di kelas <span class="badge badge-primary font-bold uppercase">{{ classGroup.name }}</span>.</p>
        </div>

        <div v-if="subjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="item in subjects" :key="item.id" class="card bg-base-100 shadow-xl border border-base-200 hover:shadow-2xl transition-all group overflow-hidden">
                <div class="h-2 bg-primary"></div>
                <div class="card-body p-6">
                    <h3 class="card-title text-xl font-black text-base-content mb-1 group-hover:text-primary transition-colors leading-tight">
                        {{ item.subject.name }}
                    </h3>
                    <p class="text-xs opacity-50 mb-6 flex items-center gap-2 font-bold uppercase tracking-widest">
                        <GraduationCap class="w-4 h-4 text-primary" /> {{ item.teacher.user.full_name }}
                    </p>
                    <div class="card-actions mt-auto pt-4 border-t border-base-50">
                        <Link :href="route('siswa.meetings.index', item.id)" class="btn btn-primary btn-block shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-xs">
                            Buka Materi <ArrowRight class="w-4 h-4 ml-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-300 shadow-inner">
            <div class="bg-base-200 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <School class="w-10 h-10 opacity-20" />
            </div>
            <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Belum Terdaftar di Kelas</h3>
            <p class="opacity-40 text-sm italic mt-2">Mata pelajaran akan muncul setelah kamu dimasukkan ke dalam kelas oleh Kajur.</p>
        </div>
    </SiswaLayout>
</template>
