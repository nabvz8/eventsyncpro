<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { animate } from 'motion';

const cardRef = ref(null);
const logoRef = ref(null);

onMounted(() => {
    // Animasi logo memudar dan membesar sedikit
    animate(logoRef.value, { opacity: [0, 1], scale: [0.9, 1] }, { duration: 0.5, easing: 'ease-out' });
    
    // Animasi kartu form masuk dengan geser ke atas dan membesar lembut (elastic effect)
    animate(
        cardRef.value, 
        { opacity: [0, 1], y: [40, 0], scale: [0.98, 1] }, 
        { duration: 0.6, easing: 'ease-out', delay: 0.1 }
    );
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4 sm:p-6"
    >
        <!-- Logo / Branding -->
        <div ref="logoRef" class="mb-6 opacity-0 flex flex-col items-center">
            <Link href="/" class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <span class="text-white font-extrabold text-xl tracking-tighter">E</span>
                </div>
                <span class="text-white font-extrabold text-2xl tracking-tight bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">EventSync <span class="text-indigo-400">Pro</span></span>
            </Link>
            <p class="text-xs text-slate-400 mt-2 tracking-wider uppercase font-semibold">Workspace & Task Orchestration</p>
        </div>

        <!-- Form Card (Glassmorphism) -->
        <div
            ref="cardRef"
            class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-xl px-8 py-8 shadow-2xl shadow-indigo-950/40 opacity-0"
        >
            <slot />
        </div>
    </div>
</template>
