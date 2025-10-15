<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

defineProps({
  payments: Object,
  filters: Object,
  users: Array
})

const showModal = ref(false)
const editMode = ref(false)
const currentId = ref(null)

const search = ref('')
const form = useForm({
  user_id: '',
  receipt_number: '',
  checked: false,
  paid_at: '',
  disbursement_type: ''
})

function openCreate() {
  form.reset()
  editMode.value = false
  showModal.value = true
}

function openEdit(payment) {
  currentId.value = payment.id
  form.user_id = users.find(u => u.username === payment.user)?.id || ''
  form.receipt_number = payment.receipt_number
  form.checked = payment.checked
  form.paid_at = payment.paid_at
  form.disbursement_type = payment.disbursement_type || ''
  editMode.value = true
  showModal.value = true
}


const filters = ref({
  search: filters.search || '',
  disbursement: filters.disbursement || '',
})




function submit() {
  if (editMode.value) {
    form.put(route('tenants.payments.update', currentId.value), {
      onSuccess: () => showModal.value = false
    })
  } else {
    form.post(route('tenants.payments.store'), {
      onSuccess: () => showModal.value = false
    })
  }
}

function destroy(id) {
  if (confirm('Delete this payment?')) {
    router.delete(route('tenants.payments.destroy', id))
  }
}

function searchPayments() {
  router.get(route('tenants.payments.index'), {
    search: filters.value.search,
    disbursement: filters.value.disbursement,
  }, { preserveState: true })
}



</script>

<template>
  <Head title="Tenant Payments" />

  <div class="p-6 space-y-4">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Payments</h1>
      <a :href="route('tenants.payments.export')" class="bg-green-600 text-white px-4 py-2 rounded">Export Excel</a>
      <button @click="openCreate" class="bg-blue-600 text-white px-4 py-2 rounded">Add Payment</button>
    </div>

    <div class="flex gap-4 mb-4">
        <input
            v-model="filters.search"
            @keyup.enter="searchPayments"
            placeholder="Search by user or phone"
            class="border p-2 rounded w-full max-w-md"
        />

        <select
            v-model="filters.disbursement"
            @change="searchPayments"
            class="border p-2 rounded"
        >
            <option value="">All</option>
            <option value="direct">Direct</option>
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select>
    </div>


    <table class="w-full text-left border mt-4 bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Phone</th>
          <th class="px-4 py-2">Receipt</th>
          <th class="px-4 py-2">Checked</th>
          <th class="px-4 py-2">Paid At</th>
          <th class="px-4 py-2">Disbursement</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="payment in payments.data" :key="payment.id" class="border-t">
          <td class="px-4 py-2">{{ payment.user }}</td>
          <td class="px-4 py-2">{{ payment.phone }}</td>
          <td class="px-4 py-2">{{ payment.receipt_number }}</td>
          <td class="px-4 py-2">{{ payment.checked ? 'Yes' : 'No' }}</td>
          <td class="px-4 py-2">{{ payment.paid_at }}</td>
          <td class="px-4 py-2">{{ payment.disbursement }}</td>
          <td class="px-4 py-2 space-x-2">
            <button @click="openEdit(payment)" class="text-blue-600 hover:underline">Edit</button>
            <button @click="destroy(payment.id)" class="text-red-600 hover:underline">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between">
      <button
        v-if="payments.prev_page_url"
        @click="router.get(payments.prev_page_url)"
        class="px-4 py-2 bg-gray-200 rounded"
      >Previous</button>
      <button
        v-if="payments.next_page_url"
        @click="router.get(payments.next_page_url)"
        class="px-4 py-2 bg-gray-200 rounded"
      >Next</button>
    </div>
  </div>

  <!-- Modal -->
  <Modal :show="showModal" @close="showModal = false">
    <form @submit.prevent="submit" class="p-6 space-y-4">
      <h2 class="text-lg font-semibold">{{ editMode ? 'Edit' : 'Add' }} Payment</h2>

      <select v-model="form.user_id" class="w-full border p-2 rounded" required>
        <option value="">Select User</option>
        <option v-for="user in users" :key="user.id" :value="user.id">
          {{ user.username }}
        </option>
      </select>

      <input v-model="form.receipt_number" placeholder="Receipt #" class="w-full border p-2 rounded" required />
      <input v-model="form.paid_at" type="datetime-local" class="w-full border p-2 rounded" required />

      <select v-model="form.disbursement_type" class="w-full border p-2 rounded">
        <option value="">Pending</option>
        <option value="direct">Direct</option>
        <option value="paid">Paid</option>
      </select>

      <label class="flex items-center space-x-2">
        <input type="checkbox" v-model="form.checked" />
        <span>Checked</span>
      </label>

      <div class="flex justify-end space-x-2">
        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
          {{ editMode ? 'Update' : 'Save' }}
        </button>
      </div>
    </form>
  </Modal>
</template>
