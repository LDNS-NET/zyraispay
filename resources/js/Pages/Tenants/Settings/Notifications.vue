<script setup>
import { ref } from 'vue'

// Prefilled message templates
const defaultHotspotReminder = 'Dear {name}, your internet package ({package_name}) will expire on {expires_at}. Renew to avoid disconnection.'
const defaultPPPoEReminder = 'Reminder: Your PPPoE package ({package_name}) expires on {expires_at}. Kindly renew in advance.'
const defaultStaticReminder = 'Notice: Your static IP service ({package_name}) expires on {expires_at}. Contact support to renew.'
const defaultPaymentConfirmation = 'Payment received for {package_name}. Amount: {amount}. Transaction ID: {transaction_id}. Thank you!'
const defaultFinalExpiry = 'Dear {name}, your internet package ({package_name}) has expired on {expires_at}. Please renew to restore service.'
const defaultMikrotikAlert = 'ALERT: Router {router_name} ({router_ip}) is offline. We are working to resolve the issue. Total downtime: {offline_duration}.'

import { router, usePage, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextArea from '@/Components/TextArea.vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const page = usePage()
const defaultForm = {
  email_enabled: false,
  notification_email: '',
  sms_enabled: false,
  whatsapp_enabled: false,
  notification_phone: '',
  reminders: {
    hotspot: { enabled: false, days_before: [], message: '' },
    pppoe: { enabled: false, days_before: [], message: '' },
    static: { enabled: false, days_before: [], message: '' }
  },
  payment_confirmation: { enabled: false, message: '' },
  final_expiry_notification: { enabled: false, message: '' },
  mikrotik_status_alert: { enabled: false, message: '' }
}
const form = ref({ ...defaultForm, ...(page.props.settings || {}) })
const success = ref(page.props.flash?.success || '')
const loading = ref(false)

function submit() {
  loading.value = true
  success.value = ''
  router.post(route('tenants.settings.notifications.update'), form.value, {
    onSuccess: (page) => {
      success.value = page.props.flash.success || 'Settings updated successfully.'
      // Only update form if backend returns new settings (prevents overwriting with defaults)
      if (page.props.settings) {
        Object.assign(form.value, { ...defaultForm, ...page.props.settings })
      }
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

// Watch for initial page.props.settings changes (e.g. on first load)
import { watch } from 'vue'
watch(() => page.props.settings, (val) => {
  if (val) {
    Object.assign(form.value, { ...defaultForm, ...val })
  }
})
</script>

<template>
  <Head title="Notification Settings" />
    <div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 flex items-center gap-2">
        <span>Notification Settings</span>
      </h2>
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Email Notifications -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="email_enabled" value="Email Notifications" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="email_enabled" v-model="form.email_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Receive alerts and updates via email.</p>
          <div v-if="form.email_enabled" class="mt-4">
            <InputLabel for="notification_email" value="Notification Email Address" />
            <TextInput id="notification_email" type="email" class="mt-1 block w-full md:w-2/3" v-model="form.notification_email" placeholder="e.g., alerts@myisp.com" />
            <InputError class="mt-2" :message="page.props.errors.notification_email" />
          </div>
        </div>
        <!-- SMS Notifications -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="sms_enabled" value="SMS Notifications" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="sms_enabled" v-model="form.sms_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Receive critical alerts via SMS.</p>
        </div>
        <!-- WhatsApp Notifications -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="whatsapp_enabled" value="WhatsApp Notifications" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="whatsapp_enabled" v-model="form.whatsapp_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Receive updates and alerts on WhatsApp.</p>
        </div>
        <!-- Common Notification Phone -->
        <div v-if="form.sms_enabled || form.whatsapp_enabled" class="p-4 border dark:border-gray-700 rounded-lg">
          <InputLabel for="notification_phone" value="Notification Phone Number" />
          <TextInput id="notification_phone" type="text" class="mt-1 block w-full md:w-2/3" v-model="form.notification_phone" placeholder="e.g., +254700000000" />
          <InputError class="mt-2" :message="page.props.errors.notification_phone" />
        </div>
        <!-- User Expiry Reminders -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">User Expiry Reminders</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Automatically notify users before their internet package expires. Notifications will be sent using the channels enabled above.</p>
          <div class="space-y-4">
            <!-- Hotspot Reminders -->
            <div class="p-4 border dark:border-gray-600 rounded-lg">
              <div class="flex items-center justify-between">
                <InputLabel for="hotspot_reminders_enabled" value="Hotspot User Reminders" class="font-semibold" />
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="hotspot_reminders_enabled" v-model="form.reminders.hotspot.enabled" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div v-if="form.reminders.hotspot.enabled" class="mt-4">
                <InputLabel value="Send reminders on (days before expiry)" class="mb-2" />
                <Multiselect v-model="form.reminders.hotspot.days_before" mode="tags" :options="[{ value: 0, label: 'On Expiry Day' }, { value: 1, label: '1 Day Before' }, { value: 2, label: '2 Days Before' }, { value: 3, label: '3 Days Before' }, { value: 7, label: '7 Days Before' }]" :close-on-select="false" placeholder="Select reminder days" />
                <InputError class="mt-2" :message="page.props.errors['reminders.hotspot.days_before']" />
                <InputLabel for="hotspot_message" value="Reminder Message" class="mt-4 mb-2" />
                <TextArea id="hotspot_message" v-model="form.reminders.hotspot.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {name}, {package_name}, {expires_at}, {days_left}</p>
  <button type="button" @click="form.reminders.hotspot.message = defaultHotspotReminder" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['reminders.hotspot.message']" />
              </div>
            </div>
            <!-- PPPoE Reminders -->
            <div class="p-4 border dark:border-gray-600 rounded-lg">
              <div class="flex items-center justify-between">
                <InputLabel for="pppoe_reminders_enabled" value="PPPoE User Reminders" class="font-semibold" />
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="pppoe_reminders_enabled" v-model="form.reminders.pppoe.enabled" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div v-if="form.reminders.pppoe.enabled" class="mt-4">
                <InputLabel value="Send reminders on (days before expiry)" class="mb-2" />
                <Multiselect v-model="form.reminders.pppoe.days_before" mode="tags" :options="[{ value: 0, label: 'On Expiry Day' }, { value: 1, label: '1 Day Before' }, { value: 2, label: '2 Days Before' }, { value: 3, label: '3 Days Before' }, { value: 7, label: '7 Days Before' }]" :close-on-select="false" placeholder="Select reminder days" />
                <InputError class="mt-2" :message="page.props.errors['reminders.pppoe.days_before']" />
                <InputLabel for="pppoe_message" value="Reminder Message" class="mt-4 mb-2" />
                <TextArea id="pppoe_message" v-model="form.reminders.pppoe.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {name}, {package_name}, {expires_at}, {days_left}</p>
  <button type="button" @click="form.reminders.pppoe.message = defaultPPPoEReminder" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['reminders.pppoe.message']" />
              </div>
            </div>
            <!-- Static IP Reminders -->
            <div class="p-4 border dark:border-gray-600 rounded-lg">
              <div class="flex items-center justify-between">
                <InputLabel for="static_reminders_enabled" value="Static IP User Reminders" class="font-semibold" />
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="static_reminders_enabled" v-model="form.reminders.static.enabled" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              <div v-if="form.reminders.static.enabled" class="mt-4">
                <InputLabel value="Send reminders on (days before expiry)" class="mb-2" />
                <Multiselect v-model="form.reminders.static.days_before" mode="tags" :options="[{ value: 0, label: 'On Expiry Day' }, { value: 1, label: '1 Day Before' }, { value: 2, label: '2 Days Before' }, { value: 3, label: '3 Days Before' }, { value: 7, label: '7 Days Before' }]" :close-on-select="false" placeholder="Select reminder days" />
                <InputError class="mt-2" :message="page.props.errors['reminders.static.days_before']" />
                <InputLabel for="static_message" value="Reminder Message" class="mt-4 mb-2" />
                <TextArea id="static_message" v-model="form.reminders.static.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {name}, {package_name}, {expires_at}, {days_left}</p>
  <button type="button" @click="form.reminders.static.message = defaultStaticReminder" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['reminders.static.message']" />
              </div>
            </div>
          </div>
        </div>
        <!-- Payment Confirmation -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="payment_confirmation_enabled" value="Payment Confirmation SMS" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="payment_confirmation_enabled" v-model="form.payment_confirmation.enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div v-if="form.payment_confirmation.enabled" class="mt-4">
            <InputLabel for="payment_confirmation_message" value="Confirmation Message" class="mb-2" />
            <TextArea id="payment_confirmation_message" v-model="form.payment_confirmation.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {name}, {package_name}, {amount}, {transaction_id}</p>
  <button type="button" @click="form.payment_confirmation.message = defaultPaymentConfirmation" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['payment_confirmation.message']" />
          </div>
        </div>
        <!-- Final Expiry Notification -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="final_expiry_enabled" value="Final Expiry SMS" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="final_expiry_enabled" v-model="form.final_expiry_notification.enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div v-if="form.final_expiry_notification.enabled" class="mt-4">
            <InputLabel for="final_expiry_message" value="Expiry Message" class="mb-2" />
            <TextArea id="final_expiry_message" v-model="form.final_expiry_notification.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {name}, {package_name}, {expires_at}</p>
  <button type="button" @click="form.final_expiry_notification.message = defaultFinalExpiry" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['final_expiry_notification.message']" />
          </div>
        </div>
        <!-- Mikrotik Status Alert -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <InputLabel for="mikrotik_status_enabled" value="Mikrotik Status Alert" class="font-semibold text-lg" />
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" id="mikrotik_status_enabled" v-model="form.mikrotik_status_alert.enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <div v-if="form.mikrotik_status_alert.enabled" class="mt-4">
            <InputLabel for="mikrotik_alert_message" value="Alert Message" class="mb-2" />
            <TextArea id="mikrotik_alert_message" v-model="form.mikrotik_status_alert.message" class="w-full" rows="4" />
<div class="flex items-center gap-2 mt-2">
  <p class="text-xs text-gray-500">Placeholders: {router_name}, {router_ip}, {offline_duration}</p>
  <button type="button" @click="form.mikrotik_status_alert.message = defaultMikrotikAlert" class="text-blue-600 text-xs underline ml-auto">Reset to Default</button>
</div>
<InputError class="mt-2" :message="page.props.errors['mikrotik_status_alert.message']" />
          </div>
        </div>
        <div class="flex items-center justify-end mt-8">
          <PrimaryButton :class="{ 'opacity-25': loading }" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save Settings' }}
          </PrimaryButton>
        </div>
        <div v-if="success" class="mt-4 p-4 bg-green-100 dark:bg-green-800 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-md">
          {{ success }}
        </div>
      </form>
    </div>
</template>

<style scoped>
input[type='checkbox'].peer:checked + div {
  background-color: #2563eb;
}
</style>
