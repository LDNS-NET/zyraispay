<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DangerButton from '@/Components/DangerButton.vue'

const props = defineProps({
  usages: Object,
})

function formatBytes(bytes) {
  if (!bytes && bytes !== 0) return '-'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024))
  pow = Math.min(pow, units.length - 1)
  const val = bytes / Math.pow(1024, pow)
  return `${val.toFixed(2)} ${units[pow]}`
}

function deleteUsage(usage) {
  if (confirm('Delete this record?')) {
    router.delete(route('tenants.bandwidth-usage.destroy', usage.id))
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Bandwidth Usage</h2>
    </template>
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Router</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interface</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bytes In</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bytes Out</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Packets In</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Packets Out</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="usage in usages.data" :key="usage.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ usage.router?.name || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ usage.interface_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatBytes(usage.bytes_in) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatBytes(usage.bytes_out) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ usage.packets_in }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ usage.packets_out }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ usage.timestamp ? new Date(usage.timestamp).toLocaleString() : '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <DangerButton @click="deleteUsage(usage)" size="sm">Delete</DangerButton>
                  </td>
                </tr>
                <tr v-if="!usages.data.length">
                  <td colspan="8" class="px-6 py-4 text-center text-gray-500">No bandwidth usage records found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 