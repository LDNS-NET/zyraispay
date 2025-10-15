<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'
import { onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  tenant: Object
})

const confirmDelete = () => {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This will permanently delete this tenant and all their data!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e3342f',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('tenants.destroy', props.tenant.id))
    }
  })
}
</script>

<template>
  <Head :title="`Tenant - ${tenant.data?.name || tenant.id}`" />

  <SuperAdminLayout>
    <div class="max-w-4xl mx-auto bg-white p-6 shadow rounded-md">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Tenant Details</h1>
        <div class="space-x-2">
          <Link
            :href="route('tenants.edit', tenant.id)"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            Edit
          </Link>
          <button
            @click="confirmDelete"
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
          >
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <p class="text-gray-600 font-medium">Name:</p>
          <p class="text-lg">{{ tenant.data?.name }}</p>
        </div>
        <div>
          <p class="text-gray-600 font-medium">Domain ID:</p>
          <p class="text-lg">{{ tenant.id }}</p>
        </div>
        <div>
          <p class="text-gray-600 font-medium">Email:</p>
          <p class="text-lg">{{ tenant.data?.email }}</p>
        </div>
        <div>
          <p class="text-gray-600 font-medium">Domain:</p>
          <p class="text-lg">{{ tenant.data?.domain }}.zyraispay.test</p>
        </div>
        <div>
          <p class="text-gray-600 font-medium">Created At:</p>
          <p class="text-lg">{{ new Date(tenant.created_at).toLocaleString() }}</p>
        </div>
        <div>
          <p class="text-gray-600 font-medium">Updated At:</p>
          <p class="text-lg">{{ new Date(tenant.updated_at).toLocaleString() }}</p>
        </div>
      </div>

      <div class="mt-8">
        <Link
          :href="route('admin.dashboard')"
          class="text-blue-600 hover:underline"
        >
          ← Back to Dashboard
        </Link>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<style scoped>
p {
  word-break: break-word;
}
</style>
