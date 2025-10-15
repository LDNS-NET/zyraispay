<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout :hideLogo="true"> <!-- Hide logo for clean design -->
        <Head title="Forgot Password" />

        <!-- Background + Card -->
        <div class="bg-gradient-to-r from-[#ffe5e3] via-[#fbe1d1] to-[#ffe4e6] dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 py-10">
            <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-6">

                <!-- Title -->
                <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white">
                    Forgot your
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FF2D20] to-orange-500">
                        Password?
                    </span>
                </h2>

                <!-- Info Text -->
                <p class="text-sm text-gray-600 dark:text-gray-300 text-center">
                    No problem. Enter your email below and we’ll send you a password reset link.
                </p>

                <!-- Status Message -->
                <p v-if="status" class="text-sm font-medium text-green-600 dark:text-green-400 text-center">
                    {{ status }}
                </p>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-2 w-full"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full justify-center bg-[#FF2D20] hover:bg-[#e0241b] text-white transition duration-150 ease-in-out"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Send Reset Link
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
