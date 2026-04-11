<script setup>
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Users, 
    ShieldCheck, 
    Calendar, 
    BookOpen, 
    RotateCcw, 
    History, 
    Settings,
    Building2
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
            
            <li class="menu-title uppercase text-[10px] font-black opacity-40 tracking-widest mt-6 mb-2">Keamanan & Sistem</li>
            <li><Link href="#"><ShieldCheck class="w-5 h-5"/> Role & Permission</Link></li>
            <li><Link href="#"><RotateCcw class="w-5 h-5"/> Reset Password</Link></li>
            <li><Link href="#"><History class="w-5 h-5"/> Log Aktivitas</Link></li>
            <li><Link href="#"><Settings class="w-5 h-5"/> Pengaturan Sistem</Link></li>
        </template>
        <slot />
    </AppLayout>
</template>
