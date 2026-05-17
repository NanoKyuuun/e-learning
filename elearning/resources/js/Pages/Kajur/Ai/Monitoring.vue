<script setup>
import KajurLayout from '@/Layouts/KajurLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    Sparkles, MessageSquare, Globe, FileText, Users,
    TrendingUp, CheckCircle2, XCircle, AlertTriangle, BarChart3
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    stats:        Object,
    topUsers:     Array,
    featureStats: Array,
});

// Proses featureStats jadi format yang mudah dipakai
const featureMap = computed(() => {
    const map = {};
    (props.featureStats || []).forEach(row => {
        if (!map[row.feature]) map[row.feature] = { success: 0, error: 0, total: 0 };
        map[row.feature][row.status] = (map[row.feature][row.status] || 0) + row.total;
        map[row.feature].total += row.total;
    });
    return map;
});

const featureList = [
    { key: 'chat',     label: 'Chat Dokumen', icon: MessageSquare, color: 'text-primary'   },
    { key: 'web_search', label: 'Web Search', icon: Globe,         color: 'text-secondary' },
    { key: 'summary',  label: 'Ringkasan',    icon: FileText,      color: 'text-accent'    },
    { key: 'quiz',     label: 'Kuis',         icon: BarChart3,     color: 'text-warning'   },
    { key: 'glossary', label: 'Glosarium',    icon: Sparkles,      color: 'text-info'      },
];

function successRate(feature) {
    const d = featureMap.value[feature];
    if (!d || d.total === 0) return 0;
    return Math.round((d.success / d.total) * 100);
}
</script>

<template>
    <Head title="Monitoring AI" />
    <KajurLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                    <Sparkles class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-base-content">Monitoring AI</h1>
                    <p class="text-sm opacity-50">Statistik penggunaan AI E-Learning bulan ini</p>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <MessageSquare class="w-6 h-6 mx-auto mb-2 text-primary" />
                    <p class="text-2xl font-black text-primary">{{ stats.total_chats_today }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Chat Hari Ini</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <Globe class="w-6 h-6 mx-auto mb-2 text-secondary" />
                    <p class="text-2xl font-black text-secondary">{{ stats.total_web_search_today }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Web Search Hari Ini</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <TrendingUp class="w-6 h-6 mx-auto mb-2 text-accent" />
                    <p class="text-2xl font-black text-accent">{{ stats.total_chats_month }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Chat Bulan Ini</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <CheckCircle2 class="w-6 h-6 mx-auto mb-2 text-success" />
                    <p class="text-2xl font-black text-success">{{ stats.documents_completed }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Dok. Selesai</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <AlertTriangle class="w-6 h-6 mx-auto mb-2 text-warning" />
                    <p class="text-2xl font-black text-warning">{{ stats.documents_processing }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Diproses</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-3xl p-5 text-center shadow-sm col-span-1">
                    <XCircle class="w-6 h-6 mx-auto mb-2 text-error" />
                    <p class="text-2xl font-black text-error">{{ stats.documents_failed }}</p>
                    <p class="text-xs opacity-50 font-bold mt-1">Dok. Gagal</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Feature Stats This Week -->
                <div class="card bg-base-100 border border-base-200 shadow-sm">
                    <div class="card-body p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            <h2 class="font-black text-lg">Penggunaan per Fitur (Minggu Ini)</h2>
                        </div>
                        <div class="space-y-4">
                            <div v-for="f in featureList" :key="f.key" class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <component :is="f.icon" :class="['w-4 h-4', f.color]" />
                                        <span class="text-sm font-bold">{{ f.label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-black">{{ featureMap[f.key]?.total ?? 0 }}</span>
                                        <span class="opacity-40">permintaan</span>
                                        <span class="badge badge-xs badge-success font-bold">
                                            {{ successRate(f.key) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="w-full bg-base-200 rounded-full h-2">
                                    <div :class="['h-2 rounded-full transition-all', f.color.replace('text-', 'bg-')]"
                                         :style="{ width: successRate(f.key) + '%' }"></div>
                                </div>
                                <div class="flex gap-3 text-xs opacity-50">
                                    <span class="text-success font-bold">✓ {{ featureMap[f.key]?.success ?? 0 }} berhasil</span>
                                    <span class="text-error font-bold">✗ {{ featureMap[f.key]?.error ?? 0 }} gagal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="card bg-base-100 border border-base-200 shadow-sm">
                    <div class="card-body p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <Users class="w-5 h-5 text-secondary" />
                            <h2 class="font-black text-lg">Top 10 Pengguna Aktif (Bulan Ini)</h2>
                        </div>
                        <div v-if="!topUsers?.length" class="text-center py-8 opacity-40 italic text-sm">
                            Belum ada data penggunaan bulan ini.
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="(u, i) in topUsers" :key="u.user_id"
                                 class="flex items-center gap-4 p-3 bg-base-50 rounded-2xl border border-base-200">
                                <div :class="['w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm shrink-0',
                                    i === 0 ? 'bg-warning text-warning-content' :
                                    i === 1 ? 'bg-base-300 text-base-content' :
                                    i === 2 ? 'bg-orange-400/20 text-orange-600' : 'bg-base-200 text-base-content/60']">
                                    {{ i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm truncate">
                                        {{ u.user?.full_name ?? 'User #' + u.user_id }}
                                    </p>
                                    <p class="text-xs opacity-50 truncate">{{ u.user?.email }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-primary">{{ u.total }}</p>
                                    <p class="text-xs opacity-50">permintaan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-xs opacity-30 text-center">
                Data diperbarui setiap kali halaman dimuat. Statistik berdasarkan log aktivitas AI.
            </p>
        </div>
    </KajurLayout>
</template>
