<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  activeUsers: Array,
})

const editingUser = ref(null)

function formatCountdown(seconds) {
  if (seconds <= 0) return 'Expired'
  const days = Math.floor(seconds / (3600 * 24))
  const hours = Math.floor((seconds % (3600 * 24)) / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  return `${days}d ${hours}h ${minutes}m`
}

function editUser(user) {
  editingUser.value = { ...user }
}

function saveUserEdits() {
  const { id } = editingUser.value // Only need the user id now

  router.put(`/admin/active-users/${id}`, editingUser.value, { // URL changed
    onSuccess: () => {
      editingUser.value = null
    },
    onError: (errors) => {
      console.error(errors)
    },
  })
}

function deleteUser(user) {
  if (confirm(`Delete user ${user.name}?`)) {
    router.delete(`/admin/active-users/${user.id}`, { // URL changed
      onSuccess: () => console.log('Deleted'),
    })
  }
}
</script>

<template>
  <Head title="Active Users" />

  <SuperAdminLayout>
    <div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
      <h1 class="text-2xl font-bold mb-4">Active Users</h1>

      <table class="w-full text-sm border">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="px-4 py-2 border">Tenant</th>
            <th class="px-4 py-2 border">Name</th>
            <th class="px-4 py-2 border">Email</th>
            <th class="px-4 py-2 border">Trial Expires</th>
            <th class="px-4 py-2 border">Remaining</th>
            <th class="px-4 py-2 border">Status</th>
            <th class="px-4 py-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in props.activeUsers" :key="user.id" class="hover:bg-gray-50">
            <td class="border px-4 py-2">{{ user.tenant_name }}</td>
            <td class="border px-4 py-2">{{ user.name }}</td>
            <td class="border px-4 py-2">{{ user.email }}</td>
            <td class="border px-4 py-2">{{ user.trial_expires_at }}</td>
            <td class="border px-4 py-2">{{ formatCountdown(user.trial_remaining_seconds) }}</td>
            <td class="border px-4 py-2">
              <span :class="user.is_trial_active ? 'text-green-600' : 'text-red-600'">
                {{ user.is_trial_active ? 'Active' : 'Expired' }}
              </span>
            </td>
            <td class="border px-4 py-2">
              <button @click="editUser(user)" class="text-blue-600 hover:underline">Edit</button>
              <button @click="deleteUser(user)" class="text-red-600 hover:underline ml-2">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!props.activeUsers.length" class="text-center text-gray-500 py-6">No users found.</div>
    </div>

    <div v-if="editingUser" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow max-w-md w-full space-y-4">
        <h2 class="text-lg font-semibold">Edit User</h2>

        <label class="block">
          Name:
          <input v-model="editingUser.name" class="w-full mt-1 p-2 border rounded" />
        </label>

        <label class="block">
          Email:
          <input v-model="editingUser.email" class="w-full mt-1 p-2 border rounded" />
        </label>

        <label class="block">
          Trial Expires At:
          <input type="datetime-local" v-model="editingUser.trial_expires_at" class="w-full mt-1 p-2 border rounded" />
        </label>

        <div class="flex justify-end space-x-2 pt-4">
          <button @click="editingUser = null" class="text-gray-600 hover:underline">Cancel</button>
          <button @click="saveUserEdits" class="text-blue-600 hover:underline font-bold">Save</button>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>
