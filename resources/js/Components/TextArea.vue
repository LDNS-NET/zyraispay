<script setup>
defineProps({
  id: String,
  modelValue: String,
})
</script>


<template>
  <div>
    <textarea
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="mt-1 block w-full rounded-lg border border-blue-600 bg-gradient-to-br from-white via-blue-50 to-cyan-50 px-4 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500 transition-all duration-200 shadow-sm placeholder-gray-400"
      rows="4"
    ></textarea>
  </div>
</template>


