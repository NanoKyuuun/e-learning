<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Megaphone, Send, Save } from 'lucide-vue-next';

const form = useForm({
    title:       '',
    body:        '',
    target_role: 'all',
    status:      'draft',
    start_at:    '',
    end_at:      '',
});

const targetOptions = [
    { value: 'all',          label: 'Semua Pengguna' },
    { value: 'siswa',        label: 'Siswa' },
    { value: 'guru',         label: 'Guru' },
    { value: 'admin-sistem', label: 'Admin Sistem' },
    { value: 'kajur',        label: 'Kajur' },
];

const submit = (publishNow = false) => {
    if (publishNow) form.status = 'published';
    form.post(route('kajur.announcements.store'));
};
</script>

<template>
    <Head title="Tambah Pengumuman" />
    <KajurLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <Link :href="route('kajur.announcements.index')"
                      class="btn btn-ghost btn-sm gap-2 mb-4">
                    <ArrowLeft class="w-4 h-4" /> Kembali ke Daftar
                </Link>
                <div class="flex items-center gap-4">
                    <div class="bg-primary/10 text-primary w-12 h-12 rounded-2xl flex items-center justify-center">
                        <Megaphone class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">Tambah Pengumuman</h1>
                        <p class="text-sm opacity-60">Buat pengumuman baru untuk pengguna sistem.</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit(false)"
                  class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
                <div class="p-8 space-y-6">

                    <!-- Judul -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold opacity-70">Judul Pengumuman</span></label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Masukkan judul yang jelas dan deskriptif..."
                            class="input input-bordered w-full rounded-xl"
                            :class="{ 'input-error': form.errors.title }"
                        />
                        <label v-if="form.errors.title" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.title }}</span>
                        </label>
                    </div>

                    <!-- Isi -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold opacity-70">Isi Pengumuman</span></label>
                        <textarea
                            v-model="form.body"
                            placeholder="Tulis isi pengumuman secara lengkap di sini..."
                            rows="8"
                            class="textarea textarea-bordered w-full rounded-xl resize-y leading-relaxed"
                            :class="{ 'textarea-error': form.errors.body }"
                        ></textarea>
                        <label v-if="form.errors.body" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.body }}</span>
                        </label>
                    </div>

                    <!-- Target & Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold opacity-70">Target Penerima</span></label>
                            <select v-model="form.target_role"
                                    class="select select-bordered w-full rounded-xl"
                                    :class="{ 'select-error': form.errors.target_role }">
                                <option v-for="opt in targetOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <label v-if="form.errors.target_role" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.target_role }}</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold opacity-70">Status Awal</span></label>
                            <select v-model="form.status"
                                    class="select select-bordered w-full rounded-xl"
                                    :class="{ 'select-error': form.errors.status }">
                                <option value="draft">Draft (Belum Tampil)</option>
                                <option value="published">Published (Langsung Tampil)</option>
                            </select>
                            <label v-if="form.errors.status" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.status }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Periode -->
                    <div class="bg-base-200/50 rounded-2xl p-4 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-widest opacity-50">Periode Tampil (Opsional)</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold opacity-70">Mulai Tampil</span></label>
                                <input v-model="form.start_at" type="datetime-local"
                                       class="input input-bordered w-full rounded-xl"
                                       :class="{ 'input-error': form.errors.start_at }" />
                                <label v-if="form.errors.start_at" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.start_at }}</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-bold opacity-70">Selesai Tampil</span></label>
                                <input v-model="form.end_at" type="datetime-local"
                                       class="input input-bordered w-full rounded-xl"
                                       :class="{ 'input-error': form.errors.end_at }" />
                                <label v-if="form.errors.end_at" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.end_at }}</span>
                                </label>
                            </div>
                        </div>
                        <p class="text-xs opacity-40 italic">
                            Jika periode tidak diisi, pengumuman akan tampil selama status masih Published.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-8 py-5 bg-base-200/40 border-t border-base-200">
                    <Link :href="route('kajur.announcements.index')" class="btn btn-ghost w-full sm:w-auto">
                        Batal
                    </Link>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="btn btn-outline flex-1 sm:flex-none gap-2"
                                :disabled="form.processing">
                            <Save class="w-4 h-4" /> Simpan Draft
                        </button>
                        <button type="button" @click="submit(true)"
                                class="btn btn-primary flex-1 sm:flex-none gap-2 shadow-lg shadow-primary/20"
                                :disabled="form.processing">
                            <Send class="w-4 h-4" /> Publish Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </KajurLayout>
</template>
