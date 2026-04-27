<script setup>
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Users, 
    Calendar, 
    BookOpen, 
    Building2,
    Camera,
    Bell
} from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <template #sidebar>
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-4 mb-2">Pusat Kendali</li>
            <li>
                <Link :href="route('admin.dashboard')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/admin/dashboard' }">
                    <LayoutDashboard class="w-5 h-5"/> Dashboard
                </Link>
            </li>
            <li>
                <Link :href="route('announcements.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url === '/announcements' }">
                    <Bell class="w-5 h-5"/> Pengumuman
                </Link>
            </li>
            <li>
                <Link :href="route('admin.users.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/admin/users') }">
                    <Users class="w-5 h-5"/> Manajemen User
                </Link>
            </li>
            <li>
                <Link :href="route('admin.departments.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/admin/departments') }">
                    <Building2 class="w-5 h-5"/> Jurusan / Departemen
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Konfigurasi Akademik</li>
            <li>
                <Link :href="route('admin.academic-years.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/admin/academic-years') }">
                    <Calendar class="w-5 h-5"/> Tahun Ajaran
                </Link>
            </li>
            <li>
                <Link :href="route('admin.semesters.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/admin/semesters') }">
                    <BookOpen class="w-5 h-5"/> Data Semester
                </Link>
            </li>
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Absensi & Wajah</li>
            <li>
                <Link :href="route('admin.face-profiles.index')" :class="{ 'active bg-primary/10 text-primary font-bold': $page.url.startsWith('/admin/face-profiles') }">
                    <Camera class="w-5 h-5"/> Kelola Wajah Siswa
                </Link>
            </li>
        </template>
        <slot />
    </AppLayout>
</template>
