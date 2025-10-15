<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border border-blue-300 bg-gradient-to-br from-white via-blue-50 to-cyan-50 px-4 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500 transition-all duration-200 shadow-sm placeholder-gray-400"
        v-model="model"
        ref="input"
    />
</template>
