<script setup>
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    School, 
    Book, 
    UserPlus, 
    Users, 
    MonitorCheck, 
    FileText,
    GraduationCap,
    UsersRound,
    UserCheck,
    Bell,
    Megaphone
} from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <template #sidebar>
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-4 mb-2">Manajemen Akademik</li>
            <li>
                <Link :href="route('kajur.dashboard')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/kajur/dashboard' }">
                    <LayoutDashboard class="w-5 h-5"/> Dashboard
                </Link>
            </li>

            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Pengumuman</li>
            <li>
                <Link :href="route('kajur.announcements.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/announcements') }">
                    <Megaphone class="w-5 h-5"/> Kelola Pengumuman
                </Link>
            </li>
            <li>
                <Link :href="route('announcements.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/announcements' }">
                    <Bell class="w-5 h-5"/> Lihat Pengumuman
                </Link>
            </li>
            <li>
                <Link :href="route('kajur.subjects.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/subjects') }">
                    <Book class="w-5 h-5"/> Mata Pelajaran
                </Link>
            </li>
            <li>
                <Link :href="route('kajur.class-groups.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/class-groups') }">
                    <School class="w-5 h-5"/> Manajemen Kelas
                </Link>
            </li>
            <li>
                <Link :href="route('kajur.teaching-assignments.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/teaching-assignments') }">
                    <UserCheck class="w-5 h-5"/> Plotting Pengampu
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">SDM Jurusan</li>
            <li>
                <Link :href="route('kajur.teachers.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/teachers') }">
                    <GraduationCap class="w-5 h-5"/> Data Guru
                </Link>
            </li>
            <li>
                <Link :href="route('kajur.students.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/students') }">
                    <UsersRound class="w-5 h-5"/> Data Siswa
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Monitoring</li>
            <li>
                <Link :href="route('kajur.monitoring.progress')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/kajur/monitoring/progress') }">
                    <MonitorCheck class="w-5 h-5"/> Progres Pembelajaran
                </Link>
            </li>
            <li>
                <Link :href="route('kajur.monitoring.grades')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/kajur/monitoring/grades' }">
                    <FileText class="w-5 h-5"/> Rekap Nilai
                </Link>
            </li>
        </template>
        <slot />
    </AppLayout>
</template>
