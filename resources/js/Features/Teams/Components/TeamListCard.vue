<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    team: {
        type: Object,
        required: true,
    },
    workspaceMembers: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    }
});

const showInviteForm = ref(false);

const form = useForm({
    user_id: '',
    role: 'member',
});

const submitInvite = () => {
    if (!form.user_id) return;
    form.post(route('teams.members.store', { teamId: props.team.id }), {
        onSuccess: () => {
            form.reset();
            showInviteForm.value = false;
        }
    });
};
</script>

<template>
    <div class="rounded-2xl border border-slate-850 bg-slate-900/30 p-5 shadow-sm space-y-4">
        <!-- Team Header -->
        <div class="flex items-start justify-between gap-4 border-b border-slate-850 pb-3">
            <div>
                <h3 class="font-bold text-white text-base tracking-tight">{{ team.name }}</h3>
                <p class="text-xs text-slate-450 mt-0.5 leading-relaxed">{{ team.description || 'Tidak ada deskripsi tim.' }}</p>
            </div>
            <!-- Add Member Button -->
            <button
                v-if="canManage"
                @click="showInviteForm = !showInviteForm"
                class="rounded-lg border border-slate-800 hover:bg-slate-850 px-2.5 py-1.5 text-[10px] font-bold text-indigo-400 uppercase tracking-wider transition-colors active:scale-[0.98]"
            >
                {{ showInviteForm ? 'Batal' : 'Tambah Anggota' }}
            </button>
        </div>

        <!-- Invite Form -->
        <div 
            v-if="showInviteForm" 
            class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl space-y-3"
        >
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Tambah Anggota ke Tim</h4>
            <form @submit.prevent="submitInvite" class="grid gap-3 sm:grid-cols-3 items-end">
                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Pilih Anggota</label>
                    <select
                        v-model="form.user_id"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none transition-colors"
                        required
                    >
                        <option value="" disabled>-- Pilih Anggota --</option>
                        <option 
                            v-for="member in workspaceMembers" 
                            :key="member.id" 
                            :value="member.id"
                        >
                            {{ member.full_name }}
                        </option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Peran Tim</label>
                    <select
                        v-model="form.role"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none transition-colors"
                        required
                    >
                        <option value="leader">Team Leader</option>
                        <option value="co_leader">Co-Team Leader</option>
                        <option value="member">Regular Member</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Menambahkan...' : 'Tambahkan' }}
                </button>
            </form>
            <div v-if="form.errors.user_id" class="text-xs text-red-400 font-semibold mt-1">{{ form.errors.user_id }}</div>
        </div>

        <!-- Team Members List -->
        <div class="space-y-2">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Anggota Pelaksana ({{ team.members ? team.members.length : 0 }})</div>
            
            <div 
                v-if="!team.members || team.members.length === 0" 
                class="py-4 text-center border border-dashed border-slate-850 rounded-xl text-xs text-slate-500"
            >
                Belum ada anggota di tim ini.
            </div>

            <div v-else class="space-y-1.5">
                <div 
                    v-for="member in team.members" 
                    :key="member.id"
                    class="flex items-center justify-between bg-slate-950/20 border border-slate-850/50 rounded-xl px-3 py-2 text-xs"
                >
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-slate-800 flex items-center justify-center font-bold text-[10px] text-slate-300 select-none">
                            {{ member.full_name.charAt(0) }}
                        </div>
                        <span class="font-medium text-white">{{ member.full_name }}</span>
                    </div>
                    <span 
                        class="rounded px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wider"
                        :class="[
                            member.pivot.role_team === 'leader' ? 'bg-indigo-950/60 border border-indigo-900/40 text-indigo-300' :
                            member.pivot.role_team === 'co_leader' ? 'bg-cyan-950/60 border border-cyan-900/40 text-cyan-300' :
                            'bg-slate-850 border border-slate-800 text-slate-400'
                        ]"
                    >
                        {{ member.pivot.role_team }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
