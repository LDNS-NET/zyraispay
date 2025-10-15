<script setup>
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
  tenant: Object,
})

const form = useForm({
  name: tenant.data.name,
  domain: tenant.data.domain.replace('.zyraispay.test', ''), // strip base domain
})
</script>

<template>
  <Head title="Edit ISP Tenant" />

  <div class="max-w-2xl mx-auto p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-6">✏️ Edit ISP Tenant</h1>

    <form @submit.prevent="form.put(`/admin/tenants/${tenant.id}`)">
      <div class="mb-4">
        <label class="block mb-1 font-medium">ISP Name</label>
        <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
        <div v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</div>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-medium">Subdomain</label>
        <div class="flex items-center">
          <input v-model="form.domain" type="text" class="flex-1 border p-2 rounded-l" />
          <span class="bg-gray-100 px-3 py-2 rounded-r border border-l-0">.zyraispay.test</span>
        </div>
        <div v-if="form.errors.domain" class="text-red-600 text-sm">{{ form.errors.domain }}</div>
      </div>

      <button
        type="submit"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        :disabled="form.processing"
      >
        💾 Update Tenant
      </button>
    </form>
  </div>
</template>
