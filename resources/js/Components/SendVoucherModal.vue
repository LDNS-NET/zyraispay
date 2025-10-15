<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  voucher: {
    type: Object,
    required: true,
  },
  users: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['close', 'send'])

const selectedUserId = ref(null)

watch(
  () => props.show,
  (val) => {
    if (!val) selectedUserId.value = null
  }
)

function handleSend() {
  if (selectedUserId.value) {
    emit('send', {
      voucher_id: props.voucher.id,
      user_id: selectedUserId.value,
    })
  }
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
  >
    <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg">
      <h2 class="text-lg font-semibold mb-4">Send Voucher</h2>

      <p class="text-sm mb-2 text-gray-600">Voucher Code: <strong>{{ voucher.code }}</strong></p>

      <!-- User select -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
        <select
          v-model="selectedUserId"
          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500"
        >
          <option disabled value="">-- Select a user --</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }} ({{ user.email }})
          </option>
        </select>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-2">
        <button
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
          :disabled="!selectedUserId"
          @click="handleSend"
        >
          Send
        </button>
      </div>
    </div>
  </div>
</template>
