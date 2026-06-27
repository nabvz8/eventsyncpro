<script setup>
import { ref, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { animate } from 'motion';

const page = usePage();
const workspaces = ref(page.props.auth.workspaces || []);
const activeWorkspace = ref(page.props.auth.active_workspace || null);
const user = ref(page.props.auth.user);

const showWorkspaceDropdown = ref(false);
const showCreateWorkspaceModal = ref(false);
const newWorkspaceName = ref('');
const newWorkspaceDescription = ref('');
const isSubmittingWorkspace = ref(false);

const sidebarRef = ref(null);
const mainContentRef = ref(null);

onMounted(() => {
    // Animasi masuk sidebar (slide in dari kiri)
    animate(sidebarRef.value, { x: [-260, 0], opacity: [0, 1] }, { duration: 0.4, easing: 'ease-out' });
    // Animasi masuk konten utama (fade in)
    animate(mainContentRef.value, { opacity: [0, 1] }, { duration: 0.5, easing: 'ease-out', delay: 0.1 });
});

const switchWorkspace = (wsId) => {
    showWorkspaceDropdown.value = false;
    router.post(route('workspaces.switch', { id: wsId }), {}, {
        onSuccess: () => {
            window.location.reload();
        }
    });
};

const createWorkspace = () => {
    if (!newWorkspaceName.value.trim()) return;
    isSubmittingWorkspace.value = true;
    
    router.post(route('workspaces.store'), {
        name: newWorkspaceName.value,
        description: newWorkspaceDescription.value,
    }, {
        onSuccess: () => {
            newWorkspaceName.value = '';
            newWorkspaceDescription.value = '';
            showCreateWorkspaceModal.value = false;
            isSubmittingWorkspace.value = false;
            window.location.reload();
        },
        onError: () => {
            isSubmittingWorkspace.value = false;
        }
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside 
            ref="sidebarRef" 
            class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between h-screen fixed z-20 transform -translate-x-full"
        >
            <div class="flex flex-col overflow-y-auto">
                <!-- Workspace Switcher -->
                <div class="p-4 border-b border-slate-800 relative">
                    <button 
                        @click="showWorkspaceDropdown = !showWorkspaceDropdown"
                        class="w-full flex items-center justify-between bg-slate-950/80 hover:bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition-all active:scale-[0.98]"
                    >
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-6 rounded-lg bg-indigo-600 flex items-center justify-center text-xs font-bold text-white uppercase shadow-sm">
                                {{ activeWorkspace ? activeWorkspace.name.charAt(0) : 'E' }}
                            </div>
                            <span class="truncate block max-w-[140px] text-white">
                                {{ activeWorkspace ? activeWorkspace.name : 'Pilih Workspace' }}
                            </span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </button>

                    <!-- Switcher Dropdown List -->
                    <div 
                        v-if="showWorkspaceDropdown" 
                        class="absolute left-4 right-4 mt-2 bg-slate-900 border border-slate-850 rounded-xl shadow-2xl p-1.5 z-30 max-h-60 overflow-y-auto space-y-1"
                    >
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-2.5 py-1.5">Switch Workspace</div>
                        
                        <button 
                            v-for="ws in workspaces" 
                            :key="ws.id"
                            @click="switchWorkspace(ws.id)"
                            class="w-full flex items-center gap-2 px-2.5 py-2 text-xs rounded-lg hover:bg-slate-800 transition-colors text-left"
                            :class="[activeWorkspace && activeWorkspace.id === ws.id ? 'bg-indigo-950/40 text-indigo-300 font-semibold' : 'text-slate-300']"
                        >
                            <div class="h-5 w-5 rounded bg-slate-800 flex items-center justify-center font-bold text-[10px] text-slate-300 uppercase">
                                {{ ws.name.charAt(0) }}
                            </div>
                            <span class="truncate">{{ ws.name }}</span>
                        </button>

                        <div class="border-t border-slate-850 my-1"></div>

                        <button 
                            @click="showCreateWorkspaceModal = true; showWorkspaceDropdown = false"
                            class="w-full flex items-center gap-2 px-2.5 py-2 text-xs rounded-lg hover:bg-slate-800 transition-colors text-left text-indigo-400 font-semibold"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Buat Workspace</span>
                        </button>
                    </div>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5">
                    <Link 
                        :href="route('dashboard')"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl font-medium transition-colors"
                        :class="[route().current('dashboard') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-850 hover:text-slate-200']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>
                    
                    <Link 
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl font-medium transition-colors"
                        :class="[route().current('profile.edit') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-850 hover:text-slate-200']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>Profile Settings</span>
                    </Link>
                </nav>
            </div>

            <!-- User Info Section -->
            <div class="p-4 border-t border-slate-800 flex items-center justify-between bg-slate-950/20">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="h-8 w-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-white">
                        {{ user.full_name ? user.full_name.charAt(0) : 'U' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block text-xs font-bold text-white truncate">{{ user.full_name }}</span>
                        <span class="block text-[10px] text-slate-500 truncate">@{{ user.username }}</span>
                    </div>
                </div>
                <button 
                    @click="logout"
                    class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-slate-800/50 rounded-lg transition-all"
                    title="Log Out"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- MAIN LAYOUT WRAPPER -->
        <div class="flex-1 flex flex-col min-h-screen pl-64">
            
            <!-- HEADER -->
            <header class="h-16 border-b border-slate-800 bg-slate-900/60 backdrop-blur flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <span class="text-slate-400 text-sm font-semibold">Workspace:</span>
                    <span class="text-white font-bold text-sm bg-slate-950 border border-slate-850 px-3 py-1.5 rounded-lg select-none">
                        {{ activeWorkspace ? activeWorkspace.name : 'Tidak Ada' }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <slot name="header_right" />
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main ref="mainContentRef" class="flex-1 overflow-y-auto p-8 opacity-0">
                <slot />
            </main>
        </div>

        <!-- CREATE WORKSPACE MODAL -->
        <div 
            v-if="showCreateWorkspaceModal" 
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in"
        >
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-white">Buat Workspace Baru</h3>
                    <p class="text-xs text-slate-400 mt-1">Buat ruang kerja tambahan untuk tim Anda</p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Nama Workspace</label>
                        <input
                            type="text"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                            v-model="newWorkspaceName"
                            required
                            placeholder="Contoh: R&D Team, HR Department"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Deskripsi (Opsional)</label>
                        <textarea
                            rows="2"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors resize-none"
                            v-model="newWorkspaceDescription"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-4 py-2 text-xs font-semibold text-slate-300 transition-colors"
                        @click="showCreateWorkspaceModal = false"
                        :disabled="isSubmittingWorkspace"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                        @click="createWorkspace"
                        :disabled="isSubmittingWorkspace"
                    >
                        {{ isSubmittingWorkspace ? 'Membuat...' : 'Buat Workspace' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>
