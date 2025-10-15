<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-base font-semibold bg-clip-text text-transparent bg-gradient-to-r from-black via-blue-600 to-green-400 transition-all duration-200">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
