<script setup>
import { ref, onClickOutside } from '@vueuse/core'
import { MoreVertical } from 'lucide-vue-next'

const isOpen = ref(false)
const dropdownRef = ref(null)

onClickOutside(dropdownRef, () => {
  isOpen.value = false
})

defineProps({
  align: {
    type: String,
    default: 'right', // or 'left'
  },
  icon: {
    type: Boolean,
    default: true,
  },
  label: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="relative inline-block text-left" ref="dropdownRef">
    <!-- Trigger -->
    <button
      @click="isOpen = !isOpen"
      class="p-2 rounded-md hover:bg-gray-100 transition text-gray-600"
    >
      <template v-if="icon">
        <MoreVertical class="w-5 h-5" />
      </template>
      <template v-else>
        {{ label }}
      </template>
    </button>

    <!-- Dropdown -->
    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-2 w-40 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none',
          align === 'left' ? 'left-0' : 'right-0',
        ]"
      >
        <div class="py-1 text-sm text-gray-700">
          <slot />
        </div>
      </div>
    </transition>
  </div>
</template>
