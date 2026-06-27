<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { animate, stagger } from 'motion';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

onMounted(() => {
    // Efek stagger masuk untuk setiap input agar terasa hidup
    animate(
        ".form-item",
        { opacity: [0, 1], x: [-10, 0] },
        { delay: stagger(0.08), duration: 0.4, easing: "ease-out" }
    );
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke EventSync Pro" />

        <div v-if="status" class="form-item mb-4 text-sm font-medium text-green-400 opacity-0">
            {{ status }}
        </div>

        <div class="form-item mb-6 opacity-0">
            <h2 class="text-xl font-bold text-white tracking-tight">Selamat Datang Kembali</h2>
            <p class="text-xs text-slate-400 mt-1">Masuk untuk mengakses ruang kerja dan tugas Anda</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email -->
            <div class="form-item opacity-0">
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <input
                    id="email"
                    type="email"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.email"
                    required
                    autofocus
                    placeholder="nama@email.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="form-item opacity-0">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Password</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        Lupa Password?
                    </Link>
                </div>
                <input
                    id="password"
                    type="password"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.password"
                    required
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="form-item opacity-0 block">
                <label class="flex items-center cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        v-model="form.remember" 
                        class="rounded border-slate-800 bg-slate-950/80 text-indigo-600 shadow-sm focus:ring-indigo-500/30 focus:ring-offset-0 focus:ring-1"
                    />
                    <span class="ms-2 text-xs text-slate-400">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="form-item opacity-0 pt-2 flex flex-col gap-3">
                <button
                    type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all active:scale-[0.98]"
                    :class="{ 'opacity-50 pointer-events-none': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk Akun
                </button>

                <div class="text-center mt-2">
                    <span class="text-xs text-slate-400">Belum punya akun? </span>
                    <Link
                        :href="route('register')"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold transition-colors"
                    >
                        Daftar sekarang
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
