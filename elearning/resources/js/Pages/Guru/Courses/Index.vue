<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, ArrowRight } from 'lucide-vue-next';

defineProps({
    teachingAssignments: Array,
});
</script>

<template>
    <Head title="Daftar Pengampu" />

    <GuruLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase text-primary">Mata Pelajaran Saya</h1>
            <p class="text-base-content/60 font-medium mt-1">Daftar kelas dan mata pelajaran yang Anda ampu semester ini.</p>
        </div>

        <div v-if="teachingAssignments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="item in teachingAssignments" :key="item.id" class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary/40 transition-all group overflow-hidden">
                <div class="h-2 bg-primary"></div>
                <div class="card-body p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="badge badge-primary font-black px-3 uppercase text-[10px]">{{ item.class_group.name }}</div>
                        <div class="text-[10px] opacity-40 font-mono tracking-tighter">{{ item.subject.code }}</div>
                    </div>
                    <h3 class="card-title text-xl font-black text-base-content mb-6 group-hover:text-primary transition-colors leading-tight">
                        {{ item.subject.name }}
                    </h3>
                    <div class="card-actions mt-auto pt-4 border-t border-base-50">
                        <Link :href="route('guru.meetings.index', item.id)" class="btn btn-primary btn-block shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-xs">
                            Kelola Pembelajaran <ArrowRight class="w-4 h-4 ml-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="bg-base-100 p-20 text-center rounded-[2.5rem] border-2 border-dashed border-base-300 shadow-inner">
            <div class="bg-base-200 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <BookOpen class="w-10 h-10 opacity-20" />
            </div>
            <h3 class="text-xl font-black text-base-content opacity-60 uppercase tracking-widest">Belum Ada Tugas Mengajar</h3>
            <p class="opacity-40 text-sm italic mt-2">Hubungi Kajur untuk plotting jadwal mengajar Anda.</p>
        </div>
    </GuruLayout>
</template>
