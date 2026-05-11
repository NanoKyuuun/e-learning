<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_students: 0,
            total_teachers: 0,
            total_subjects: 0,
            total_meetings:  0,
        }),
    },
});

const scrollY = ref(0);
const navSolid = ref(false);
const counters = ref([
    { label: 'Siswa Aktif',    value: 0, target: props.stats.total_students, suffix: '',  icon: '🎓' },
    { label: 'Guru Pengajar',  value: 0, target: props.stats.total_teachers, suffix: '',  icon: '👨‍🏫' },
    { label: 'Mata Pelajaran', value: 0, target: props.stats.total_subjects, suffix: '',  icon: '📚' },
    { label: 'Pertemuan',      value: 0, target: props.stats.total_meetings,  suffix: '+', icon: '📅' },
]);
const statsVisible = ref(false);
let statsObserver = null;

const onScroll = () => {
    scrollY.value = window.scrollY;
    navSolid.value = window.scrollY > 50;
};

const animateCounters = () => {
    counters.value.forEach((c, i) => {
        const duration = 1800;
        const steps = 60;
        const inc = c.target / steps;
        let cur = 0;
        const t = setInterval(() => {
            cur += inc;
            if (cur >= c.target) { cur = c.target; clearInterval(t); }
            counters.value[i].value = Math.floor(cur);
        }, duration / steps);
    });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll);
    const el = document.getElementById('stats-section');
    if (el) {
        statsObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !statsVisible.value) {
                statsVisible.value = true;
                animateCounters();
            }
        }, { threshold: 0.3 });
        statsObserver.observe(el);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    if (statsObserver) statsObserver.disconnect();
});

const features = [
    {
        icon: '🧬',
        title: 'Presensi Face Recognition',
        desc: 'Sistem presensi otomatis berbasis pengenalan wajah. Siswa cukup menatap kamera — hadir tercatat secara real-time.',
        color: 'from-violet-500 to-purple-600',
    },
    {
        icon: '📖',
        title: 'Materi Digital',
        desc: 'Guru dapat mengunggah materi berupa PDF, video, dan dokumen setiap pertemuan. Siswa mengaksesnya kapan saja.',
        color: 'from-blue-500 to-cyan-500',
    },
    {
        icon: '📝',
        title: 'Tugas & Pengumpulan',
        desc: 'Buat tugas dengan deadline jelas. Siswa mengumpulkan file atau jawaban teks langsung di platform.',
        color: 'from-emerald-500 to-teal-500',
    },
    {
        icon: '⭐',
        title: 'Penilaian & Feedback',
        desc: 'Guru memberikan nilai 0–100 dan komentar langsung ke setiap submission. Siswa melihat hasilnya real-time.',
        color: 'from-amber-400 to-orange-500',
    },
    {
        icon: '📊',
        title: 'Monitoring Akademik',
        desc: 'Kajur dapat memantau progres pertemuan, rekap nilai, dan aktivitas seluruh siswa dalam satu dashboard.',
        color: 'from-pink-500 to-rose-500',
    },
    {
        icon: '🔒',
        title: 'Keamanan Multi-Role',
        desc: 'Sistem RBAC ketat memastikan setiap role hanya mengakses data yang menjadi haknya — aman dan terkontrol.',
        color: 'from-slate-500 to-gray-600',
    },
];

const steps = [
    {
        num: '01',
        title: 'Daftarkan Wajah',
        desc: 'Admin atau guru mendaftarkan foto wajah siswa ke sistem face recognition sekali saja.',
        icon: '📸',
    },
    {
        num: '02',
        title: 'Login & Presensi',
        desc: 'Siswa login dengan email, lalu gunakan face recognition untuk presensi di setiap pertemuan.',
        icon: '🔍',
    },
    {
        num: '03',
        title: 'Belajar & Kumpulkan',
        desc: 'Akses materi, kerjakan tugas, kumpulkan jawaban, dan pantau nilai semua dalam satu platform.',
        icon: '🚀',
    },
];

const roles = [
    {
        name: 'Admin Sistem',
        emoji: '⚙️',
        color: 'border-slate-400',
        bg: 'bg-slate-50',
        badge: 'badge-neutral',
        items: ['Kelola akun & role pengguna', 'Konfigurasi tahun ajaran', 'Atur semester aktif', 'Monitor log sistem'],
    },
    {
        name: 'Kepala Jurusan',
        emoji: '🏛️',
        color: 'border-blue-400',
        bg: 'bg-blue-50',
        badge: 'badge-primary',
        items: ['Kelola kelas & mata pelajaran', 'Atur guru pengampu', 'Mapping siswa ke kelas', 'Monitoring akademik'],
    },
    {
        name: 'Guru',
        emoji: '👨‍🏫',
        color: 'border-emerald-400',
        bg: 'bg-emerald-50',
        badge: 'badge-success',
        items: ['Buat pertemuan & materi', 'Buat tugas dengan deadline', 'Periksa submission siswa', 'Beri nilai & feedback'],
    },
    {
        name: 'Siswa',
        emoji: '🎓',
        color: 'border-violet-400',
        bg: 'bg-violet-50',
        badge: 'badge-secondary',
        items: ['Akses materi pelajaran', 'Kumpulkan tugas online', 'Presensi face recognition', 'Pantau nilai & feedback'],
    },
];
</script>

<template>
    <Head title="Beranda — E-Learning SMKN 5 Padang" />

    <div class="font-sans antialiased bg-white text-gray-900">

        <!-- ===== NAVBAR ===== -->
        <nav
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
                navSolid
                    ? 'bg-gray-950/95 backdrop-blur-md shadow-lg shadow-black/20'
                    : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img
                        src="/assets/images/LogoSMKN5.png"
                        alt="Logo SMKN 5 Padang"
                        class="w-9 h-9 object-contain drop-shadow-lg"
                    />
                    <div class="flex flex-col leading-tight">
                        <span class="text-white font-black text-base tracking-tight">
                            E-<span class="text-violet-400">Learning</span>
                        </span>
                        <span class="text-gray-400 text-[10px] font-semibold tracking-widest uppercase">
                            SMKN 5 Padang
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('login')"
                        class="text-white/70 hover:text-white text-sm font-medium transition-colors hidden sm:block"
                    >
                        Masuk
                    </Link>
                    <Link
                        :href="route('login')"
                        class="px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-sm font-bold transition-all duration-200 shadow-md shadow-violet-900/40 hover:shadow-violet-500/40 hover:-translate-y-0.5"
                    >
                        Mulai Belajar →
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ===== HERO ===== -->
        <section
            class="relative min-h-screen flex items-center justify-center overflow-hidden"
            style="background: linear-gradient(135deg, #0f0c29 0%, #1a1040 40%, #0d1b3e 100%);"
        >
            <!-- Background grid -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: linear-gradient(rgba(139,92,246,0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(139,92,246,0.3) 1px, transparent 1px); background-size: 40px 40px;">
            </div>

            <!-- Glow blobs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full opacity-20 blur-3xl"
                 style="background: radial-gradient(circle, #7c3aed, transparent);"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full opacity-15 blur-3xl"
                 style="background: radial-gradient(circle, #2563eb, transparent);"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-32 grid lg:grid-cols-2 gap-16 items-center">
                <!-- Text -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold mb-6"
                         style="background: rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.3); color: #c4b5fd;">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                        Teknologi · Face Recognition
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
                        Platform Belajar<br>
                        <span style="background: linear-gradient(90deg, #a78bfa, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            Cerdas & Modern
                        </span>
                    </h1>
                    <p class="text-lg text-gray-400 mb-8 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                        Sistem e-learning terintegrasi dengan teknologi <strong class="text-violet-400">Face Recognition</strong> untuk presensi otomatis. Materi, tugas, dan penilaian dalam satu platform untuk sekolah modern.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                        <Link
                            :href="route('login')"
                            class="px-6 py-3.5 rounded-xl text-white font-bold text-base transition-all duration-200 hover:-translate-y-0.5 shadow-xl"
                            style="background: linear-gradient(135deg, #7c3aed, #2563eb); box-shadow: 0 8px 30px rgba(124,58,237,0.4);"
                        >
                            🚀 Masuk ke Platform
                        </Link>
                        <a
                            href="#features"
                            class="px-6 py-3.5 rounded-xl font-bold text-base text-gray-300 hover:text-white transition-all duration-200 hover:-translate-y-0.5"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);"
                        >
                            Pelajari Fitur ↓
                        </a>
                    </div>
                </div>

                <!-- Face Scan Animation -->
                <div class="flex justify-center lg:justify-end">
                    <div class="relative w-72 h-72 sm:w-80 sm:h-80">
                        <!-- Outer ring -->
                        <div class="absolute inset-0 rounded-full border-2 border-violet-500/30 animate-spin"
                             style="animation-duration: 8s;"></div>
                        <div class="absolute inset-3 rounded-full border border-blue-500/20 animate-spin"
                             style="animation-duration: 6s; animation-direction: reverse;"></div>

                        <!-- Center face area -->
                        <div class="absolute inset-8 rounded-3xl overflow-hidden"
                             style="background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.3);">
                            <!-- Scan line -->
                            <div class="absolute left-0 right-0 h-0.5 opacity-80"
                                 style="background: linear-gradient(90deg, transparent, #7c3aed, #60a5fa, #7c3aed, transparent); animation: scanLine 2.5s ease-in-out infinite; top: 0;">
                            </div>
                            <!-- Face outline SVG -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg viewBox="0 0 100 120" class="w-28 h-28 opacity-60" fill="none">
                                    <ellipse cx="50" cy="55" rx="30" ry="38" stroke="#a78bfa" stroke-width="1.5" stroke-dasharray="4 2"/>
                                    <circle cx="36" cy="48" r="4" stroke="#60a5fa" stroke-width="1.5"/>
                                    <circle cx="64" cy="48" r="4" stroke="#60a5fa" stroke-width="1.5"/>
                                    <path d="M38 70 Q50 78 62 70" stroke="#a78bfa" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="50" y1="20" x2="50" y2="28" stroke="#7c3aed" stroke-width="1" stroke-dasharray="2"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Corner brackets -->
                        <div class="absolute top-8 left-8 w-5 h-5 border-t-2 border-l-2 border-violet-400 rounded-tl-sm"></div>
                        <div class="absolute top-8 right-8 w-5 h-5 border-t-2 border-r-2 border-violet-400 rounded-tr-sm"></div>
                        <div class="absolute bottom-8 left-8 w-5 h-5 border-b-2 border-l-2 border-violet-400 rounded-bl-sm"></div>
                        <div class="absolute bottom-8 right-8 w-5 h-5 border-b-2 border-r-2 border-violet-400 rounded-br-sm"></div>

                        <!-- Status badge -->
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap"
                             style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: #6ee7b7;">
                            ✓ Wajah Teridentifikasi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave bottom -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 80" fill="white" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-12">
                    <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z"/>
                </svg>
            </div>
        </section>

        <!-- ===== STATS ===== -->
        <section id="stats-section" class="py-16 border-b border-gray-100"
                 style="background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 50%, #f0fdf4 100%);">
            <div class="max-w-5xl mx-auto px-6">
                <!-- Section label -->
                <div class="text-center mb-10">
                    <span class="text-violet-600 font-bold text-xs uppercase tracking-widest">SMKN 5 Padang dalam Angka</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="(c, idx) in counters"
                        :key="c.label"
                        class="relative text-center bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                    >
                        <!-- Accent top bar per stat -->
                        <div
                            class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl"
                            :style="[
                                'background: linear-gradient(90deg, #7c3aed, #a78bfa)',
                                'background: linear-gradient(90deg, #2563eb, #60a5fa)',
                                'background: linear-gradient(90deg, #059669, #34d399)',
                                'background: linear-gradient(90deg, #d97706, #fbbf24)',
                            ][idx % 4]"
                        ></div>
                        <div class="text-3xl mb-2">{{ c.icon }}</div>
                        <div
                            class="text-3xl sm:text-4xl font-black"
                            :style="[
                                'color: #7c3aed',
                                'color: #2563eb',
                                'color: #059669',
                                'color: #d97706',
                            ][idx % 4]"
                        >
                            {{ c.value.toLocaleString('id-ID') }}{{ c.suffix }}
                        </div>
                        <div class="text-sm text-gray-500 font-semibold mt-1">{{ c.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FEATURES ===== -->
        <section id="features" class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="text-violet-600 font-bold text-sm uppercase tracking-widest">Fitur Unggulan</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mt-2 mb-4">
                        Semua yang Dibutuhkan Sekolah Modern
                    </h2>
                    <p class="text-gray-500 max-w-2xl mx-auto">
                        Dari presensi otomatis hingga manajemen pembelajaran lengkap — semua terintegrasi dalam satu platform yang mudah digunakan.
                    </p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group"
                    >
                        <div :class="`w-12 h-12 rounded-xl bg-gradient-to-br ${f.color} flex items-center justify-center text-xl mb-4 shadow-sm group-hover:scale-110 transition-transform duration-300`">
                            {{ f.icon }}
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-2">{{ f.title }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== HOW IT WORKS ===== -->
        <section class="py-24 bg-white">
            <div class="max-w-5xl mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-bold text-sm uppercase tracking-widest">Cara Kerja</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mt-2 mb-4">
                        Mulai dalam 3 Langkah Mudah
                    </h2>
                </div>
                <div class="grid md:grid-cols-3 gap-8 relative">
                    <!-- connector line -->
                    <div class="hidden md:block absolute top-10 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-violet-200 via-blue-200 to-emerald-200"></div>

                    <div
                        v-for="s in steps"
                        :key="s.num"
                        class="relative text-center"
                    >
                        <div class="relative inline-flex w-20 h-20 rounded-2xl items-center justify-center text-3xl mb-5 shadow-md"
                             style="background: linear-gradient(135deg, #ede9fe, #dbeafe);">
                            {{ s.icon }}
                            <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-violet-600 text-white text-xs font-black flex items-center justify-center shadow">
                                {{ s.num.slice(-1) }}
                            </span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-2">{{ s.title }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ s.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== ROLES ===== -->
        <section class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="text-emerald-600 font-bold text-sm uppercase tracking-widest">Peran Pengguna</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mt-2 mb-4">
                        Dirancang untuk Semua Pihak
                    </h2>
                    <p class="text-gray-500 max-w-xl mx-auto">
                        Setiap role memiliki akses yang terpisah dan terukur sesuai kebutuhan akademiknya.
                    </p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="r in roles"
                        :key="r.name"
                        :class="`${r.bg} rounded-2xl p-6 border-2 ${r.color} hover:shadow-lg hover:-translate-y-1 transition-all duration-300`"
                    >
                        <div class="text-4xl mb-3">{{ r.emoji }}</div>
                        <div :class="`badge ${r.badge} badge-sm font-bold mb-3`">{{ r.name }}</div>
                        <ul class="space-y-2">
                            <li
                                v-for="item in r.items"
                                :key="item"
                                class="flex items-start gap-2 text-sm text-gray-700"
                            >
                                <span class="text-emerald-500 font-bold mt-0.5">✓</span>
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CTA ===== -->
        <section class="py-24 relative overflow-hidden"
                 style="background: linear-gradient(135deg, #0f0c29 0%, #1a1040 50%, #0d1b3e 100%);">
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(circle at 2px 2px, rgba(139,92,246,0.4) 1px, transparent 0); background-size: 32px 32px;">
            </div>
            <div class="relative z-10 max-w-3xl mx-auto px-6 text-center">
                <div class="text-5xl mb-6">🎓</div>
                <h2 class="text-3xl sm:text-5xl font-black text-white mb-4">
                    Siap Transformasi<br>
                    <span style="background: linear-gradient(90deg, #a78bfa, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        Cara Belajarmu?
                    </span>
                </h2>
                <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
                    Bergabunglah dengan ribuan siswa dan guru yang sudah merasakan kemudahan belajar digital dengan teknologi Face Recognition.
                </p>
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl text-white font-black text-lg transition-all duration-200 hover:-translate-y-1 shadow-2xl"
                    style="background: linear-gradient(135deg, #7c3aed, #2563eb); box-shadow: 0 12px 40px rgba(124,58,237,0.5);"
                >
                    🚀 Masuk ke Platform Sekarang
                </Link>
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-gray-950 text-gray-500 py-10">
            <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <img
                        src="/assets/images/LogoSMKN5.png"
                        alt="Logo SMKN 5 Padang"
                        class="w-7 h-7 object-contain opacity-90"
                    />
                    <div class="flex flex-col leading-tight">
                        <span class="text-white font-bold text-sm">E-Learning SMKN 5 Padang</span>
                        <span class="text-gray-600 text-xs">Face Recognition System</span>
                    </div>
                </div>
                <p class="text-xs text-gray-600 text-center">
                    © {{ new Date().getFullYear() }} SMKN 5 Padang. Dibangun dengan ❤️ untuk pendidikan Indonesia.
                </p>
                <Link :href="route('login')" class="text-violet-400 hover:text-violet-300 text-sm font-medium transition-colors">
                    Login →
                </Link>
            </div>
        </footer>

    </div>
</template>

<style scoped>
@keyframes scanLine {
    0%   { top: 5%; opacity: 0.8; }
    50%  { top: 90%; opacity: 1; }
    100% { top: 5%; opacity: 0.8; }
}
</style>
