<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-bold text-white tracking-tight">
                Perbarui Password
            </h2>
            <p class="mt-1 text-xs text-slate-400">
                Pastikan akun Anda menggunakan password yang aman dan acak untuk menjaga keamanan.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password Saat Ini</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.current_password" class="mt-1.5" />
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password Baru</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" class="mt-1.5" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-1.5" />
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
