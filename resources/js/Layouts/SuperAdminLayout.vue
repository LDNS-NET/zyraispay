<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'

//lucid icons
import {
  LayoutDashboard,
  Users,
  PlusSquare,
  Activity,
  DollarSign,
  Contact,
  Ticket,
  MessageSquare,
  Package,
  Server,
  FileText,
  CreditCard,
} from 'lucide-vue-next'

const sidebarOpen = ref(false)
const user = usePage().props.auth.user
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div
      :class="[ 'fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-200 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0 lg:static'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-6 border-b">
        <Link :href="route('admin.dashboard')">
          <ApplicationLogo class="block h-9 w-auto text-gray-800" />
        </Link>
        <button class="lg:hidden text-gray-500" @click="sidebarOpen = false">✕</button>
      </div>

      <!-- Nav -->
      <nav class="mt-4 flex flex-col px-6 space-y-6 text-sm text-gray-700">
            <!-- Management -->
            <div>
                <h3 class="mb-2 text-xs font-semibold text-indigo-500 uppercase tracking-wider">Management</h3>

                <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                <div class="flex items-center space-x-2">
                    <LayoutDashboard class="w-4 h-4" />
                    <span>Dashboard</span>
                </div>
                </NavLink>

                <NavLink :href="route('admin.active-users')" :active="route().current('admin.active-users')">
                    <div class="flex items-center space-x-2">
                        <Activity class="w-4 h-4" />
                        <span>Active Users</span>
                    </div>
                </NavLink>

                <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.index')">
                <div class="flex items-center space-x-2">
                    <Users class="w-4 h-4" />
                    <span>Users</span>
                </div>
                </NavLink>

                <NavLink :href="route('leads.index')" :active="route().current('leads.index')">
                <div class="flex items-center space-x-2">
                    <Contact class="w-4 h-4" />
                    <span>Leads</span>
                </div>
                </NavLink>

                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <Ticket class="w-4 h-4" />
                    <span>Tickets</span>
                </div>
                </NavLink>

                <!--<NavLink :href="route('tenants.create')" :active="route().current('tenants.create')">
                <div class="flex items-center space-x-2">
                    <PlusSquare class="w-4 h-4" />
                    <span>Create ISP</span>
                </div>
                </NavLink>-->
            </div>

            <!-- Operations -->
            <div>
                <h3 class="mb-2 text-xs font-semibold text-emerald-500 uppercase tracking-wider">Operations</h3>

                <NavLink :href="route('admin.sms.index')" :active="route().current('admin.sms.index')">
                <div class="flex items-center space-x-2">
                    <MessageSquare class="w-4 h-4" />
                    <span>SMS</span>
                </div>
                </NavLink>


            </div>

            <!-- Finances -->
            <div>



                <h3 class="mb-2 text-xs font-semibold text-yellow-500 uppercase tracking-wider">Finances</h3>

                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <DollarSign class="w-4 h-4" />
                    <span>Revenue</span>
                </div>
                </NavLink>

                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <FileText class="w-4 h-4" />
                    <span>Invoices</span>
                </div>
                </NavLink>
                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <CreditCard class="w-4 h-4" />
                    <span>Expenses</span>
                </div>
                </NavLink>



            </div>


            <!-- Network -->
            <div>
                <h3 class="mb-2 text-xs font-semibold text-rose-500 uppercase tracking-wider">Network</h3>

                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <Package class="w-4 h-4" />
                    <span>Equipment</span>
                </div>
                </NavLink>

                <NavLink href="#" disabled>
                <div class="flex items-center space-x-2">
                    <Server class="w-4 h-4" />
                    <span>Mikrotik</span>
                </div>
                </NavLink>

            </div>
        </nav>

    </div>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Top bar -->
      <header class="flex items-center justify-between bg-white shadow px-4 py-3">
        <div class="flex items-center space-x-4 lg:hidden">
          <button @click="sidebarOpen = true" class="text-gray-600 focus:outline-none">☰</button>
          <ApplicationLogo class="h-8 w-auto text-indigo-600" />
        </div>

        <div class="ml-auto relative">
          <Dropdown align="right" width="48">
            <template #trigger>
              <button class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-indigo-600 focus:outline-none">
                <span>{{ user.name }}</span>
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06 0L10 10.94l3.71-3.73a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 010-1.06z" clip-rule="evenodd" />
                </svg>
              </button>
            </template>

            <template #content>
              <div class="px-4 py-2">
                <div class="text-sm font-medium text-gray-800">{{ user.name }}</div>
                <div class="text-xs text-gray-500">{{ user.email }}</div>
              </div>
              <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
              <DropdownLink method="post" :href="route('logout')" as="button">Log Out</DropdownLink>
            </template>
          </Dropdown>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
