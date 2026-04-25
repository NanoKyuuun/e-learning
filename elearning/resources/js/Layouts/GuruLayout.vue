<script setup>
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    BookOpen, 
    Presentation, 
    FileUp, 
    ClipboardList, 
    Inbox, 
    GraduationCap, 
    BarChart3,
    Users
} from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <template #sidebar>
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-4 mb-2">Panel Guru</li>
            <li>
                <Link :href="route('guru.dashboard')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/guru/dashboard' }">
                    <LayoutDashboard class="w-5 h-5"/> Dashboard
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Aktivitas Mengajar</li>
            <li>
                <Link :href="route('guru.courses.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/guru/courses') || $page.url.startsWith('/guru/teaching-assignments') || $page.url.startsWith('/guru/meetings') }">
                    <Presentation class="w-5 h-5"/> Daftar Pengampu
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Evaluasi</li>
            <li>
                <Link :href="route('guru.assignments.all-submissions')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/guru/assignments/submissions' }">
                    <Inbox class="w-5 h-5"/> Submission Tugas
                </Link>
            </li>
            <li>
                <Link :href="route('guru.assignments.grading')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/guru/assignments/grading' }">
                    <GraduationCap class="w-5 h-5"/> Penilaian
                </Link>
            </li>
            <li>
                <Link :href="route('guru.grades.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/guru/grades') }">
                    <BarChart3 class="w-5 h-5"/> Rekap Nilai
                </Link>
            </li>
        </template>
        <slot />
    </AppLayout>
</template>
