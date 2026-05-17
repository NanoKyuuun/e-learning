<script setup>
/**
 * AiChatBox.vue — Komponen chat AI untuk siswa.
 * Mode: 'document' (materi guru), 'free' (chat bebas), 'web_search' (internet)
 */
import { ref, nextTick, computed } from 'vue';
import {
    Bot, Send, Loader2, Globe, BookOpen, MessageCircle,
    AlertTriangle, ExternalLink, FileText, X, Sparkles, Copy, Check
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    meeting:          { type: Object,  required: true },
    role:             { type: String,  default: 'siswa' },
    documents:        { type: Array,   default: () => [] },
    initialLimit:     { type: Number,  default: 20 },
    webSearchEnabled: { type: Boolean, default: true },
});

// ─── State ────────────────────────────────────────────────────────────────
const messages  = ref([]);
const input     = ref('');
const mode      = ref('free');   // 'document' | 'free' | 'web_search'
const loading   = ref(false);
const sessionId = ref(null);
const remaining = ref(props.initialLimit);
const chatEnd   = ref(null);
const copied    = ref(null);

const hasDocuments = computed(() =>
    props.documents.some(d => d.processing_status === 'completed')
);

// Mode config
const modes = computed(() => {
    const list = [
        { key: 'free',       label: 'Bebas',    icon: MessageCircle, color: 'btn-accent'     },
        { key: 'document',   label: 'Materi',   icon: BookOpen,      color: 'btn-primary'    },
    ];
    if (props.webSearchEnabled) {
        list.push({ key: 'web_search', label: 'Internet', icon: Globe, color: 'btn-secondary' });
    }
    return list;
});

const activeMode = computed(() => modes.value.find(m => m.key === mode.value) || modes.value[0]);

// Apakah boleh kirim pesan
const canSend = computed(() => {
    if (loading.value || remaining.value <= 0) return false;
    if (mode.value === 'document' && !hasDocuments.value) return false;
    return true;
});

// Placeholder berdasarkan mode & kondisi
const placeholder = computed(() => {
    if (remaining.value <= 0)                               return 'Batas harian tercapai. Coba lagi besok.';
    if (mode.value === 'document' && !hasDocuments.value)  return 'Belum ada materi — coba mode Bebas atau Internet.';
    if (mode.value === 'web_search')                        return 'Cari informasi dari internet...';
    if (mode.value === 'free')                              return 'Tanya apa saja — matematika, sains, tips belajar...';
    return 'Tanya tentang materi ini...';
});

// ─── Kirim pesan ─────────────────────────────────────────────────────────
async function sendMessage() {
    const question = input.value.trim();
    if (!question || !canSend.value) return;

    messages.value.push({ role: 'user', content: question, id: Date.now() });
    input.value   = '';
    loading.value = true;
    await scrollToBottom();

    try {
        let endpoint, payload;

        if (mode.value === 'web_search') {
            endpoint = route('siswa.meetings.ai.web-search', props.meeting.id);
            payload  = { question, session_id: sessionId.value };

        } else if (mode.value === 'free') {
            endpoint = route('siswa.meetings.ai.free-chat', props.meeting.id);
            // Kirim history 10 pesan SEBELUM pertanyaan ini (hindari duplikasi)
            // messages.value sudah include current message di index -1, jadi slice 0,-1
            const history = messages.value
                .filter(m => m.role === 'user' || m.role === 'assistant')
                .slice(0, -1)   // exclude current question yang baru dipush
                .slice(-10)     // max 10 pesan sebelumnya
                .map(m => ({ role: m.role, content: m.content }));
            payload = { question, session_id: sessionId.value, history };

        } else {
            endpoint = route('siswa.meetings.ai.chat', props.meeting.id);
            payload  = { question, session_id: sessionId.value };
        }

        const { data } = await axios.post(endpoint, {
            ...payload,
            _token: document.querySelector('meta[name="csrf-token"]')?.content,
        });

        sessionId.value = data.session_id || sessionId.value;
        if (data.remaining !== undefined) remaining.value = data.remaining;

        if (!data.success || !data.answer) {
            messages.value.push({
                role:    'error',
                content: data.message || 'Tidak ada jawaban dari server.',
                id:      Date.now() + 1,
            });
            return;
        }

        messages.value.push({
            role:    'assistant',
            content: data.answer,
            sources: data.sources || [],
            model:   data.model,
            mode:    mode.value,
            id:      Date.now() + 1,
        });

    } catch (err) {
        const status  = err.response?.status;
        const msg     = err.response?.data?.message || err.message;
        let display   = msg;
        if (status === 429)  display = 'Batas harian tercapai. Coba lagi besok.';
        else if (status === 403) display = 'Akses ditolak: ' + msg;
        else if (!err.response)  display = 'Tidak bisa terhubung ke server AI.';

        messages.value.push({ role: 'error', content: display, id: Date.now() + 1 });
    } finally {
        loading.value = false;
        await scrollToBottom();
    }
}

async function scrollToBottom() {
    await nextTick();
    chatEnd.value?.scrollIntoView({ behavior: 'smooth' });
}

function copyText(text, id) {
    navigator.clipboard.writeText(text).then(() => {
        copied.value = id;
        setTimeout(() => { copied.value = null; }, 2000);
    });
}

function handleKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function clearChat() {
    messages.value = [];
    sessionId.value = null;
}
</script>

<template>
    <div class="flex flex-col bg-base-100 rounded-3xl border border-base-200 shadow-lg overflow-hidden" style="min-height:480px; max-height:680px;">

        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200 bg-gradient-to-r from-primary/5 to-secondary/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-primary/10 flex items-center justify-center">
                    <Sparkles class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <h3 class="font-black text-sm text-base-content">AI Learning Assistant</h3>
                    <p class="text-xs opacity-50">Sisa hari ini: {{ remaining }} pertanyaan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Mode tabs -->
                <div class="join shadow-sm border border-base-200 rounded-xl overflow-hidden">
                    <button v-for="m in modes" :key="m.key"
                        @click="mode = m.key"
                        :class="['join-item btn btn-xs gap-1 font-bold border-none', mode === m.key ? m.color + ' text-white' : 'btn-ghost']">
                        <component :is="m.icon" class="w-3 h-3" />
                        {{ m.label }}
                    </button>
                </div>
                <button v-if="messages.length > 0" @click="clearChat" class="btn btn-ghost btn-xs btn-square" title="Hapus chat">
                    <X class="w-3 h-3" />
                </button>
            </div>
        </div>

        <!-- Mode description badge -->
        <div class="px-4 pt-2 shrink-0">
            <div v-if="mode === 'free'" class="flex items-center gap-2 text-xs text-accent font-bold bg-accent/5 border border-accent/20 rounded-xl px-3 py-1.5">
                <MessageCircle class="w-3 h-3" />
                <span>Mode Bebas — Tanya apa saja, AI siap membantu belajarmu!</span>
            </div>
            <div v-else-if="mode === 'document' && !hasDocuments" class="flex items-center gap-2 text-xs text-warning font-bold bg-warning/5 border border-warning/20 rounded-xl px-3 py-1.5">
                <AlertTriangle class="w-3 h-3 shrink-0" />
                <span>Guru belum memproses materi dengan AI. Coba mode
                    <span class="underline cursor-pointer" @click="mode = 'free'">Bebas</span> atau
                    <span v-if="webSearchEnabled" class="underline cursor-pointer" @click="mode = 'web_search'">Internet</span>.</span>
            </div>
            <div v-else-if="mode === 'document' && hasDocuments" class="flex items-center gap-2 text-xs text-primary font-bold bg-primary/5 border border-primary/20 rounded-xl px-3 py-1.5">
                <BookOpen class="w-3 h-3" />
                <span>Mode Materi — Jawaban berdasarkan {{ documents.filter(d => d.processing_status === 'completed').length }} dokumen yang siap.</span>
            </div>
            <div v-else-if="mode === 'web_search'" class="flex items-center gap-2 text-xs text-secondary font-bold bg-secondary/5 border border-secondary/20 rounded-xl px-3 py-1.5">
                <Globe class="w-3 h-3" />
                <span>Mode Internet — Informasi dari web. Verifikasi sebelum digunakan!</span>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 mt-2">
            <!-- Empty state -->
            <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-40 text-center opacity-40">
                <Bot class="w-10 h-10 mb-2 text-primary" />
                <p class="font-bold text-sm">
                    <template v-if="mode === 'free'">Halo! Tanya apa saja, aku siap membantu! 🎓</template>
                    <template v-else-if="mode === 'document'">Tanya tentang materi yang sudah diproses guru.</template>
                    <template v-else>Cari informasi dari internet!</template>
                </p>
                <p v-if="mode === 'free'" class="text-xs mt-1 opacity-70">Matematika, fisika, kimia, bahasa, tips belajar, dan lainnya.</p>
            </div>

            <!-- Messages -->
            <template v-for="msg in messages" :key="msg.id">
                <!-- User -->
                <div v-if="msg.role === 'user'" class="flex justify-end">
                    <div class="bg-primary text-primary-content rounded-3xl rounded-tr-lg px-4 py-2.5 max-w-[80%] text-sm font-medium shadow-md shadow-primary/20">
                        {{ msg.content }}
                    </div>
                </div>

                <!-- Error -->
                <div v-else-if="msg.role === 'error'"
                     class="flex items-start gap-2 bg-error/10 border border-error/20 rounded-2xl p-3">
                    <AlertTriangle class="w-4 h-4 text-error shrink-0 mt-0.5" />
                    <p class="text-sm text-error font-medium">{{ msg.content }}</p>
                </div>

                <!-- Assistant -->
                <div v-else class="flex items-start gap-3">
                    <div :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-md',
                        msg.mode === 'free'       ? 'bg-gradient-to-br from-accent to-primary' :
                        msg.mode === 'web_search' ? 'bg-gradient-to-br from-secondary to-info' :
                                                    'bg-gradient-to-br from-primary to-secondary']">
                        <Bot class="w-4 h-4 text-white" />
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="bg-base-200/60 rounded-3xl rounded-tl-lg px-4 py-3 text-sm leading-relaxed relative group">
                            <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                            <button
                                @click="copyText(msg.content, msg.id)"
                                class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square opacity-0 group-hover:opacity-100 transition-opacity">
                                <Check v-if="copied === msg.id" class="w-3 h-3 text-success" />
                                <Copy v-else class="w-3 h-3" />
                            </button>
                        </div>

                        <!-- Sources -->
                        <div v-if="msg.sources && msg.sources.length > 0" class="space-y-1">
                            <p class="text-xs font-bold opacity-40 uppercase tracking-wider ml-1">Sumber:</p>
                            <div v-for="(src, i) in msg.sources" :key="i"
                                 class="flex items-center gap-2 text-xs bg-base-100 border border-base-200 rounded-xl px-3 py-2">
                                <Globe v-if="src.type === 'web'" class="w-3 h-3 text-secondary shrink-0" />
                                <FileText v-else class="w-3 h-3 text-primary shrink-0" />
                                <span class="truncate flex-1 font-medium">
                                    <template v-if="src.type === 'web'">{{ src.title || src.url }}</template>
                                    <template v-else>
                                        {{ src.filename || 'Dokumen' }}
                                        <span v-if="src.page_number" class="opacity-50"> — hal. {{ src.page_number }}</span>
                                        <span v-if="src.sheet_name" class="opacity-50"> — {{ src.sheet_name }}</span>
                                    </template>
                                </span>
                                <a v-if="src.url" :href="src.url" target="_blank" class="text-secondary hover:text-secondary/70">
                                    <ExternalLink class="w-3 h-3" />
                                </a>
                            </div>
                        </div>

                        <!-- Mode & model badge -->
                        <div class="flex items-center gap-2 ml-1">
                            <span v-if="msg.mode === 'web_search'" class="badge badge-xs badge-secondary font-bold gap-1">
                                <Globe class="w-2 h-2" /> Internet
                            </span>
                            <span v-else-if="msg.mode === 'free'" class="badge badge-xs badge-accent font-bold gap-1">
                                <MessageCircle class="w-2 h-2" /> Bebas
                            </span>
                            <span v-if="msg.model" class="text-xs opacity-30">{{ msg.model }}</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Loading -->
            <div v-if="loading" class="flex items-center gap-3">
                <div :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0',
                    mode === 'free' ? 'bg-gradient-to-br from-accent to-primary' :
                    mode === 'web_search' ? 'bg-gradient-to-br from-secondary to-info' :
                    'bg-gradient-to-br from-primary to-secondary']">
                    <Loader2 class="w-4 h-4 text-white animate-spin" />
                </div>
                <div class="bg-base-200/60 rounded-3xl rounded-tl-lg px-4 py-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-primary/50 rounded-full animate-bounce [animation-delay:0ms]"></span>
                        <span class="w-2 h-2 bg-primary/50 rounded-full animate-bounce [animation-delay:150ms]"></span>
                        <span class="w-2 h-2 bg-primary/50 rounded-full animate-bounce [animation-delay:300ms]"></span>
                    </div>
                </div>
            </div>

            <div ref="chatEnd"></div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-base-200 bg-base-50/50 shrink-0">
            <div class="flex gap-2 items-end">
                <textarea
                    v-model="input"
                    @keydown="handleKeydown"
                    :disabled="!canSend"
                    :placeholder="placeholder"
                    rows="1"
                    class="textarea textarea-bordered flex-1 resize-none rounded-2xl text-sm focus:outline-primary min-h-[44px] max-h-[120px]"
                    style="field-sizing: content;"
                ></textarea>
                <button
                    @click="sendMessage"
                    :disabled="!input.trim() || !canSend"
                    :class="['btn btn-square rounded-2xl shadow-lg transition-all',
                        mode === 'web_search' ? 'btn-secondary shadow-secondary/20' :
                        mode === 'free'       ? 'btn-accent shadow-accent/20' :
                                                'btn-primary shadow-primary/20']">
                    <Send class="w-4 h-4" />
                </button>
            </div>
            <p class="text-xs opacity-30 mt-2 text-center">AI dapat membuat kesalahan. Verifikasi jawaban penting.</p>
        </div>
    </div>
</template>
