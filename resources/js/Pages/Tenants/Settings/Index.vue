<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import General from './General.vue'
import Notifications from './Notifications.vue'
import Hotspot from './Hotspot.vue'
import PaymentGateway from './PaymentGateway.vue'
import SmsGateway from './SmsGateway.vue'
import WhatsappGateway from './WhatsappGateway.vue'
import { Settings, Bell, Wifi, CreditCard, MessageCircle, Smartphone } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const tenant = page.props.tenant || {}
const mainTabs = [
  { key: 'general', label: 'General', icon: Settings },
  { key: 'notifications', label: 'Notifications', icon: Bell },
  { key: 'hotspot', label: 'Hotspot', icon: Wifi },
  { key: 'whatsapp', label: 'WhatsApp', icon: MessageCircle },
  { key: 'sms', label: 'SMS', icon: Smartphone },
  { key: 'payment', label: 'Payment', icon: CreditCard },
]
const mainTab = ref('general')

const mainTabComponent = computed(() => {
  switch (mainTab.value) {
    case 'general': return General
    case 'notifications': return Notifications
    case 'hotspot': return Hotspot
    case 'payment': return PaymentGateway
    case 'sms': return SmsGateway
    case 'whatsapp': return WhatsappGateway
    default: return General
  }
})
const gatewayTabComponent = computed(() => {
  switch (gatewayTab.value) {
    
    default: return PaymentGateway
  }
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-5xl mx-auto p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <aside class="md:col-span-1 bg-white rounded-xl shadow p-4 flex flex-col gap-2">
          <h2 class="text-lg font-bold mb-4">Settings</h2>
          <nav class="flex flex-col gap-2">
            <button
              v-for="tab in mainTabs"
              :key="tab.key"
              @click="mainTab = tab.key"
              :class="mainTab === tab.key ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600'"
              class="flex items-center gap-2 px-3 py-2 rounded hover:bg-blue-100 transition-colors focus:outline-none"
              :title="'Go to ' + tab.label + ' settings'"
            >
              <component :is="tab.icon" class="w-5 h-5" />
              <span>{{ tab.label }}</span>
            </button>
          </nav>
        </aside>
        <!-- Main Content -->
        <main class="md:col-span-3">
          <!-- Main Tab Content -->
          <component :is="mainTabComponent" />
        </main>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
aside button {
  transition: background 0.2s, color 0.2s;
}
</style>


