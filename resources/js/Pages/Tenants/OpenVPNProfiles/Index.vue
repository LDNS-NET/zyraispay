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
  profiles: Array,
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const selectedProfile = ref(null)

const form = useForm({
  config_path: '',
  client_cert_path: '',
  client_key_path: '',
  ca_cert_path: '',
  status: 'active',
})

function openAdd() {
  form.reset()
  showAddModal.value = true
}

function openEdit(profile) {
  selectedProfile.value = profile
  form.config_path = profile.config_path
  form.client_cert_path = profile.client_cert_path
  form.client_key_path = profile.client_key_path
  form.ca_cert_path = profile.ca_cert_path
  form.status = profile.status
  showEditModal.value = true
}

function submitForm() {
  form.post(route('tenants.openvpn-profiles.store'), {
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
  form.put(route('tenants.openvpn-profiles.update', selectedProfile.value.id), {
    onSuccess: () => {
      showEditModal.value = false
      window.location.reload()
    },
    onError: (errors) => {
      alert('Error: ' + Object.values(errors).flat().join(', '))
    }
  })
}

function deleteProfile(profile) {
  if (confirm('Delete this profile?')) {
    router.delete(route('tenants.openvpn-profiles.destroy', profile.id))
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">OpenVPN Profiles</h2>
        <PrimaryButton @click="openAdd">Add Profile</PrimaryButton>
      </div>
    </template>
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Config Path</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Cert</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Key</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA Cert</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="profile in profiles" :key="profile.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ profile.config_path }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ profile.client_cert_path }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ profile.client_key_path }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ profile.ca_cert_path }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      profile.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    ]">
                      {{ profile.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <PrimaryButton @click="openEdit(profile)" size="sm">Edit</PrimaryButton>
                    <DangerButton @click="deleteProfile(profile)" size="sm">Delete</DangerButton>
                  </td>
                </tr>
                <tr v-if="!profiles.length">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">No OpenVPN profiles found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <Modal :show="showAddModal" @close="showAddModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Add OpenVPN Profile</h2>
        <form @submit.prevent="submitForm">
          <InputLabel value="Config Path" />
          <TextInput v-model="form.config_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.config_path" />
          <InputLabel value="Client Cert Path" />
          <TextInput v-model="form.client_cert_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.client_cert_path" />
          <InputLabel value="Client Key Path" />
          <TextInput v-model="form.client_key_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.client_key_path" />
          <InputLabel value="CA Cert Path" />
          <TextInput v-model="form.ca_cert_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.ca_cert_path" />
          <InputLabel value="Status" />
          <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <InputError :message="form.errors.status" />
          <div class="flex justify-end mt-4">
            <PrimaryButton type="submit">Save</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
    <Modal :show="showEditModal" @close="showEditModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Edit OpenVPN Profile</h2>
        <form @submit.prevent="updateForm">
          <InputLabel value="Config Path" />
          <TextInput v-model="form.config_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.config_path" />
          <InputLabel value="Client Cert Path" />
          <TextInput v-model="form.client_cert_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.client_cert_path" />
          <InputLabel value="Client Key Path" />
          <TextInput v-model="form.client_key_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.client_key_path" />
          <InputLabel value="CA Cert Path" />
          <TextInput v-model="form.ca_cert_path" class="mt-1 block w-full" />
          <InputError :message="form.errors.ca_cert_path" />
          <InputLabel value="Status" />
          <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <InputError :message="form.errors.status" />
          <div class="flex justify-end mt-4">
            <PrimaryButton type="submit">Update</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template> 