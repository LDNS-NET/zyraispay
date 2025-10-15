<script setup>
import { computed } from 'vue'
import { CheckCircle, XCircle, Loader2 } from 'lucide-vue-next'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  icons: {
    type: Boolean,
    default: true,
  },
})

// Define reusable config
const statusMap = {
  active: {
    label: 'Active',
    color: 'green',
    icon: CheckCircle,
  },
  inactive: {
    label: 'Inactive',
    color: 'gray',
    icon: XCircle,
  },
  pending: {
    label: 'Pending',
    color: 'yellow',
    icon: Loader2,
  },
  expired: {
    label: 'Expired',
    color: 'red',
    icon: XCircle,
  },
  used: {
    label: 'Used',
    color: 'blue',
    icon: CheckCircle,
  },
}

// Compute styling
const badge = computed(() => {
  const lower = props.status.toLowerCase()
  return statusMap[lower] || {
    label: props.status,
    color: 'gray',
    icon: null,
  }
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold capitalize whitespace-nowrap"
    :class="`bg-${badge.color}-100 text-${badge.color}-800`"
  >
    <component
      v-if="icons && badge.icon"
      :is="badge.icon"
      class="w-4 h-4"
    />
    {{ badge.label }}
  </span>
</template>
