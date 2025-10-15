<script setup>
import { reactive, watch, toRefs } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { format } from 'date-fns'

const props = defineProps({
  mode: {
    type: String,
    default: 'create', // or 'edit'
  },
  model: {
    type: Object,
    default: () => ({
      code: '',
      amount: '',
      status: 'active',
      expiration_date: '',
    }),
  },
})

const emit = defineEmits(['submit'])

const form = useForm({
  code: props.model.code || '',
  amount: props.model.amount || '',
  status: props.model.status || 'active',
  expiration_date: props.model.expiration_date || '',
})

watch(
  () => props.model,
  (newVal) => {
    form.code = newVal.code
    form.amount = newVal.amount
    form.status = newVal.status
    form.expiration_date = newVal.expiration_date
  },
  { deep: true }
)

function handleSubmit() {
  emit('submit', form)
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Code -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Code</label>
      <input
        v-model="form.code"
        type="text"
        required
        class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    <!-- Amount -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Amount</label>
      <input
        v-model="form.amount"
        type="number"
        required
        class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    <!-- Status -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Status</label>
      <select
        v-model="form.status"
        class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
      >
        <option value="active">Active</option>
        <option value="used">Used</option>
        <option value="expired">Expired</option>
      </select>
    </div>

    <!-- Expiration Date -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Expiration Date</label>
      <input
        v-model="form.expiration_date"
        type="date"
        class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    <!-- Submit -->
    <div>
      <button
        type="submit"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none"
      >
        {{ mode === 'edit' ? 'Update' : 'Create' }} Voucher
      </button>
    </div>
  </form>
</template>
