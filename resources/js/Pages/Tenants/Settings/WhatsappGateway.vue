<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  gateways: { type: Array, default: () => [] },
  user_id: { type: [String, Number], default: '' },
})

const PROVIDERS = [
  { value: 'twilio', label: 'Twilio (Default, managed by platform)' },
  { value: 'custom', label: 'Custom Provider' },
]

const initial = props.gateways[0] || { provider: 'twilio' }
const form = useForm({
  provider: initial.provider || 'twilio',
  api_key: initial.api_key || '',
  api_secret: initial.api_secret || '',
  phone_number: initial.phone_number || '',
  webhook_url: initial.webhook_url || '',
  status_callback_url: initial.status_callback_url || '',
  label: initial.label || '',
  is_active: initial.is_active || false,
  custom_parameters: initial.custom_parameters || {},
})

const testing = ref(false)
const testResult = ref(null)

const showCredentials = computed(() => form.provider !== 'twilio')

const save = () => {
  form.post(route('tenants.settings.whatsapp_gateway.update'), {
    preserveScroll: true,
    onSuccess: () => {
      testResult.value = null
    }
  })
}

const testGateway = () => {
  testing.value = true
  testResult.value = null
  form.post(route('tenants.settings.whatsapp_gateway.test'), {
    preserveScroll: true,
    onSuccess: () => {
      testResult.value = 'Success! Test message sent.'
      testing.value = false
    },
    onError: () => {
      testResult.value = 'Failed to send test message.'
      testing.value = false
    }
  })
}
</script>

<template>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h3 class="text-xl font-bold mb-4">Tenant WhatsApp Gateway</h3>
    <div v-if="props.gateways && props.gateways.length > 0" class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
      <div class="font-semibold mb-2">Current Saved Gateway:</div>
      <div><span class="font-medium">Provider:</span> {{ props.gateways[0].provider === 'twilio' ? 'Twilio (Default)' : props.gateways[0].provider }}</div>
      <div v-if="props.gateways[0].phone_number"><span class="font-medium">From Number:</span> {{ props.gateways[0].phone_number }}</div>
      <div v-if="props.gateways[0].label"><span class="font-medium">Label:</span> {{ props.gateways[0].label }}</div>
      <div v-if="props.gateways[0].webhook_url"><span class="font-medium">Webhook URL:</span> {{ props.gateways[0].webhook_url }}</div>
      <div v-if="props.gateways[0].status_callback_url"><span class="font-medium">Status Callback:</span> {{ props.gateways[0].status_callback_url }}</div>
      <div v-if="props.gateways[0].is_active"><span class="text-green-600 font-bold">Active</span></div>
    </div>
    <form @submit.prevent="save">
      <div class="mb-4">
        <label class="block font-medium mb-1">WhatsApp Provider</label>
        <select v-model="form.provider" class="input input-bordered w-full">
          <option v-for="p in PROVIDERS" :key="p.value" :value="p.value">{{ p.label }}</option>
        </select>
      </div>
      <div v-if="showCredentials">
        <div class="mb-4">
          <label class="block font-medium mb-1">API Key / SID</label>
          <input v-model="form.api_key" class="input input-bordered w-full" placeholder="Enter API Key or SID" />
        </div>
        <div class="mb-4">
          <label class="block font-medium mb-1">API Secret / Token</label>
          <input v-model="form.api_secret" type="password" class="input input-bordered w-full" placeholder="Enter API Secret or Token" />
        </div>
        <div class="mb-4">
          <label class="block font-medium mb-1">WhatsApp From Number</label>
          <input v-model="form.phone_number" class="input input-bordered w-full" placeholder="whatsapp:+1234567890" />
        </div>
        <div class="mb-4">
          <label class="block font-medium mb-1">Webhook URL (optional)</label>
          <input v-model="form.webhook_url" class="input input-bordered w-full" placeholder="https://..." />
        </div>
        <div class="mb-4">
          <label class="block font-medium mb-1">Status Callback URL (optional)</label>
          <input v-model="form.status_callback_url" class="input input-bordered w-full" placeholder="https://..." />
        </div>
      </div>
      <div class="mb-4 flex items-center">
        <input type="checkbox" v-model="form.is_active" id="is_active" class="mr-2" />
        <label for="is_active">Active</label>
      </div>
      <div class="flex gap-4 mt-6">
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</template>
