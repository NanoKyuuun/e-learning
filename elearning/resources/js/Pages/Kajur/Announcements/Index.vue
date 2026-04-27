<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Pencil, Trash2, Bell, Search, Megaphone, CalendarClock, Users2, Eye } from 'lucide-vue-next';

const props = defineProps({
    announcements: Object,
    filters:       Object,
});

const search = ref(props.filters?.search || '');

watch(search, (value) => {
    router.get(route('kajur.announcements.index'), { search: value }, {
        preserveState: true,
        replace:       true,
    });
});

const destroyAnnouncement = (announcement) => {
    if (confirm(`Hapus pengumuman "${announcement.title}"?`)) {
        router.delete(route('kajur.announcements.destroy', announcement.id));
    }
};

const statusConfig = {
    published: { label: 'Published', cls: 'badge-success' },
    draft:     { label: 'Draft',     cls: 'badge-warning' },
};

const targetConfig = {
    all:          { label: 'Semua',       cls: 'badge-primary' },
    siswa:        { label: 'Siswa',       cls: 'badge-info' },
    guru:         { label: 'Guru',        cls: 'badge-secondary' },
    'admin-sistem': { label: 'Admin',     cls: 'badge-accent' },
    kajur:        { label: 'Kajur',       cls: 'badge-neutral' },
};

const formatDate = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Kelola Pengumuman" />
    <KajurLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-primary/10 text-primary w-14 h-14 rounded-2xl flex items-center justify-center">
                        <Megaphone class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-base-content tracking-tight">Kelola Pengumuman</h1>
                        <p class="text-sm opacity-60 mt-0.5">Buat dan kelola pengumuman akademik untuk pengguna sistem.</p>
                    </div>
                </div>
                <Link :href="route('kajur.announcements.create')"
                      class="btn btn-primary gap-2 shadow-lg shadow-primary/20">
                    <Plus class="w-4 h-4" /> Tambah Pengumuman
                </Link>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="alert alert-success border-none bg-success/10 text-success font-bold shadow-sm">
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <!-- Tabel -->
            <div class="bg-base-100 rounded-3xl border border-base-200 shadow-sm overflow-hidden">
                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-6 border-b border-base-200">
                    <div class="relative w-full sm:w-72">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-40" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul atau isi pengumuman..."
                            class="input input-bordered input-sm w-full pl-9 rounded-xl"
                        />
                    </div>
                    <div class="text-xs font-bold opacity-40 uppercase tracking-widest">
                        {{ announcements.total }} pengumuman
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="text-xs uppercase tracking-widest opacity-50">
                                <th>Judul</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Periode</th>
                                <th>Dibuat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ann in announcements.data" :key="ann.id"
                                class="hover:bg-base-200/40 transition-colors">
                                <td>
                                    <div class="font-bold text-base-content leading-tight max-w-xs">
                                        {{ ann.title }}
                                    </div>
                                    <div class="text-xs opacity-50 mt-1 line-clamp-1 italic">
                                        {{ ann.body }}
                                    </div>
                                </td>
                                <td>
                                    <span :class="['badge badge-sm font-bold', targetConfig[ann.target_role]?.cls ?? 'badge-ghost']">
                                        {{ targetConfig[ann.target_role]?.label ?? ann.target_role }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['badge badge-sm font-bold', statusConfig[ann.status]?.cls ?? 'badge-ghost']">
                                        {{ statusConfig[ann.status]?.label ?? ann.status }}
                                    </span>
                                </td>
                                <td class="text-xs space-y-0.5">
                                    <div class="flex items-center gap-1 opacity-60">
                                        <CalendarClock class="w-3 h-3" />
                                        <span>{{ formatDate(ann.start_at) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-60">
                                        <span class="opacity-40">s/d</span>
                                        <span>{{ formatDate(ann.end_at) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm font-medium">{{ ann.creator?.full_name ?? '—' }}</div>
                                    <div class="text-xs opacity-40">{{ formatDate(ann.created_at) }}</div>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Link :href="route('kajur.announcements.edit', ann.id)"
                                              class="btn btn-ghost btn-sm btn-square text-primary tooltip"
                                              data-tip="Edit">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button @click="destroyAnnouncement(ann)"
                                                class="btn btn-ghost btn-sm btn-square text-error tooltip"
                                                data-tip="Hapus">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="announcements.data.length === 0">
                                <td colspan="6">
                                    <div class="flex flex-col items-center justify-center py-16 gap-3 opacity-40">
                                        <Bell class="w-12 h-12" />
                                        <p class="font-bold">Belum ada pengumuman.</p>
                                        <p class="text-sm">Klik "Tambah Pengumuman" untuk membuat pengumuman pertama.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="announcements.last_page > 1"
                     class="flex justify-center gap-1 p-4 border-t border-base-200">
                    <template v-for="link in announcements.links" :key="link.label">
                        <Link
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
        </div>
    </KajurLayout>
</template>
