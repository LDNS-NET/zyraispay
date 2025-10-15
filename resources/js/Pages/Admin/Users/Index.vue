<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'

defineProps({ users: Array })
</script>

<template>
  <Head title="All Tenants" />

  <SuperAdminLayout>
    <section class="px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">All Registered Tenants</h1>

        <Link
          href="/admin/tenants/create"
          class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition"
        >
          ➕ Create Tenant
        </Link>
      </div>

      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-4 py-3 text-left">Name</th>
              <th class="px-4 py-3 text-left">Email</th>
              <th class="px-4 py-3 text-left hidden md:table-cell">Created At</th>
              <th class="px-4 py-3 text-left hidden lg:table-cell">Phone</th>
              <th class="px-4 py-3 text-left hidden lg:table-cell">Payments</th>
              <th class="px-4 py-3 text-left hidden lg:table-cell">Mikrotiks</th>
              <th class="px-4 py-3 text-left hidden md:table-cell">Users</th>
              <th class="px-4 py-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="user in users"
              :key="user.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">{{ user.name }}</td>
              <td class="px-4 py-3">{{ user.email }}</td>
              <td class="px-4 py-3 hidden md:table-cell">{{ user.created_at }}</td>
              <td class="px-4 py-3 hidden lg:table-cell">{{ user.phone ?? '—' }}</td>
              <td class="px-4 py-3 hidden lg:table-cell">{{ user.payments_count }}</td>
              <td class="px-4 py-3 hidden lg:table-cell">{{ user.mikrotiks_count }}</td>
              <td class="px-4 py-3 hidden md:table-cell">{{ user.users_count }}</td>
              <td class="px-4 py-3 space-x-2 whitespace-nowrap">
                <Link
                  :href="`/admin/users/${user.id}/edit`"
                  class="text-green-600 hover:underline"
                >
                  Edit
                </Link>

                <Link
                  as="button"
                  method="delete"
                  :href="`/admin/users/${user.id}`"
                  class="text-red-600 hover:underline"
                >
                  Delete
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!users.length" class="p-4 text-center text-gray-500">
          No tenants found.
        </div>
      </div>
    </section>
  </SuperAdminLayout>
</template>
