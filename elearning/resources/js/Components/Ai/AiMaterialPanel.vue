<script setup>
/**
 * AiMaterialPanel.vue — Panel AI untuk guru di halaman meeting.
 * Fitur: proses dokumen, buat ringkasan, kuis, glosarium, lihat output.
 */
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Sparkles, FileText, Brain, ListChecks, BookMarked, RefreshCw,
    ChevronDown, ChevronUp, Copy, Check, Loader2, AlertTriangle,
    CheckCircle2, Clock, XCircle, Trash2
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    meeting:   { type: Object, required: true },
    documents: { type: Array,  default: () => [] },
});

// ─── State ────────────────────────────────────────────────────────────────
const activeTab    = ref('status'); // 'status' | 'summary' | 'quiz' | 'glossary'
const loading      = ref(null);     // null | 'summary' | 'quiz' | 'glossary'
const result       = ref(null);
const error        = ref(null);
const copied       = ref(false);
const numQuestions = ref(5);
const quizTypes    = ref(['multiple_choice', 'true_false']);

const selectedDocId = ref(props.documents.find(d => d.processing_status === 'completed')?.id || null);

const completedDocs = computed(() => props.documents.filter(d => d.processing_status === 'completed'));
const pendingDocs   = computed(() => props.documents.filter(d => ['pending', 'processing'].includes(d.processing_status)));
const failedDocs    = computed(() => props.documents.filter(d => d.processing_status === 'failed'));
const selectedDoc   = computed(() => props.documents.find(d => d.id === selectedDocId.value));

// ─── Actions ─────────────────────────────────────────────────────────────
function processDocument(materialId) {
    if (!materialId) return;
    router.post(route('guru.materials.ai.process', materialId));
}

async function generate(type) {
    if (!selectedDocId.value) return;
    loading.value = type;
    error.value   = null;
    result.value  = null;

    try {
        const endpoint = type === 'summary'
            ? route('guru.meetings.ai.summary', props.meeting.id)
            : type === 'quiz'
                ? route('guru.meetings.ai.quiz', props.meeting.id)
                : route('guru.meetings.ai.glossary', props.meeting.id);

        const payload = {
            document_id:    selectedDocId.value,
            num_questions:  numQuestions.value,
            question_types: quizTypes.value,
        };

        const { data } = await axios.post(endpoint, payload, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        });

        if (data.success) {
            result.value = { type, content: data.content };
            activeTab.value = type;
        } else {
            error.value = data.message || 'Gagal membuat output.';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Terjadi kesalahan.';
    } finally {
        loading.value = null;
    }
}

function copyResult() {
    if (!result.value) return;
    navigator.clipboard.writeText(JSON.stringify(result.value.content, null, 2)).then(() => {
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    });
}

// ─── Status badge helper ──────────────────────────────────────────────────
function statusBadge(status) {
    return {
        completed:   { class: 'badge-success', label: 'Siap',         icon: CheckCircle2 },
        pending:     { class: 'badge-warning', label: 'Menunggu',     icon: Clock },
        processing:  { class: 'badge-info',    label: 'Diproses',    icon: Loader2 },
        failed:      { class: 'badge-error',   label: 'Gagal',       icon: XCircle },
        not_started: { class: 'badge-ghost',   label: 'Belum diproses', icon: FileText },
    }[status] || { class: 'badge-ghost', label: status, icon: FileText };
}
</script>

<template>
    <div class="bg-base-100 rounded-3xl border border-base-200 shadow-md overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-base-200 bg-gradient-to-r from-primary/5 to-violet-500/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center">
                    <Sparkles class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <h3 class="font-black text-base-content">AI Document Tools</h3>
                    <p class="text-xs opacity-50">Analisis & buat konten dari materi secara otomatis</p>
                </div>
            </div>
        </div>

        <!-- Tab navigation -->
        <div class="flex border-b border-base-200 bg-base-50/50">
            <button
                v-for="tab in [{key:'status',label:'Status'}, {key:'summary',label:'Ringkasan'}, {key:'quiz',label:'Kuis'}, {key:'glossary',label:'Glosarium'}]"
                :key="tab.key"
                @click="activeTab = tab.key"
                :class="['flex-1 py-3 text-xs font-bold transition-colors', activeTab === tab.key ? 'text-primary border-b-2 border-primary bg-primary/5' : 'opacity-50 hover:opacity-70']">
                {{ tab.label }}
            </button>
        </div>

        <div class="p-5">
            <!-- Tab: Status Dokumen -->
            <div v-if="activeTab === 'status'" class="space-y-3">
                <div v-if="documents.length === 0" class="text-center py-8 opacity-40 italic text-sm">
                    <FileText class="w-10 h-10 mx-auto mb-2 opacity-30" />
                    <p>Belum ada materi yang diunggah untuk pertemuan ini.</p>
                    <p class="text-xs mt-1">Upload materi di panel materi di atas, lalu klik "Proses AI" di sini.</p>
                </div>

                <div v-for="doc in documents" :key="doc.material_id || doc.id"
                     class="flex items-center justify-between p-3 bg-base-50 rounded-2xl border border-base-200 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <FileText class="w-4 h-4 text-primary shrink-0" />
                        <div class="min-w-0">
                            <p class="font-bold text-sm truncate">{{ doc.material_title || doc.title }}</p>
                            <p v-if="doc.processing_status === 'completed'" class="text-xs opacity-50">
                                {{ doc.total_chunks }} chunk
                                <span v-if="doc.total_pages"> · {{ doc.total_pages }} hal.</span>
                                <span v-if="doc.total_sheets"> · {{ doc.total_sheets }} sheet</span>
                            </p>
                            <p v-if="doc.processing_status === 'not_started'" class="text-xs opacity-50">
                                Klik "Proses AI" untuk menganalisis materi ini dengan AI.
                            </p>
                            <p v-if="doc.error_message" class="text-xs text-error truncate">{{ doc.error_message }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span :class="['badge badge-sm font-bold', statusBadge(doc.processing_status).class]">
                            {{ statusBadge(doc.processing_status).label }}
                        </span>
                        <button
                            v-if="doc.processing_status !== 'completed' && doc.material_id"
                            @click="processDocument(doc.material_id)"
                            class="btn btn-xs btn-primary gap-1">
                            <RefreshCw class="w-3 h-3" />
                            {{ doc.processing_status === 'failed' ? 'Ulang' : 'Proses AI' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Generator Tabs (Summary, Quiz, Glossary) ── -->
            <div v-else class="space-y-4">
                <!-- Pilih dokumen -->
                <div v-if="completedDocs.length === 0" class="p-4 bg-warning/10 rounded-2xl text-xs text-warning font-bold flex gap-2">
                    <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5" />
                    Belum ada dokumen yang selesai diproses. Proses dokumen terlebih dahulu di tab Status.
                </div>

                <div v-else class="space-y-3">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-xs opacity-60">Pilih Dokumen</span></label>
                        <select v-model="selectedDocId" class="select select-bordered select-sm rounded-xl">
                            <option v-for="doc in completedDocs" :key="doc.id" :value="doc.id">{{ doc.title }}</option>
                        </select>
                    </div>

                    <!-- Opsi kuis -->
                    <div v-if="activeTab === 'quiz'" class="space-y-3">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold text-xs opacity-60">Jumlah Soal</span></label>
                            <input type="number" v-model="numQuestions" min="1" max="20"
                                class="input input-bordered input-sm rounded-xl w-24" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold text-xs opacity-60">Jenis Soal</span></label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="type in ['multiple_choice','true_false','short_answer','essay']" :key="type"
                                    class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" :value="type" v-model="quizTypes" class="checkbox checkbox-xs checkbox-primary" />
                                    <span class="text-xs font-medium">{{ {
                                        multiple_choice: 'Pilihan Ganda',
                                        true_false: 'Benar/Salah',
                                        short_answer: 'Isian',
                                        essay: 'Esai',
                                    }[type] }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Generate button -->
                    <button
                        @click="generate(activeTab)"
                        :disabled="!selectedDocId || loading !== null"
                        :class="['btn btn-primary w-full gap-2 shadow-md shadow-primary/20 rounded-2xl', loading === activeTab ? 'loading' : '']">
                        <Loader2 v-if="loading === activeTab" class="w-4 h-4 animate-spin" />
                        <Brain v-else class="w-4 h-4" />
                        {{ loading === activeTab ? 'Membuat...' : {
                            summary: 'Buat Ringkasan',
                            quiz: 'Buat Kuis',
                            glossary: 'Buat Glosarium',
                        }[activeTab] }}
                    </button>
                </div>

                <!-- Error -->
                <div v-if="error" class="p-3 bg-error/10 rounded-2xl text-xs text-error font-bold flex gap-2">
                    <AlertTriangle class="w-4 h-4 shrink-0" /> {{ error }}
                </div>

                <!-- Result -->
                <div v-if="result && result.type === activeTab" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold opacity-50 uppercase tracking-wider">Hasil AI</p>
                        <div class="flex gap-2">
                            <button @click="copyResult" class="btn btn-ghost btn-xs gap-1">
                                <Check v-if="copied" class="w-3 h-3 text-success" />
                                <Copy v-else class="w-3 h-3" /> Salin
                            </button>
                            <button @click="result = null" class="btn btn-ghost btn-xs btn-square">
                                <Trash2 class="w-3 h-3" />
                            </button>
                        </div>
                    </div>

                    <!-- Summary display -->
                    <div v-if="result.type === 'summary' && result.content" class="space-y-3">
                        <div v-if="result.content.ringkasan_singkat" class="p-4 bg-primary/5 rounded-2xl border border-primary/20">
                            <p class="text-xs font-black text-primary uppercase tracking-wider mb-2">Ringkasan</p>
                            <p class="text-sm">{{ result.content.ringkasan_singkat }}</p>
                        </div>
                        <div v-if="result.content.poin_penting?.length" class="p-4 bg-base-200/60 rounded-2xl">
                            <p class="text-xs font-black opacity-60 uppercase tracking-wider mb-2">Poin Penting</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li v-for="(p, i) in result.content.poin_penting" :key="i" class="text-sm">{{ p }}</li>
                            </ul>
                        </div>
                        <div v-if="result.content.istilah_kunci?.length" class="p-4 bg-base-200/60 rounded-2xl">
                            <p class="text-xs font-black opacity-60 uppercase tracking-wider mb-2">Istilah Kunci</p>
                            <div class="space-y-1">
                                <div v-for="(item, i) in result.content.istilah_kunci" :key="i" class="text-sm">
                                    <span class="font-bold">{{ item.istilah }}:</span> {{ item.definisi }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz display -->
                    <div v-else-if="result.type === 'quiz' && result.content?.questions" class="space-y-3">
                        <div v-for="(q, i) in result.content.questions" :key="i"
                             class="p-4 bg-base-200/60 rounded-2xl border border-base-200">
                            <p class="font-bold text-sm mb-2">{{ i + 1 }}. {{ q.question }}</p>
                            <div v-if="q.options" class="space-y-1 mb-2">
                                <div v-for="(opt, j) in q.options" :key="j"
                                     :class="['text-xs p-2 rounded-xl', opt.startsWith(q.answer) ? 'bg-success/20 text-success font-bold border border-success/30' : 'bg-base-100']">
                                    {{ opt }}
                                </div>
                            </div>
                            <p v-if="q.explanation" class="text-xs opacity-60 italic">{{ q.explanation }}</p>
                        </div>
                    </div>

                    <!-- Glossary display -->
                    <div v-else-if="result.type === 'glossary' && result.content?.glossary" class="space-y-2">
                        <div v-for="(item, i) in result.content.glossary" :key="i"
                             class="p-3 bg-base-200/60 rounded-2xl">
                            <p class="font-bold text-sm">{{ item.term }}</p>
                            <p class="text-xs opacity-70 mt-1">{{ item.definition }}</p>
                        </div>
                    </div>

                    <!-- Fallback raw -->
                    <div v-else class="p-4 bg-base-200/60 rounded-2xl">
                        <pre class="text-xs whitespace-pre-wrap">{{ JSON.stringify(result.content, null, 2) }}</pre>
                    </div>

                    <p class="text-xs opacity-30 text-center">⚠️ Hasil AI perlu diperiksa sebelum diberikan ke siswa.</p>
                </div>
            </div>
        </div>
    </div>
</template>
