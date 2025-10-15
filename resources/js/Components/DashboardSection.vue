<template>
  <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ title }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
      <slot />
    </div>
  </section>
</template>

<script setup>
defineProps({
  title: String,
});
</script>
