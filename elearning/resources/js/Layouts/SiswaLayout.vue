<script setup>
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    School, 
    Book, 
    Presentation, 
    FileText, 
    ClipboardCheck, 
    Award, 
    User,
    Bell
} from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <template #sidebar>
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-4 mb-2">Panel Siswa</li>
            <li>
                <Link :href="route('siswa.dashboard')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/siswa/dashboard' }">
                    <LayoutDashboard class="w-5 h-5"/> Dashboard
                </Link>
            </li>
            <li>
                <Link :href="route('announcements.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/announcements' }">
                    <Bell class="w-5 h-5"/> Pengumuman
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Akademik Saya</li>
            <li>
                <Link :href="route('siswa.subjects.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/siswa/subjects' || $page.url.startsWith('/siswa/meetings') }">
                    <Book class="w-5 h-5"/> Mata Pelajaran
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Evaluasi & Hasil</li>
            <li>
                <Link :href="route('siswa.grades.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/siswa/grades' }">
                    <Award class="w-5 h-5"/> Nilai & Feedback
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Profil</li>
            <li>
                <Link :href="route('profile.edit')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/profile' }">
                    <User class="w-5 h-5"/> Data Diri
                </Link>
            </li>
        </template>
        <slot />
    </AppLayout>
</template>
