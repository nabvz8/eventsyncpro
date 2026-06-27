<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { animate, stagger } from 'motion';

const form = useForm({
    username: '',
    full_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const formItemsRef = ref([]);

onMounted(() => {
    // Efek stagger masuk untuk setiap input agar terasa hidup
    animate(
        ".form-item",
        { opacity: [0, 1], x: [-10, 0] },
        { delay: stagger(0.08), duration: 0.4, easing: "ease-out" }
    );
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Buat Akun EventSync Pro" />

        <div class="form-item mb-6 opacity-0">
            <h2 class="text-xl font-bold text-white tracking-tight">Buat Akun Baru</h2>
            <p class="text-xs text-slate-400 mt-1">Mulai koordinasikan ruang kerja dan tugas tim Anda</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Full Name -->
            <div class="form-item opacity-0">
                <label for="full_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input
                    id="full_name"
                    type="text"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.full_name"
                    required
                    autofocus
                    placeholder="Contoh: Budi Setiawan"
                />
                <InputError class="mt-1.5" :message="form.errors.full_name" />
            </div>

            <!-- Username -->
            <div class="form-item opacity-0">
                <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Username</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-500 text-sm select-none">@</span>
                    <input
                        id="username"
                        type="text"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 pl-8 pr-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                        v-model="form.username"
                        required
                        placeholder="budisetiawan"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.username" />
            </div>

            <!-- Email -->
            <div class="form-item opacity-0">
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <input
                    id="email"
                    type="email"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.email"
                    required
                    placeholder="nama@email.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="form-item opacity-0">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
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

            <!-- Confirm Password -->
            <div class="form-item opacity-0">
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.password_confirmation"
                    required
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <!-- Buttons -->
            <div class="form-item opacity-0 pt-2 flex flex-col gap-3">
                <button
                    type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/10 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all active:scale-[0.98]"
                    :class="{ 'opacity-50 pointer-events-none': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar Sekarang
                </button>

                <div class="text-center mt-2">
                    <span class="text-xs text-slate-400">Sudah punya akun? </span>
                    <Link
                        :href="route('login')"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold transition-colors"
                    >
                        Masuk di sini
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
