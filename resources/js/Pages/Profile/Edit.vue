<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Pengaturan Profil - EventSync Pro" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Pengaturan Profil</h1>
                <p class="text-xs text-slate-400 mt-1">Kelola data profil pribadi dan keamanan akun Anda</p>
            </div>

            <!-- Forms Container -->
            <div class="space-y-6 max-w-4xl">
                <!-- Update Profile Info Panel -->
                <div class="border border-slate-800 bg-slate-900/40 p-6 sm:p-8 rounded-2xl shadow-sm">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Update Password Panel -->
                <div class="border border-slate-800 bg-slate-900/40 p-6 sm:p-8 rounded-2xl shadow-sm">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account Panel -->
                <div class="border border-slate-800 bg-slate-900/40 p-6 sm:p-8 rounded-2xl shadow-sm">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
