<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {ref} from 'vue';

defineProps({
    canResetPassword: Boolean,
    canRegister: Boolean,
    status: String,
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <!-- Improved Gradient Background 
        <div class="bg-gradient-to-r  px-4 py-10">-->
            <div class="max-w-md mx-auto text rounded-xl shadow-lg p-4 space-y-6">

                <!-- Title -->
                <h2 class="text-3xl font-bold text-center text-blue-800">
                    Login to
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FF2D20] to-orange-500">
                        ZYRAISPAY
                    </span>
                </h2>

                <!-- Status Message -->
                <p v-if="status" class="text-sm font-medium text-green-600 dark:text-green-400 text-center">
                    {{ status }}
                </p>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <InputLabel for="login" value="Email or username" />
                        <TextInput
                            id="login"
                            type="text"
                            v-model="form.login"
                            class="mt-2 w-full"
                            required
                            autofocus
                            aria-placeholder="username"
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.login" />
                    </div>

                    <!-- Password -->
                    <!-- Password with Toggle -->
                    <div>
                        <InputLabel for="password" value="Password" />
                        <div class="relative">
                            <TextInput
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                v-model="form.password"
                                class="mt-2 w-full pr-10"
                                required
                                autocomplete="current-password"
                            />
                            <!-- Toggle Icon -->
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-green-500 hover:text-gray-700 dark:text-green-400 dark:hover:text-blue-950"
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

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="text-gray-600 dark:text-gray-300">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-[#FF2D20] hover:underline transition"
                        >
                            Reset Password?
                        </Link>
                    </div>

                    <!-- Login Button & Register -->
                    <div class="flex ">

                        <!-- Register if user has no account -->
                        <Link
                            :href="route('register')"
                            class="w-full justify-end text-sm text-black hover:text-violet-900"
                            >
                            Create Account?
                        </Link>

                        <!--login button-->

                        <PrimaryButton
                            class="w-full flex items-center justify-center bg-green-500 hover:bg-green-700"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                                Log In

                        </PrimaryButton>



                    </div>

                </form>


            </div>
        <!--</div>-->
    </GuestLayout>
</template>
