<script setup>
import { computed } from 'vue';
import { usePage, Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KajurLayout from '@/Layouts/KajurLayout.vue';
import GuruLayout from '@/Layouts/GuruLayout.vue';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { User, Shield, Key, Mail, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
    academicProfile: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value.roles[0]?.name);

const Layout = computed(() => {
    if (role.value === 'admin-sistem') return AdminLayout;
    if (role.value === 'kajur') return KajurLayout;
    if (role.value === 'guru') return GuruLayout;
    if (role.value === 'siswa') return SiswaLayout;
    return AdminLayout;
});

const profileForm = useForm({
    full_name: user.value.full_name,
    username: user.value.username || '',
    email: user.value.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'));
};

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <Head title="Profil Saya" />

    <component :is="Layout">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-base-content tracking-tight uppercase">Profil Saya</h1>
            <p class="text-base-content/60 italic text-sm">Kelola informasi akun dan pengaturan keamanan Anda.</p>
        </div>

        <div v-if="$page.props.flash.success" class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold text-sm">
            <CheckCircle class="w-5 h-5" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sisi Kiri: Informasi Akun -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Data Pribadi -->
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="card-body p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="bg-primary/10 p-3 rounded-xl text-primary">
                                <User class="w-6 h-6" />
                            </div>
                            <h2 class="text-xl font-black uppercase tracking-tight">Informasi Pribadi</h2>
                        </div>

                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <TextInput 
                                    label="Nama Lengkap"
                                    v-model="profileForm.full_name"
                                    :error="profileForm.errors.full_name"
                                    required
                                />
                                <TextInput 
                                    label="Username"
                                    v-model="profileForm.username"
                                    :error="profileForm.errors.username"
                                />
                            </div>
                            <TextInput 
                                label="Email"
                                type="email"
                                v-model="profileForm.email"
                                :error="profileForm.errors.email"
                                required
                            />

                            <div class="flex justify-end mt-4">
                                <button class="btn btn-primary px-8 shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-xs" :disabled="profileForm.processing">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Keamanan: Ganti Password -->
                <div class="card bg-base-100 shadow-xl border border-base-200">
                    <div class="card-body p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="bg-error/10 text-error p-3 rounded-xl">
                                <Key class="w-6 h-6" />
                            </div>
                            <h2 class="text-xl font-black uppercase tracking-tight">Keamanan Akun</h2>
                        </div>

                        <form @submit.prevent="updatePassword" class="space-y-6">
                            <TextInput 
                                label="Password Saat Ini"
                                type="password"
                                v-model="passwordForm.current_password"
                                :error="passwordForm.errors.current_password"
                                required
                            />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <TextInput 
                                    label="Password Baru"
                                    type="password"
                                    v-model="passwordForm.password"
                                    :error="passwordForm.errors.password"
                                    required
                                />
                                <TextInput 
                                    label="Konfirmasi Password Baru"
                                    type="password"
                                    v-model="passwordForm.password_confirmation"
                                    required
                                />
                            </div>

                            <div class="flex justify-end mt-4">
                                <button class="btn btn-error text-white px-8 shadow-lg shadow-error/20 font-black uppercase tracking-widest text-xs" :disabled="passwordForm.processing">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Informasi Akademik (Role Specific) -->
            <div class="lg:col-span-1 space-y-8">
                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="bg-primary h-1"></div>
                    <div class="card-body p-6">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-6 text-center">Status Akademik</h3>
                        
                        <div class="flex flex-col items-center mb-8">
                            <div class="avatar placeholder mb-4">
                                <div class="bg-primary text-primary-content rounded-2xl w-20 font-black text-3xl shadow-lg">
                                    <span>{{ user.full_name.charAt(0) }}</span>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="font-black text-lg leading-tight">{{ user.full_name }}</p>
                                <div class="badge badge-primary badge-sm font-black uppercase mt-1 px-3">{{ role.replace('-', ' ') }}</div>
                            </div>
                        </div>

                        <!-- Data Guru -->
                        <div v-if="academicProfile && role === 'guru'" class="space-y-4">
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">NIP / No Pegawai</p>
                                <p class="font-mono text-sm font-bold">{{ academicProfile.employee_number || '-' }}</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">Jurusan</p>
                                <p class="text-sm font-bold">{{ academicProfile.department?.name || 'Umum' }}</p>
                            </div>
                        </div>

                        <!-- Data Siswa -->
                        <div v-if="academicProfile && role === 'siswa'" class="space-y-4">
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">NIS / No Induk</p>
                                <p class="font-mono text-sm font-bold">{{ academicProfile.student_number || '-' }}</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">Kelas Aktif</p>
                                <p class="text-sm font-bold">{{ academicProfile.enrollments[0]?.class_group.name || 'Belum masuk kelas' }}</p>
                            </div>
                        </div>

                        <div v-if="role === 'admin-sistem'" class="bg-info/5 p-6 rounded-3xl border border-info/10 text-center">
                            <Shield class="w-10 h-10 text-info opacity-20 mx-auto mb-3" />
                            <p class="text-xs font-medium opacity-60">Anda memiliki akses penuh untuk mengelola konfigurasi seluruh sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
