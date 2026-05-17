<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import {
    Sparkles, Activity, Shield, Save, RefreshCw, CheckCircle2,
    XCircle, AlertTriangle, Loader2, Settings2, Globe, FileText, MessageSquare
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    health: Object,
    limits: Object,
    config: Object,
});

const saving  = ref(false);
const saved   = ref(false);
const checking = ref(false);
const healthData = ref(props.health);

// Build editable limits array dari objek
const roles = ['siswa', 'guru', 'admin-sistem', 'kajur'];
const editableLimits = reactive(
    roles.map(role => ({
        role,
        daily_chat_limit:             props.limits[role]?.daily_chat_limit              ?? 20,
        daily_web_search_limit:        props.limits[role]?.daily_web_search_limit        ?? 10,
        daily_document_process_limit:  props.limits[role]?.daily_document_process_limit  ?? 5,
        max_file_size_mb:              props.limits[role]?.max_file_size_mb              ?? 20,
        is_active:                     props.limits[role]?.is_active                     ?? true,
    }))
);

const roleLabel = { 'siswa': 'Siswa', 'guru': 'Guru', 'admin-sistem': 'Admin', 'kajur': 'Kajur' };
const roleColor = { 'siswa': 'badge-primary', 'guru': 'badge-secondary', 'admin-sistem': 'badge-error', 'kajur': 'badge-accent' };

async function saveLimits() {
    saving.value = true;
    saved.value  = false;
    try {
        await axios.patch(route('admin.ai.settings.update'), { limits: editableLimits }, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        });
        saved.value = true;
        setTimeout(() => { saved.value = false; }, 3000);
    } catch (e) {
        alert('Gagal menyimpan: ' + (e.response?.data?.message || e.message));
    } finally {
        saving.value = false;
    }
}

async function checkHealth() {
    checking.value = true;
    try {
        const { data } = await axios.get(route('admin.ai.health'));
        healthData.value = data;
    } catch {
        healthData.value = { status: 'unreachable', error: 'Tidak dapat terhubung ke AI Service' };
    } finally {
        checking.value = false;
    }
}

const isHealthy = () => healthData.value?.status === 'ok';
</script>

<template>
    <Head title="Pengaturan AI" />
    <AdminLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <Sparkles class="w-6 h-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-base-content">Pengaturan AI</h1>
                        <p class="text-sm opacity-50">Manajemen limit penggunaan & status AI Service</p>
                    </div>
                </div>
                <span class="badge badge-primary badge-lg font-bold">BETA</span>
            </div>

            <!-- Health Status Card -->
            <div :class="['card border shadow-sm', isHealthy() ? 'border-success/30 bg-success/5' : 'border-error/30 bg-error/5']">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center', isHealthy() ? 'bg-success/20' : 'bg-error/20']">
                                <Activity :class="['w-6 h-6', isHealthy() ? 'text-success' : 'text-error']" />
                            </div>
                            <div>
                                <h3 class="font-black text-lg">Status AI Service</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="['badge badge-sm font-bold', isHealthy() ? 'badge-success' : 'badge-error']">
                                        {{ isHealthy() ? '● Online' : '● Offline' }}
                                    </span>
                                    <span v-if="healthData?.version" class="text-xs opacity-50">v{{ healthData.version }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div v-if="healthData?.openrouter_configured !== undefined" class="text-center">
                                <p class="text-xs opacity-50 uppercase tracking-wider">OpenRouter</p>
                                <div class="flex items-center gap-1 mt-1">
                                    <CheckCircle2 v-if="healthData.openrouter_configured" class="w-4 h-4 text-success" />
                                    <XCircle v-else class="w-4 h-4 text-error" />
                                    <span class="text-xs font-bold">{{ healthData.openrouter_configured ? 'Terhubung' : 'Belum dikonfigurasi' }}</span>
                                </div>
                            </div>
                            <div v-if="config" class="text-center">
                                <p class="text-xs opacity-50 uppercase tracking-wider">Model</p>
                                <p class="text-xs font-bold mt-1">{{ config.openrouter_model }}</p>
                            </div>
                            <button @click="checkHealth" :disabled="checking"
                                class="btn btn-sm btn-ghost gap-2 border border-base-300">
                                <Loader2 v-if="checking" class="w-4 h-4 animate-spin" />
                                <RefreshCw v-else class="w-4 h-4" />
                                Cek Ulang
                            </button>
                        </div>
                    </div>
                    <div v-if="healthData?.error" class="mt-3 text-sm text-error font-medium">
                        <AlertTriangle class="w-4 h-4 inline mr-1" /> {{ healthData.error }}
                    </div>
                </div>
            </div>

            <!-- Config Info -->
            <div v-if="config" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-base-100 border border-base-200 rounded-2xl p-4 text-center">
                    <Globe class="w-5 h-5 mx-auto mb-2 text-secondary" />
                    <p class="text-xs font-black opacity-50 uppercase tracking-wider">Web Search</p>
                    <p class="font-bold text-sm mt-1">{{ config.web_search_enabled ? 'Aktif' : 'Nonaktif' }}</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-2xl p-4 text-center">
                    <Settings2 class="w-5 h-5 mx-auto mb-2 text-primary" />
                    <p class="text-xs font-black opacity-50 uppercase tracking-wider">Search Mode</p>
                    <p class="font-bold text-xs mt-1 truncate">{{ config.web_search_mode }}</p>
                </div>
                <div class="bg-base-100 border border-base-200 rounded-2xl p-4 text-center col-span-2">
                    <Sparkles class="w-5 h-5 mx-auto mb-2 text-accent" />
                    <p class="text-xs font-black opacity-50 uppercase tracking-wider">AI Service URL</p>
                    <p class="font-bold text-xs mt-1 truncate opacity-70">{{ config.ai_service_url }}</p>
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <Shield class="w-5 h-5 text-primary" />
                            <h2 class="font-black text-lg">Limit Penggunaan Harian per Role</h2>
                        </div>
                        <button @click="saveLimits" :disabled="saving"
                            :class="['btn gap-2 shadow-md', saved ? 'btn-success' : 'btn-primary']">
                            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                            <CheckCircle2 v-else-if="saved" class="w-4 h-4" />
                            <Save v-else class="w-4 h-4" />
                            {{ saving ? 'Menyimpan...' : saved ? 'Tersimpan!' : 'Simpan Perubahan' }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="limit in editableLimits" :key="limit.role"
                             class="p-5 bg-base-50 rounded-2xl border border-base-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span :class="['badge badge-sm font-bold', roleColor[limit.role]]">
                                        {{ roleLabel[limit.role] }}
                                    </span>
                                    <span class="text-xs opacity-50 font-mono">{{ limit.role }}</span>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <span class="text-xs font-bold opacity-60">Aktif</span>
                                    <input type="checkbox" v-model="limit.is_active" class="toggle toggle-primary toggle-sm" />
                                </label>
                            </div>
                            <div :class="['grid grid-cols-2 md:grid-cols-4 gap-4', !limit.is_active ? 'opacity-40 pointer-events-none' : '']">
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-xs font-bold flex items-center gap-1">
                                            <MessageSquare class="w-3 h-3" /> Chat / hari
                                        </span>
                                    </label>
                                    <input type="number" v-model.number="limit.daily_chat_limit"
                                        min="0" max="1000" class="input input-bordered input-sm rounded-xl" />
                                </div>
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-xs font-bold flex items-center gap-1">
                                            <Globe class="w-3 h-3" /> Web Search / hari
                                        </span>
                                    </label>
                                    <input type="number" v-model.number="limit.daily_web_search_limit"
                                        min="0" max="500" class="input input-bordered input-sm rounded-xl" />
                                </div>
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-xs font-bold flex items-center gap-1">
                                            <FileText class="w-3 h-3" /> Proses Dok / hari
                                        </span>
                                    </label>
                                    <input type="number" v-model.number="limit.daily_document_process_limit"
                                        min="0" max="100" class="input input-bordered input-sm rounded-xl" />
                                </div>
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-xs font-bold flex items-center gap-1">
                                            Max File (MB)
                                        </span>
                                    </label>
                                    <input type="number" v-model.number="limit.max_file_size_mb"
                                        min="1" max="100" class="input input-bordered input-sm rounded-xl" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs opacity-40 mt-4 text-center">
                        ⚠️ Perubahan berlaku segera. Limit dihitung ulang setiap hari pukul 00:00.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
