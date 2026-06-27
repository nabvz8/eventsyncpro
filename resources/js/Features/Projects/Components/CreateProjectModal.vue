<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        }
    });
};
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div>
                <h3 class="text-lg font-bold text-white">Buat Proyek Baru</h3>
                <p class="text-xs text-slate-400 mt-1">Buat kontainer proyek baru di bawah workspace aktif saat ini</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Nama Proyek</label>
                    <input
                        type="text"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                        v-model="form.name"
                        required
                        placeholder="Contoh: Pengembangan Website MVP, Redesign Landing Page"
                    />
                    <div v-if="form.errors.name" class="text-xs text-red-400 font-semibold mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Deskripsi Proyek (Opsional)</label>
                    <textarea
                        rows="3"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors resize-none"
                        v-model="form.description"
                        placeholder="Detail atau deskripsi singkat mengenai proyek ini"
                    ></textarea>
                    <div v-if="form.errors.description" class="text-xs text-red-400 font-semibold mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-4 py-2.5 text-xs font-semibold text-slate-300 transition-colors"
                        @click="emit('close')"
                        :disabled="form.processing"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Membuat...' : 'Buat Proyek' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
