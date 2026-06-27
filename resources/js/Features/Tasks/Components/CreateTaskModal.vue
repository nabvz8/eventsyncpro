<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { animate } from 'motion';

const props = defineProps({
    projectId: {
        type: String,
        required: true,
    },
    teams: {
        type: Array,
        default: () => [],
    },
    workspaceMembers: {
        type: Array,
        default: () => [],
    },
    defaultStatusId: {
        type: String,
        default: null,
    },
    statuses: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'created']);

const form = useForm({
    title: '',
    description: '',
    priority: 'medium',
    team_id: '',
    assigned_to: '',
    due_date: '',
    status_id: props.defaultStatusId || '',
});

const modalRef = ref(null);

// Animasi masuk modal
const onModalMounted = (el) => {
    if (el) {
        animate(el, 
            { opacity: [0, 1], scale: [0.96, 1], y: [16, 0] }, 
            { duration: 0.25, easing: 'ease-out' }
        );
    }
};

const submit = () => {
    form.post(route('tasks.store', { projId: props.projectId }), {
        onSuccess: () => {
            emit('created');
            emit('close');
            form.reset();
        },
    });
};

const closeModal = () => {
    if (modalRef.value) {
        animate(modalRef.value, 
            { opacity: [1, 0], scale: [1, 0.97], y: [0, 8] }, 
            { duration: 0.2, easing: 'ease-in' }
        ).then(() => emit('close'));
    } else {
        emit('close');
    }
};
</script>

<template>
    <!-- Overlay -->
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeModal"
    >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <!-- Modal Panel -->
        <div 
            :ref="onModalMounted"
            ref="modalRef"
            class="relative z-10 w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl shadow-slate-950/60"
        >
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                <div>
                    <h2 class="text-sm font-bold text-white">Buat Tugas Baru</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tambahkan tugas ke papan Kanban proyek</p>
                </div>
                <button 
                    @click="closeModal"
                    class="h-7 w-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="submit" class="p-6 space-y-4">

                <!-- Title -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        Judul Tugas <span class="text-red-400">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        placeholder="Deskripsikan tugas secara singkat..."
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                    />
                    <p v-if="form.errors.title" class="text-xs text-red-400">{{ form.errors.title }}</p>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Deskripsi</label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="Detail tambahan tentang tugas ini..."
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors resize-none"
                    ></textarea>
                </div>

                <!-- Row: Priority + Status -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Prioritas</label>
                        <select
                            v-model="form.priority"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                        >
                            <option value="low">🟢 Rendah</option>
                            <option value="medium">🟡 Sedang</option>
                            <option value="high">🟠 Tinggi</option>
                            <option value="urgent">🔴 Urgent</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Awal</label>
                        <select
                            v-model="form.status_id"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                        >
                            <option value="">-- Pilih Status --</option>
                            <option v-for="status in statuses" :key="status.id" :value="status.id">
                                {{ status.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Row: Tim + Assignee -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5" v-if="teams.length > 0">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tim Pelaksana</label>
                        <select
                            v-model="form.team_id"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                        >
                            <option value="">-- Tidak Ada Tim --</option>
                            <option v-for="team in teams" :key="team.id" :value="team.id">
                                {{ team.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ditugaskan ke</label>
                        <select
                            v-model="form.assigned_to"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                        >
                            <option value="">-- Belum Ditugaskan --</option>
                            <option v-for="member in workspaceMembers" :key="member.id" :value="member.id">
                                {{ member.full_name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Tenggat</label>
                    <input
                        v-model="form.due_date"
                        type="date"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-colors"
                    />
                </div>

                <!-- Actions -->
                <div class="flex gap-2.5 justify-end pt-2 border-t border-slate-800">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-5 py-2.5 text-xs font-semibold text-slate-300 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-xs font-semibold text-white transition-colors active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Buat Tugas' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
