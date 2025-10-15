<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-white pt-6 sm:justify-center sm:pt-0"
    >
        <!--<div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>-->

        <div
        
        
            class="mt-6 w-md overflow-hidden bg- px-6 py-20 shadow-xl sm:max-w-md sm:rounded-lg"
        >
            <slot />
            
        </div>
    </div>
</template>
