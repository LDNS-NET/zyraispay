<script setup>
const props = defineProps({
  modelValue: [String, Number],
  label: String,
  name: String,
  id: String,
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  options: {
    type: Array,
    required: true,
    // Format: [{ value: 'admin', label: 'Admin' }]
  },
  required: Boolean,
  disabled: Boolean,
  errors: {
    type: [Array, String],
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

function handleChange(event) {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div class="w-full mb-4">
    <label
      v-if="label"
      :for="id || name"
      class="block mb-1 text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <select
      :id="id || name"
      :name="name"
      class="w-full px-4 py-2 border bg-blue-300 border-blue-500 rounded-lg shadow-sm bg-gradient-to-br from-white via-blue-200 to-cyan-300 text-base focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500 transition-all duration-200 disabled:opacity-50 placeholder-gray-400"
      :required="required"
      :disabled="disabled"
      :value="modelValue"
      @change="handleChange"
    >
      <option disabled value="">{{ placeholder }}</option>
      <option
        v-for="(option, i) in options"
        :key="i"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <div v-if="errors && errors.length" class="text-red-500 text-sm mt-1">
      <div v-if="typeof errors === 'string'">{{ errors }}</div>
      <ul v-else>
        <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
      </ul>
    </div>
  </div>
</template>
