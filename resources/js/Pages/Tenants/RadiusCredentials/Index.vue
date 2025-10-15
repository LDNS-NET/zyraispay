<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  credentials: Array,
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const selectedCredential = ref(null)

const form = useForm({
  secret: '',
  ip_range: '',
  nas_identifier: '',
})

function openAdd() {
  form.reset()
  showAddModal.value = true
}

function openEdit(credential) {
  selectedCredential.value = credential
  form.secret = credential.secret
  form.ip_range = credential.ip_range
  form.nas_identifier = credential.nas_identifier
  showEditModal.value = true
}

function submitForm() {
  form.post(route('tenants.radius-credentials.store'), {
    onSuccess: () => {
      showAddModal.value = false
      window.location.reload()
    },
    onError: (errors) => {
      alert('Error: ' + Object.values(errors).flat().join(', '))
    }
  })
}

function updateForm() {
  form.put(route('tenants.radius-credentials.update', selectedCredential.value.id), {
    onSuccess: () => {
      showEditModal.value = false
      window.location.reload()
    },
    onError: (errors) => {
      alert('Error: ' + Object.values(errors).flat().join(', '))
    }
  })
}

function deleteCredential(credential) {
  if (confirm('Delete this credential?')) {
    router.delete(route('tenants.radius-credentials.destroy', credential.id))
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">Radius Credentials</h2>
        <PrimaryButton @click="openAdd">Add Credential</PrimaryButton>
      </div>
    </template>
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Secret</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Range</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAS Identifier</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="credential in credentials" :key="credential.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ credential.secret }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ credential.ip_range }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ credential.nas_identifier }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <PrimaryButton @click="openEdit(credential)" size="sm">Edit</PrimaryButton>
                    <DangerButton @click="deleteCredential(credential)" size="sm">Delete</DangerButton>
                  </td>
                </tr>
                <tr v-if="!credentials.length">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500">No credentials found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <Modal :show="showAddModal" @close="showAddModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Add Radius Credential</h2>
        <form @submit.prevent="submitForm">
          <InputLabel value="Secret" />
          <TextInput v-model="form.secret" class="mt-1 block w-full" />
          <InputError :message="form.errors.secret" />
          <InputLabel value="IP Range" />
          <TextInput v-model="form.ip_range" class="mt-1 block w-full" />
          <InputError :message="form.errors.ip_range" />
          <InputLabel value="NAS Identifier" />
          <TextInput v-model="form.nas_identifier" class="mt-1 block w-full" />
          <InputError :message="form.errors.nas_identifier" />
          <div class="flex justify-end mt-4">
            <PrimaryButton type="submit">Save</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
    <Modal :show="showEditModal" @close="showEditModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Radius Credential</h2>
        <form @submit.prevent="updateForm">
          <InputLabel value="Secret" />
          <TextInput v-model="form.secret" class="mt-1 block w-full" />
          <InputError :message="form.errors.secret" />
          <InputLabel value="IP Range" />
          <TextInput v-model="form.ip_range" class="mt-1 block w-full" />
          <InputError :message="form.errors.ip_range" />
          <InputLabel value="NAS Identifier" />
          <TextInput v-model="form.nas_identifier" class="mt-1 block w-full" />
          <InputError :message="form.errors.nas_identifier" />
          <div class="flex justify-end mt-4">
            <PrimaryButton type="submit">Update</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template> 