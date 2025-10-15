<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'

const props = defineProps({ user: Object })

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone,
})

const updateUser = () => {
  form.put(`/admin/users/${props.user.id}`)
}
</script>

<template>
  <Head title="Edit Tenant" />
  <SuperAdminLayout>
    <h1 class="text-2xl font-bold mb-4">Edit Tenant</h1>

    <form @submit.prevent="updateUser" class="space-y-4 max-w-md">
      <div>
        <label class="block text-sm font-bold">Name</label>
        <input v-model="form.name" type="text" class="border rounded w-full p-2" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
      </div>

      <div>
        <label class="block text-sm font-bold">Phone</label>
        <input v-model="form.phone" type="text" class="border rounded w-full p-2"/>
        <span v-if="form.errors.phone" class="text-red-500 text-sm">{{ form.errors.phone }} </span>
      </div>

      <div>
        <label class="block text-sm font-bold">Email</label>
        <input v-model="form.email" type="email" class="border rounded w-full p-2" />
        <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Update Tenant
      </button>
    </form>
  </SuperAdminLayout>
</template>
