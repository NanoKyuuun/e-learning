<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { 
    Menu as MenuIcon,
    X as CloseIcon,
    LogOut,
    User as UserIcon,
    ChevronDown
} from 'lucide-vue-next';

const page = usePage();
const isSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const logout = () => {
    router.post(window.route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-base-200">
        <!-- Navbar -->
        <header class="navbar bg-base-100 shadow-sm sticky top-0 z-50 px-4 h-16 border-b border-base-200">
            <div class="flex-none lg:hidden">
                <button @click="toggleSidebar" class="btn btn-square btn-ghost">
                    <MenuIcon v-if="!isSidebarOpen" class="w-6 h-6" />
                    <CloseIcon v-else class="w-6 h-6" />
                </button>
            </div>
            <div class="flex-1 px-2 mx-2">
                <span class="font-black text-2xl text-primary tracking-tighter">E-LEARNING</span>
            </div>
            <div class="flex-none gap-4 items-center">
                <!-- User Profile Dropdown -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost flex items-center gap-2 px-2 hover:bg-base-200 rounded-lg transition-colors">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-9 shadow-inner font-bold">
                                <span>{{ $page.props.auth.user.full_name.charAt(0) }}</span>
                            </div>
                        </div>
                        <div class="hidden md:flex flex-col items-start leading-tight">
                            <span class="text-sm font-bold truncate max-w-[120px]">{{ $page.props.auth.user.full_name }}</span>
                            <span class="text-[10px] opacity-60 uppercase font-black tracking-wider">
                                {{ $page.props.auth.user.roles[0]?.name.replace('-', ' ') }}
                            </span>
                        </div>
                        <ChevronDown class="w-4 h-4 opacity-40 hidden md:block" />
                    </div>
                    <ul tabindex="0" class="mt-3 z-[1] p-2 shadow-2xl menu menu-sm dropdown-content bg-base-100 rounded-xl w-60 border border-base-200">
                        <li class="menu-title px-4 py-3 border-b border-base-100 mb-1">
                            <div class="flex flex-col">
                                <span class="font-bold text-base-content">{{ $page.props.auth.user.full_name }}</span>
                                <span class="text-xs opacity-50">{{ $page.props.auth.user.email }}</span>
                            </div>
                        </li>
                        <li><Link :href="route('profile.edit')" class="py-3 flex items-center gap-3"><UserIcon class="w-4 h-4" /> Profil Saya</Link></li>
                        <div class="divider my-1"></div>
                        <li><button @click="logout" class="py-3 text-error font-bold flex items-center gap-3"><LogOut class="w-4 h-4" /> Logout</button></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="flex">
            <!-- Sidebar -->
            <aside 
                :class="[
                    'fixed lg:static z-40 h-[calc(100vh-64px)] w-64 bg-base-100 shadow-xl transition-all duration-300 transform border-r border-base-200',
                    isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <div class="h-full overflow-y-auto px-4 py-6 scrollbar-thin scrollbar-thumb-base-300">
                    <ul class="menu w-full gap-1 p-0">
                        <slot name="sidebar"></slot>
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-4 md:p-8 w-full lg:max-w-[calc(100vw-256px)] min-h-[calc(100vh-64px)] bg-base-200 overflow-x-hidden">
                <div class="container mx-auto">
                    <slot></slot>
                </div>
            </main>
        </div>

        <!-- Overlay for mobile sidebar -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-30 lg:hidden backdrop-blur-[2px] transition-opacity duration-300"
        ></div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: transparent;
    border-radius: 20px;
}
.scrollbar-thin:hover::-webkit-scrollbar-thumb {
    background-color: hsl(var(--bc) / 0.1);
}
</style>
