<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateProjectModal from '@/Features/Projects/Components/CreateProjectModal.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    }
});

const page = usePage();
const user = ref(page.props.auth.user);
const activeWorkspace = ref(page.props.auth.active_workspace);

const showCreateProjectModal = ref(false);
</script>

<template>
    <Head title="Dashboard - EventSync Pro" />

    <AuthenticatedLayout>
        <!-- Custom header right button if needed -->
        <template #header_right>
            <span class="text-xs text-indigo-400 bg-indigo-950/40 border border-indigo-900/40 px-3 py-1.5 rounded-lg font-semibold tracking-wider uppercase select-none">
                MVP Active
            </span>
        </template>

        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/40 p-8 shadow-lg">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-cyan-500/10 blur-3xl"></div>
                
                <div class="relative z-10 space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Selamat Datang Kembali, {{ user.full_name }}!
                    </h1>
                    <p class="max-w-xl text-sm text-slate-400 leading-relaxed">
                        Anda saat ini sedang berada di ruang kerja <span class="text-indigo-400 font-semibold">"{{ activeWorkspace ? activeWorkspace.name : 'Tidak Ada Workspace' }}"</span>. Kelola proyek, tim, dan tugas kolaboratif Anda dari panel ini.
                    </p>
                </div>
            </div>

            <!-- Stats & Quick Actions Overview -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Card 1 -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-5 shadow-sm space-y-3">
                    <div class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Total Proyek</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">{{ projects.length }}</span>
                        <span class="text-xs text-indigo-400 font-medium">Dalam Workspace Ini</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-5 shadow-sm space-y-3">
                    <div class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Anggota Tim</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">1</span>
                        <span class="text-xs text-slate-400 font-medium">(Hanya Anda)</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-5 shadow-sm space-y-3">
                    <div class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Tugas Aktif</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">0</span>
                        <span class="text-xs text-slate-500 font-medium">Belum ada tugas</span>
                    </div>
                </div>
            </div>

            <!-- Project List Section -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight">Daftar Proyek Anda</h2>
                        <p class="text-xs text-slate-400">Semua proyek di dalam workspace aktif saat ini</p>
                    </div>
                    <button 
                        @click="showCreateProjectModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Buat Proyek</span>
                    </button>
                </div>

                <!-- Empty State -->
                <div 
                    v-if="projects.length === 0" 
                    class="py-12 flex flex-col items-center justify-center text-center space-y-3"
                >
                    <div class="h-12 w-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12M6 12h12M6 18h12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Belum Ada Proyek</h3>
                        <p class="text-xs text-slate-500 mt-1">Mulai buat proyek pertama Anda di workspace ini</p>
                    </div>
                    <button 
                        @click="showCreateProjectModal = true"
                        class="rounded-lg border border-slate-800 hover:bg-slate-850 px-4 py-2 text-xs font-semibold text-indigo-400 transition-colors"
                    >
                        Buat Proyek Sekarang
                    </button>
                </div>

                <!-- Projects Grid -->
                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="project in projects"
                        :key="project.id"
                        :href="route('projects.show', { id: project.id })"
                        class="group relative rounded-xl border border-slate-800 bg-slate-950/40 p-5 hover:border-indigo-500/50 hover:bg-slate-900/40 transition-all cursor-pointer block"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m-1.396-4.5L9.214 12l-4.178 2.25M10.286 9.75L6.107 12l4.179 2.25m-1.396-4.5L13.071 12l-4.178 2.25m6.215-4.5L10.93 12l4.178 2.25m-1.396-4.5L17.893 12l-4.179 2.25m6.214-4.5L15.75 12l4.179 2.25m-1.396-4.5L21.75 12l-5.964-2.25" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold text-indigo-400 bg-indigo-950/50 border border-indigo-900/30 px-2 py-0.5 rounded uppercase">Kanban</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white group-hover:text-indigo-400 transition-colors truncate">{{ project.name }}</h3>
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ project.description || 'Tidak ada deskripsi.' }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

        </div>

        <!-- CREATE PROJECT MODAL -->
        <CreateProjectModal 
            :show="showCreateProjectModal" 
            @close="showCreateProjectModal = false" 
        />
    </AuthenticatedLayout>
</template>
