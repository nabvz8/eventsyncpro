<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { animate } from 'motion';

const props = defineProps({
    user: Object,
});

const currentStep = ref(1);

const form = useForm({
    full_name: props.user.full_name || '',
    workspace_name: '',
    workspace_description: '',
    create_default_project: true,
    project_name: 'Proyek Utama',
    project_description: 'Proyek default untuk memulai kolaborasi.',
});

const stepContainerRef = ref(null);

const animateStepTransition = (direction) => {
    // Animasi transisi step (slide out and slide in)
    animate(
        stepContainerRef.value,
        { opacity: [1, 0], x: [0, direction === 'next' ? -20 : 20] },
        { duration: 0.25 }
    ).finished.then(() => {
        if (direction === 'next') {
            currentStep.value++;
        } else {
            currentStep.value--;
        }
        animate(
            stepContainerRef.value,
            { opacity: [0, 1], x: [direction === 'next' ? 20 : -20, 0] },
            { duration: 0.3, easing: 'ease-out' }
        );
    });
};

const nextStep = () => {
    if (currentStep.value === 1 && !form.full_name.trim()) return;
    if (currentStep.value === 2 && !form.workspace_name.trim()) return;
    
    animateStepTransition('next');
};

const prevStep = () => {
    if (currentStep.value === 1) return;
    animateStepTransition('prev');
};

const submit = () => {
    form.post('/onboarding');
};
</script>

<template>
    <Head title="Selamat Datang - Lengkapi Profil Anda" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4 text-white">
        <!-- Progress Stepper Indicator -->
        <div class="mb-8 w-full max-w-md flex justify-between items-center px-4">
            <div v-for="step in 3" :key="step" class="flex items-center flex-1 last:flex-initial">
                <div 
                    class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 border"
                    :class="[
                        step === currentStep 
                            ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/30 scale-110' 
                            : step < currentStep 
                                ? 'bg-indigo-950/80 border-indigo-600 text-indigo-400' 
                                : 'bg-slate-900/60 border-slate-800 text-slate-500'
                    ]"
                >
                    {{ step }}
                </div>
                <div 
                    v-if="step < 3" 
                    class="flex-1 h-0.5 mx-2 transition-colors duration-300"
                    :class="[step < currentStep ? 'bg-indigo-600' : 'bg-slate-800']"
                ></div>
            </div>
        </div>

        <!-- Glassmorphism Form Card -->
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-xl px-8 py-8 shadow-2xl shadow-indigo-950/40">
            <div ref="stepContainerRef" class="space-y-6">
                <!-- STEP 1: Profil Pengguna -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-white">Lengkapi Profil Anda</h2>
                        <p class="text-xs text-slate-400 mt-1">Gunakan nama asli Anda agar rekan tim mudah mengenali Anda</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Nama Lengkap</label>
                        <input
                            type="text"
                            class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                            v-model="form.full_name"
                            required
                            placeholder="Contoh: Budi Setiawan"
                        />
                    </div>
                </div>

                <!-- STEP 2: Info Workspace -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-white">Buat Workspace</h2>
                        <p class="text-xs text-slate-400 mt-1">Ruang kerja untuk mengelompokkan proyek, tim, dan tugas</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Nama Ruang Kerja</label>
                            <input
                                type="text"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                v-model="form.workspace_name"
                                required
                                placeholder="Contoh: Marketing Team, EventSync Inc"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Deskripsi (Opsional)</label>
                            <textarea
                                rows="3"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors resize-none"
                                v-model="form.workspace_description"
                                placeholder="Tuliskan tujuan ruang kerja ini..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Template Proyek -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-white">Proyek Perdana</h2>
                        <p class="text-xs text-slate-400 mt-1">Siapkan proyek awal untuk langsung memulai tugas pertama Anda</p>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                class="rounded border-slate-800 bg-slate-950/80 text-indigo-600 shadow-sm focus:ring-indigo-500/30 focus:ring-offset-0 focus:ring-1"
                                v-model="form.create_default_project"
                            />
                            <span class="ms-2.5 text-sm text-slate-300">Buat proyek default dengan template Kanban</span>
                        </label>

                        <div v-if="form.create_default_project" class="space-y-3 p-4 rounded-xl border border-slate-800 bg-slate-950/40">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Nama Proyek</label>
                                <input
                                    type="text"
                                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                    v-model="form.project_name"
                                    placeholder="Contoh: Launching Campaign"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Deskripsi Proyek</label>
                                <input
                                    type="text"
                                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                    v-model="form.project_description"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div class="pt-4 flex gap-3">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        class="flex-1 rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-900/60 px-4 py-2.5 text-sm font-semibold text-slate-300 transition-colors active:scale-[0.98]"
                        @click="prevStep"
                    >
                        Kembali
                    </button>

                    <button
                        v-if="currentStep < 3"
                        type="button"
                        class="flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 transition-colors active:scale-[0.98]"
                        @click="nextStep"
                    >
                        Lanjutkan
                    </button>

                    <button
                        v-else
                        type="button"
                        class="flex-1 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 transition-all active:scale-[0.98]"
                        :class="{ 'opacity-50 pointer-events-none': form.processing }"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        Selesaikan Setup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
