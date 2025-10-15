<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'

defineProps({
  title: String
})
</script>

<template>
    <SuperAdminLayout>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Top Navigation Bar -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
          <ApplicationLogo class="w-10 h-10" />
          <span class="text-xl font-bold text-gray-800">ZyraISP Super Admin</span>
        </div>

        <div>
          <Dropdown align="right" width="48">
            <template #trigger>
              <button class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-800">
                Super Admin
                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 20 20">
                  <path stroke="currentColor" stroke-width="1.5" d="M6 8l4 4 4-4" />
                </svg>
              </button>
            </template>
          </Dropdown>
        </div>
      </div>
    </header>

    <!-- Sidebar + Content -->
    <div class="flex flex-1">
      <!-- Sidebar -->


      <!-- Page Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
  </SuperAdminLayout>
</template>
