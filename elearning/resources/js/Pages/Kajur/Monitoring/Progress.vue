<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { MonitorCheck, School, ArrowRight, User, Calendar } from 'lucide-vue-next';

defineProps({
    classes: Array,
});
</script>

<template>
    <Head title="Monitoring Progres Pembelajaran" />

    <KajurLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase text-primary">Progres Pembelajaran</h1>
            <p class="text-base-content/60 font-medium mt-1">Pantau ketercapaian materi di setiap kelas jurusannya.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="item in classes" :key="item.id" class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary/40 transition-all group">
                <div class="card-body p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-primary/10 text-primary p-3 rounded-2xl">
                            <School class="w-6 h-6" />
                        </div>
                        <div class="badge badge-ghost badge-sm font-bold uppercase opacity-50">{{ item.academic_year.name }}</div>
                    </div>
                    
                    <h2 class="card-title text-2xl font-black text-base-content uppercase leading-tight group-hover:text-primary transition-colors">
                        {{ item.name }}
                    </h2>
                    
                    <div class="space-y-2 mt-4">
                        <div class="flex items-center gap-2 text-xs font-bold opacity-60 uppercase tracking-widest">
                            <User class="w-3.5 h-3.5" /> {{ item.homeroom_teacher?.user.full_name || 'Tanpa Wali Kelas' }}
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-mono opacity-40">
                            {{ item.department.name }} • Tingkat {{ item.grade_level }}
                        </div>
                    </div>

                    <div class="card-actions mt-8 pt-4 border-t border-base-100">
                        <Link :href="route('kajur.monitoring.class-detail', item.id)" class="btn btn-primary btn-block shadow-lg shadow-primary/20 gap-2 font-black uppercase tracking-widest">
                            Lihat Detail Progres <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="classes.length === 0" class="col-span-full bg-base-100 p-20 text-center rounded-[3rem] border-2 border-dashed border-base-200 opacity-40 italic">
                Belum ada data kelas di jurusan ini.
            </div>
        </div>
    </KajurLayout>
</template>
