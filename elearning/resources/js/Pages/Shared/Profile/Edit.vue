<script setup>
import { computed, ref } from 'vue';
import { usePage, Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KajurLayout from '@/Layouts/KajurLayout.vue';
import GuruLayout from '@/Layouts/GuruLayout.vue';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import TextInput from '@/Components/forms/input/TextInput.vue';
import { User, Shield, Key, CheckCircle, Camera, Upload, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    academicProfile: Object,
});

const page = usePage();
const user  = computed(() => page.props.auth.user);
const role  = computed(() => user.value.roles[0]?.name);

const Layout = computed(() => {
    if (role.value === 'admin-sistem') return AdminLayout;
    if (role.value === 'kajur')        return KajurLayout;
    if (role.value === 'guru')         return GuruLayout;
    if (role.value === 'siswa')        return SiswaLayout;
    return AdminLayout;
});

const profileForm = useForm({
    full_name: user.value.full_name,
    username:  user.value.username || '',
    email:     user.value.email,
});

const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

// ─── Avatar ─────────────────────────────────────────────────────────────────
const avatarForm    = useForm({ avatar: null });
const avatarPreview = ref(null);
const avatarInput   = ref(null);

const avatarUrl = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (user.value.avatar)   return '/storage/' + user.value.avatar;
    return null;
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar   = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar.update'), {
        forceFormData: true,
        onSuccess: () => {
            avatarPreview.value = null;
            avatarForm.reset();
        },
    });
};

const cancelAvatarPreview = () => {
    avatarPreview.value = null;
    avatarForm.reset();
    if (avatarInput.value) avatarInput.value.value = '';
};
// ─────────────────────────────────────────────────────────────────────────────

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

        <div v-if="$page.props.flash.success"
             class="alert alert-success mb-6 shadow-sm border-none bg-success/10 text-success font-bold text-sm">
            <CheckCircle class="w-5 h-5" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sisi Kiri: Form -->
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
                                <button class="btn btn-primary px-8 shadow-lg shadow-primary/20 font-black uppercase tracking-widest text-xs"
                                        :disabled="profileForm.processing">
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
                                <button class="btn btn-error text-white px-8 shadow-lg shadow-error/20 font-black uppercase tracking-widest text-xs"
                                        :disabled="passwordForm.processing">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Avatar + Informasi Akademik -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Card Foto Profil -->
                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary via-secondary to-accent h-1.5"></div>
                    <div class="card-body p-6">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-6 text-center">
                            Foto Profil
                        </h3>

                        <div class="flex flex-col items-center gap-4 mb-4">
                            <div class="relative group">
                                <!-- Foto -->
                                <div v-if="avatarUrl"
                                     class="w-28 h-28 rounded-2xl overflow-hidden shadow-lg border-4 border-base-200">
                                    <img :src="avatarUrl" alt="Foto profil"
                                         class="w-full h-full object-cover" />
                                </div>
                                <!-- Fallback inisial -->
                                <div v-else
                                     class="w-28 h-28 rounded-2xl bg-primary text-primary-content font-black text-4xl shadow-lg flex items-center justify-center border-4 border-base-200">
                                    {{ user.full_name.charAt(0).toUpperCase() }}
                                </div>

                                <!-- Overlay kamera saat hover -->
                                <label for="avatar-input"
                                       class="absolute inset-0 rounded-2xl flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <Camera class="w-7 h-7 text-white" />
                                </label>
                            </div>

                            <div class="text-center">
                                <p class="font-black text-base leading-tight">{{ user.full_name }}</p>
                                <div class="badge badge-primary badge-sm font-black uppercase mt-1 px-3">
                                    {{ role?.replace('-', ' ') }}
                                </div>
                            </div>
                        </div>

                        <!-- Input file (hidden) -->
                        <input ref="avatarInput" id="avatar-input" type="file"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="hidden" @change="onAvatarChange" />

                        <!-- Tombol pilih foto -->
                        <label v-if="!avatarPreview" for="avatar-input"
                               class="btn btn-outline btn-sm w-full gap-2 cursor-pointer">
                            <Upload class="w-4 h-4" /> Ganti Foto Profil
                        </label>

                        <!-- Preview baru: simpan / batal -->
                        <div v-if="avatarPreview" class="flex flex-col gap-2 mt-1">
                            <p class="text-xs text-center opacity-50 italic">Preview foto baru</p>
                            <button @click="submitAvatar"
                                    class="btn btn-primary btn-sm w-full gap-2"
                                    :disabled="avatarForm.processing">
                                <Upload class="w-4 h-4" />
                                {{ avatarForm.processing ? 'Mengunggah...' : 'Simpan Foto' }}
                            </button>
                            <button @click="cancelAvatarPreview"
                                    class="btn btn-ghost btn-sm w-full gap-2 text-error">
                                <Trash2 class="w-4 h-4" /> Batal
                            </button>
                        </div>

                        <p v-if="avatarForm.errors.avatar"
                           class="text-error text-xs text-center mt-1">
                            {{ avatarForm.errors.avatar }}
                        </p>
                        <p class="text-xs opacity-30 text-center mt-2">JPG, PNG, WEBP · Maks. 2MB</p>
                    </div>
                </div>

                <!-- Status Akademik -->
                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="bg-primary h-1"></div>
                    <div class="card-body p-6">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-4 text-center">
                            Status Akademik
                        </h3>

                        <!-- Data Guru -->
                        <div v-if="academicProfile && role === 'guru'" class="space-y-3">
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">NIP / No Pegawai</p>
                                <p class="font-mono text-sm font-bold">{{ academicProfile.employee_number || '— Belum diisi' }}</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">Jurusan</p>
                                <p class="text-sm font-bold">{{ academicProfile.department?.name || 'Umum' }}</p>
                            </div>
                        </div>

                        <!-- Data Siswa -->
                        <div v-else-if="academicProfile && role === 'siswa'" class="space-y-3">
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">NIS / No Induk</p>
                                <p class="font-mono text-sm font-bold">{{ academicProfile.student_number || '— Belum diisi' }}</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-2xl border border-base-300 shadow-inner">
                                <p class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">Kelas Aktif</p>
                                <p class="text-sm font-bold">{{ academicProfile.enrollments[0]?.class_group.name || 'Belum masuk kelas' }}</p>
                            </div>
                        </div>

                        <div v-else-if="role === 'admin-sistem'" class="bg-info/5 p-6 rounded-3xl border border-info/10 text-center">
                            <Shield class="w-10 h-10 text-info opacity-20 mx-auto mb-3" />
                            <p class="text-xs font-medium opacity-60">Anda memiliki akses penuh untuk mengelola konfigurasi seluruh sistem.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </component>
</template>
