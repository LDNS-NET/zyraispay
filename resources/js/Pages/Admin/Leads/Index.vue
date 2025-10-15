<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ leads: Array })
const showForm = ref(false)
</script>

<template>
  <SuperAdminLayout>
    <div class="space-y-8">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Leads</h1>
        <button @click="showForm = true" class="bg-blue-600 text-white px-4 py-2 rounded">
          Add Lead
        </button>
      </div>

      <!-- Create Form Modal -->
      <div v-if="showForm" class="bg-white border rounded p-4 max-w-md">
        <CreateLeadForm @close="showForm = false" />
      </div>

      <!-- Leads List -->
      <div class="mt-8">
        <h2 class="text-xl font-semibold">Available Leads</h2>
        <ul class="divide-y mt-4">
          <li v-for="lead in leads" :key="lead.id" class="py-4 flex justify-between items-center">
            <div>
              <p class="font-medium">{{ lead.isp_name }}</p>
              <p class="text-sm text-gray-600">{{ lead.email }} — {{ lead.phone }}</p>
            </div>
            <div class="space-x-4">
              <Link
                :href="route('tenants.create', {
                  isp_name: lead.isp_name,
                  email: lead.email,
                  phone: lead.phone
                })"
                class="text-indigo-600 hover:underline"
              >
                Convert
              </Link>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script>
import CreateLeadForm from './CreateLeadForm.vue'
</script>
