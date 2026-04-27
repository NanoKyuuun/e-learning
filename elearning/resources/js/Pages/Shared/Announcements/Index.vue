<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import GuruLayout from '@/Layouts/GuruLayout.vue';
import KajurLayout from '@/Layouts/KajurLayout.vue';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bell, Megaphone, CalendarDays, User2, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    announcements: Object,
});

const page = usePage();

const role = computed(() => page.props.auth?.user?.roles?.[0]?.name ?? '');

const SelectedLayout = computed(() => {
    if (role.value === 'admin-sistem') return AdminLayout;
    if (role.value === 'guru')         return GuruLayout;
    if (role.value === 'kajur')        return KajurLayout;
    return SiswaLayout;
});

const targetLabel = {
    all:          'Semua Pengguna',
    siswa:        'Siswa',
    guru:         'Guru',
    'admin-sistem': 'Admin Sistem',
    kajur:        'Kajur',
};

const formatDate = (val) => {
    if (!val) return null;
    return new Date(val).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
};
</script>

<template>
    <Head title="Pengumuman" />
    <component :is="SelectedLayout">
        <div class="space-y-8 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <div class="bg-primary/10 text-primary w-14 h-14 rounded-2xl flex items-center justify-center">
                    <Megaphone class="w-7 h-7" />
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Pengumuman</h1>
                    <p class="text-sm opacity-60 mt-0.5">Informasi terbaru dari jurusan dan pengelola akademik.</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="announcements.data.length === 0"
                 class="bg-base-100 rounded-3xl border-2 border-dashed border-base-300 p-16 text-center">
                <Bell class="w-16 h-16 mx-auto opacity-20 mb-4" />
                <h2 class="font-black text-xl opacity-40">Belum Ada Pengumuman</h2>
                <p class="text-sm opacity-30 mt-1">Pengumuman yang aktif akan tampil di halaman ini.</p>
            </div>

            <!-- List -->
            <div v-else class="space-y-4">
                <div v-for="ann in announcements.data" :key="ann.id"
                     class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">

                    <!-- Accent bar -->
                    <div class="h-1.5 bg-gradient-to-r from-primary via-secondary to-accent w-full"></div>

                    <div class="p-6 md:p-8">
                        <!-- Meta header -->
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="badge badge-primary badge-outline badge-sm font-bold">
                                {{ targetLabel[ann.target_role] ?? ann.target_role }}
                            </span>
                            <span v-if="ann.start_at" class="flex items-center gap-1 text-xs opacity-50">
                                <CalendarDays class="w-3 h-3" />
                                {{ formatDate(ann.start_at) }}
                                <template v-if="ann.end_at">
                                    – {{ formatDate(ann.end_at) }}
                                </template>
                            </span>
                        </div>

                        <!-- Judul -->
                        <h2 class="text-xl font-black text-base-content tracking-tight leading-snug">
                            {{ ann.title }}
                        </h2>

                        <!-- Isi -->
                        <div class="mt-4 text-base-content/75 leading-relaxed whitespace-pre-line text-sm md:text-base">
                            {{ ann.body }}
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-base-200">
                            <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                <User2 class="w-4 h-4" />
                            </div>
                            <div>
                                <span class="text-xs font-bold opacity-60">
                                    Dibuat oleh {{ ann.creator?.full_name ?? 'Kajur' }}
                                </span>
                                <span class="text-xs opacity-30 ml-2">·</span>
                                <span class="text-xs opacity-40 ml-2">
                                    {{ formatDate(ann.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="announcements.last_page > 1"
                 class="flex justify-center gap-1">
                <template v-for="link in announcements.links" :key="link.label">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        class="btn btn-sm"
                        :class="link.active ? 'btn-primary' : 'btn-ghost'"
                        v-html="link.label"
                    />
                    <span v-else
                          class="btn btn-sm btn-disabled"
                          v-html="link.label" />
                </template>
            </div>
        </div>
    </component>
</template>
