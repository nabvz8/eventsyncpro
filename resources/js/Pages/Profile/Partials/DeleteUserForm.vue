<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-bold text-white tracking-tight">
                Hapus Akun
            </h2>
            <p class="mt-1 text-xs text-slate-400">
                Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.
            </p>
        </header>

        <button 
            type="button"
            class="rounded-lg bg-red-600 hover:bg-red-500 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-red-600/10 transition-colors active:scale-[0.98]"
            @click="confirmUserDeletion"
        >
            Hapus Akun
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-2xl space-y-4">
                <div>
                    <h2 class="text-lg font-bold text-white">
                        Apakah Anda yakin ingin menghapus akun?
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">
                        Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Harap masukkan password Anda untuk mengonfirmasi penghapusan akun secara permanen.
                    </p>
                </div>

                <div class="space-y-2">
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                        placeholder="Password Anda"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-1.5" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-800 bg-slate-950/60 hover:bg-slate-850 px-4 py-2 text-xs font-semibold text-slate-300 transition-colors"
                        @click="closeModal"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 hover:bg-red-500 px-4 py-2 text-xs font-semibold text-white transition-colors active:scale-[0.98]"
                        :class="{ 'opacity-50 pointer-events-none': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Hapus Akun Permanen
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
