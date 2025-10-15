
<template>
  <div class="bg-gradient-to-br from-green-200 via-white to-cyan-400 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900 border border-cyan-100 dark:border-indigo-800 shadow-xl rounded-2xl p-6 flex flex-col gap-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl">
    <div class="flex items-center gap-2 mb-2">
      <component v-if="icon" :is="icon" class="w-7 h-7 text-cyan-700 dark:text-indigo-400 drop-shadow" />
      <span class="text-base font-semibold text-gray-700 dark:text-white">{{ title }}</span>
    </div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
      <slot name="value">
        {{ value }}
      </slot>
    </div>
    <div v-if="subtitle" class="text-xs text-cyan-700 dark:text-indigo-200 mt-1">{{ subtitle }}</div>
  </div>
</template>


<script setup>
defineProps({
  title: String,
  value: [String, Number],
  subtitle: String,
  icon: [Object, Function] // Accepts a component or a function
});
</script>
