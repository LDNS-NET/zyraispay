<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'; // 👈 Add ref for toggling

const form = useForm({
    business_name: '',
    username: '',
    email: '',
    phone:'',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Page background and card -->
        <div class="bg-gradient-to-r from-blue-200 via-red-200 to-green-200 px-4 py-10">
            <div class="max-w-md mx-auto text rounded-xl shadow-lg p-6 space-y-6">

                <!-- Title -->
                <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-blue-800">
                    Create your
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FF2D20] to-blue-500">
                        ZYRAISPAY
                    </span>
                    account
                </h2>

                <!-- Registration is for new businesses/tenants only. Not for WiFi users or staff. -->

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Business Name -->
                    <div>
                        <InputLabel for="business_name" value="Business Name" />
                        <TextInput
                            id="business_name"
                            type="text"
                            v-model="form.business_name"
                            class="mt-2 w-full"
                            required
                            autocomplete="organization"
                        />
                        <InputError class="mt-1" :message="form.errors.business_name" />
                    </div>

                    <!-- Username -->
                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput
                            id="username"
                            type="text"
                            v-model="form.username"
                            class="mt-2 w-full"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.username" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-2 w-full"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            id="phone"
                            type="text"
                            v-model="form.phone"
                            class="mt-2 w-full"
                            required
                            autocomplete="tel"
                        />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>

                    <!-- Password -->
                    <div>
                        <InputLabel for="password" value="Password" />
                        <div class="relative">
                            <TextInput
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                v-model="form.password"
                                class="mt-2 w-full pr-10"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-blue-950"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.974 9.974 0 013.73-4.59m3.772-1.401a10.05 10.05 0 015.448 0m3.772 1.4A9.974 9.974 0 0121.542 12c-.297.949-.737 1.833-1.294 2.628M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <div class="relative">
                            <TextInput
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                class="mt-2 w-full pr-10"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-blue-950"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                            >
                                <svg v-if="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.974 9.974 0 013.73-4.59m3.772-1.401a10.05 10.05 0 015.448 0m3.772 1.4A9.974 9.974 0 0121.542 12c-.297.949-.737 1.833-1.294 2.628M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between pt-4">
                        <Link
                            :href="route('login')"
                            class="text-sm text-black hover:text-violet-900"
                        >
                            Already registered?
                        </Link>
                        <PrimaryButton
                            class=" text-blue-800 bg-green-500 hover:bg-green-700"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Register
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
