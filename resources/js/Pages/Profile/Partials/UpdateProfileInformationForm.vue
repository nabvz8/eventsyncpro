<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    full_name: user.full_name,
    username: user.username,
    email: user.email,
});
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-bold text-white tracking-tight">
                Informasi Profil
            </h2>
            <p class="mt-1 text-xs text-slate-400">
                Perbarui nama lengkap, username, dan alamat email akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-4"
        >
            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input
                    id="full_name"
                    type="text"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.full_name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-1.5" :message="form.errors.full_name" />
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Username</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-500 text-sm select-none">@</span>
                    <input
                        id="username"
                        type="text"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 pl-8 pr-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                        v-model="form.username"
                        required
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.username" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <input
                    id="email"
                    type="email"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    v-model="form.email"
                    required
                    autocomplete="email"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-slate-400">
                    Alamat email Anda belum terverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-indigo-400 hover:text-indigo-300 underline font-semibold transition-colors"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-semibold text-green-400"
                >
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-indigo-600/10 transition-colors active:scale-[0.98]"
                    :disabled="form.processing"
                >
                    Simpan Perubahan
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-200"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-200"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs text-slate-400"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
