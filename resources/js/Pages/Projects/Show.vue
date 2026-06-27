<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TeamListCard from '@/Features/Teams/Components/TeamListCard.vue';
import KanbanBoard from '@/Features/Tasks/Components/KanbanBoard.vue';
import CreateTaskModal from '@/Features/Tasks/Components/CreateTaskModal.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    workspaceMembers: {
        type: Array,
        default: () => [],
    },
    myRole: {
        type: String,
        default: null,
    },
    boardData: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('board'); // 'board', 'teams', or 'members'
const showAddMemberForm = ref(false);
const showCreateTeamForm = ref(false);
const showCreateTaskModal = ref(false);
const defaultTaskStatusId = ref(null);

const isLeader = computed(() => props.myRole === 'leader' || props.myRole === 'co_leader');

const memberForm = useForm({
    user_id: '',
    role: 'co_leader',
});

const teamForm = useForm({
    name: '',
    description: '',
});

const addMember = () => {
    if (!memberForm.user_id) return;
    memberForm.post(route('projects.members.store', { id: props.project.id }), {
        onSuccess: () => {
            memberForm.reset();
            showAddMemberForm.value = false;
        }
    });
};

const createTeam = () => {
    if (!teamForm.name) return;
    teamForm.post(route('projects.teams.store', { projId: props.project.id }), {
        onSuccess: () => {
            teamForm.reset();
            showCreateTeamForm.value = false;
        }
    });
};

const openCreateTask = (statusId = null) => {
    defaultTaskStatusId.value = statusId;
    showCreateTaskModal.value = true;
};
</script>

<template>
    <Head :title="project.name + ' - EventSync Pro'" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Breadcrumbs / Back button -->
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <Link :href="route('dashboard')" class="hover:text-slate-300 transition-colors">Dashboard</Link>
                <span>/</span>
                <span class="text-slate-300 font-medium">{{ project.name }}</span>
            </div>

            <!-- Project Details Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-850 pb-6">
                <div class="space-y-1">
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ project.name }}</h1>
                    <p class="text-sm text-slate-400 max-w-2xl leading-relaxed">{{ project.description || 'Tidak ada deskripsi proyek.' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Tab Switcher -->
                    <div class="inline-flex rounded-lg bg-slate-900 border border-slate-800 p-1">
                        <button
                            @click="activeTab = 'board'"
                            class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[activeTab === 'board' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                        >
                            Kanban Board
                        </button>
                        <button
                            @click="activeTab = 'teams'"
                            class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[activeTab === 'teams' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                        >
                            Tim Pelaksana
                        </button>
                        <button
                            @click="activeTab = 'members'"
                            class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[activeTab === 'members' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                        >
                            Anggota Proyek
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 1: KANBAN BOARD -->
            <div v-if="activeTab === 'board'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight">Papan Kanban</h2>
                        <p class="text-xs text-slate-500">Seret kartu tugas antar kolom untuk mengubah status pengerjaannya</p>
                    </div>
                    <!-- Tombol Tambah Tugas -->
                    <button
                        @click="openCreateTask()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98] shadow-md shadow-indigo-900/30"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Tambah Tugas</span>
                    </button>
                </div>

                <!-- Kanban Board Component -->
                <KanbanBoard
                    :board-data="boardData"
                    :can-create-task="true"
                    @add-task="openCreateTask"
                />
            </div>

            <!-- TAB 2: TIM PELAKSANA (TEAMS) -->
            <div v-if="activeTab === 'teams'" class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight">Tim Pelaksana Proyek</h2>
                        <p class="text-xs text-slate-500">Kelola tim-tim yang bertugas menyelesaikan proyek ini</p>
                    </div>
                    <!-- Create Team Button -->
                    <button
                        v-if="isLeader"
                        @click="showCreateTeamForm = !showCreateTeamForm"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Buat Tim Baru</span>
                    </button>
                </div>

                <!-- Create Team Form Card -->
                <div
                    v-if="showCreateTeamForm"
                    class="border border-slate-800 bg-slate-900/30 p-5 rounded-2xl shadow-sm max-w-xl space-y-4"
                >
                    <h3 class="font-bold text-white text-sm">Buat Tim Pelaksana Baru</h3>
                    <form @submit.prevent="createTeam" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Tim</label>
                            <input
                                type="text"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-colors"
                                v-model="teamForm.name"
                                required
                                placeholder="Contoh: Backend Team, UI/UX Designers"
                            />
                            <div v-if="teamForm.errors.name" class="text-xs text-red-400 font-semibold mt-1">{{ teamForm.errors.name }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Tim</label>
                            <textarea
                                rows="2"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-colors resize-none"
                                v-model="teamForm.description"
                                placeholder="Fokus atau deskripsi singkat tugas tim ini"
                            ></textarea>
                            <div v-if="teamForm.errors.description" class="text-xs text-red-400 font-semibold mt-1">{{ teamForm.errors.description }}</div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-4 py-2.5 text-xs font-semibold text-slate-300 transition-colors"
                                @click="showCreateTeamForm = false"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                                :disabled="teamForm.processing"
                            >
                                {{ teamForm.processing ? 'Membuat...' : 'Buat Tim' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Empty State Teams -->
                <div 
                    v-if="!project.teams || project.teams.length === 0" 
                    class="py-12 border border-dashed border-slate-850 rounded-2xl flex flex-col items-center justify-center text-center space-y-3"
                >
                    <div class="h-10 w-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Belum Ada Tim</h3>
                        <p class="text-xs text-slate-500 mt-1">Buat tim pelaksana pertama untuk memulai kolaborasi.</p>
                    </div>
                </div>

                <!-- Teams Cards Grid -->
                <div v-else class="grid gap-6 md:grid-cols-2">
                    <TeamListCard 
                        v-for="team in project.teams"
                        :key="team.id"
                        :team="team"
                        :workspace-members="workspaceMembers"
                        :can-manage="isLeader"
                    />
                </div>
            </div>

            <!-- TAB 3: PROJECT MEMBERS -->
            <div v-if="activeTab === 'members'" class="space-y-4 max-w-3xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight">Anggota Proyek</h2>
                        <p class="text-xs text-slate-500">Daftar pengguna dengan akses khusus proyek ini</p>
                    </div>
                    <!-- Button to open add member form -->
                    <button
                        v-if="myRole === 'leader'"
                        @click="showAddMemberForm = !showAddMemberForm"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Anggota</span>
                    </button>
                </div>

                <!-- Add Member Form Card -->
                <div
                    v-if="showAddMemberForm"
                    class="border border-slate-800 bg-slate-900/30 p-5 rounded-2xl shadow-sm space-y-4"
                >
                    <h3 class="font-bold text-white text-sm">Tambah Anggota Baru ke Proyek</h3>
                    <form @submit.prevent="addMember" class="grid gap-4 sm:grid-cols-3 items-end">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Pilih Anggota</label>
                            <select
                                v-model="memberForm.user_id"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                required
                            >
                                <option value="" disabled>-- Pilih Anggota --</option>
                                <option 
                                    v-for="member in workspaceMembers" 
                                    :key="member.id" 
                                    :value="member.id"
                                >
                                    {{ member.full_name }} (@{{ member.username }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Peran Proyek</label>
                            <select
                                v-model="memberForm.role"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                required
                            >
                                <option value="leader">Project Leader</option>
                                <option value="co_leader">Co-Project Leader</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-4 py-2.5 text-xs font-semibold text-slate-300 transition-colors flex-1"
                                @click="showAddMemberForm = false"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-xs font-semibold text-white transition-colors flex-1"
                                :disabled="memberForm.processing"
                            >
                                {{ memberForm.processing ? 'Menambahkan...' : 'Tambah' }}
                            </button>
                        </div>
                    </form>
                    <div v-if="memberForm.errors.user_id" class="text-xs text-red-400 font-semibold">{{ memberForm.errors.user_id }}</div>
                </div>

                <!-- Members List -->
                <div class="border border-slate-850 bg-slate-900/20 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm text-slate-350">
                        <thead class="bg-slate-950/40 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-850">
                            <tr>
                                <th class="px-6 py-4">Nama Anggota</th>
                                <th class="px-6 py-4">Username</th>
                                <th class="px-6 py-4">Peran Proyek</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850">
                            <tr 
                                v-for="member in project.members" 
                                :key="member.id"
                                class="hover:bg-slate-950/20 transition-colors"
                            >
                                <td class="px-6 py-4 flex items-center gap-3 font-semibold text-white">
                                    <div class="h-7 w-7 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                                        {{ member.full_name.charAt(0) }}
                                    </div>
                                    <span>{{ member.full_name }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs">@{{ member.username }}</td>
                                <td class="px-6 py-4">
                                    <span 
                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold select-none border"
                                        :class="[
                                            member.pivot.role_project === 'leader'
                                                ? 'bg-indigo-950/40 border-indigo-900/40 text-indigo-300'
                                                : 'bg-emerald-950/40 border-emerald-900/40 text-emerald-300'
                                        ]"
                                    >
                                        {{ member.pivot.role_project === 'leader' ? 'Project Leader' : 'Co-Project Leader' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>

    <!-- Create Task Modal (rendered outside AuthenticatedLayout untuk portal ke body) -->
    <CreateTaskModal
        v-if="showCreateTaskModal"
        :project-id="project.id"
        :teams="project.teams || []"
        :workspace-members="workspaceMembers"
        :default-status-id="defaultTaskStatusId"
        :statuses="boardData"
        @close="showCreateTaskModal = false"
        @created="showCreateTaskModal = false"
    />
</template>
