<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { useToast } from 'vue-toastification'
const toast = useToast()
const toastOptions = { position: 'top-right', timeout: 4000, closeOnClick: true, pauseOnHover: true, maxToasts: 1 }
import BaseTable from '@/Components/BaseTable.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  gateways: { type: Array, default: () => [] }
})

const showModal = ref(false)
const editing = ref(false)
const showDetailsModal = ref(false)
const detailsGateway = ref({})
async function openDetails() {
  // Always fetch latest gateway data from backend before showing modal
  try {
  const response = await fetch('/tenants/settings/sms-gateway/json', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (response.ok) {
      const page = await response.json();
      const latest = page.props?.gateways?.[0] || defaultGateway;
      detailsGateway.value = { ...latest };
    } else {
      detailsGateway.value = props.gateways[0] ? { ...props.gateways[0] } : { ...defaultGateway };
    }
  } catch {
    detailsGateway.value = props.gateways[0] ? { ...props.gateways[0] } : { ...defaultGateway };
  }
  showDetailsModal.value = true;
}

// Add TALKSASA as default if not present
const defaultGateway = {
  id: 'default-talksasa',
  provider: 'talksasa',
  label: 'TALKSASA (Default)',
  is_active: true,
  username: '',
  sender_id: '',
  webhook_url: '',
  status_callback_url: '',
  region: '',
  custom_parameters: {},
  api_key: '',
  api_secret: '',
  is_default: true,
}


const form = useForm({
  id: '',
  provider: '',
  api_key: '',
  api_secret: '',
  username: '',
  sender_id: '',
  webhook_url: '',
  status_callback_url: '',
  region: '',
  custom_parameters: {},
  label: '',
  is_active: false,
});

import { watch, onMounted } from 'vue';

function setFormFromGateway(gateway) {
  form.id = gateway.id || null;
  form.provider = gateway.provider || '';
  form.api_key = gateway.api_key || '';
  form.api_secret = gateway.api_secret || '';
  form.username = gateway.username || '';
  form.sender_id = gateway.sender_id || '';
  form.webhook_url = gateway.webhook_url || '';
  form.status_callback_url = gateway.status_callback_url || '';
  form.region = gateway.region || '';
  form.custom_parameters = gateway.custom_parameters || {};
  form.label = gateway.label || '';
  form.is_active = typeof gateway.is_active === 'boolean' ? gateway.is_active : false;
}

onMounted(() => {
  if (props.gateways.length > 0) {
    setFormFromGateway(props.gateways[0]);
  } else {
    setFormFromGateway(defaultGateway);
  }
});

watch(() => form.provider, (newProvider) => {
  const gw = props.gateways.find(g => g.provider === newProvider);
  if (gw) {
    setFormFromGateway(gw);
  }
});

const openAdd = () => {
  editing.value = false
  form.reset()
  showModal.value = true
}
const openEdit = (gateway) => {
  editing.value = true
  form.reset()
  form.id = gateway.id
  form.provider = gateway.provider
  form.label = gateway.label
  form.is_active = gateway.is_active
  form.username = gateway.username
  form.sender_id = gateway.sender_id
  form.webhook_url = gateway.webhook_url
  form.status_callback_url = gateway.status_callback_url
  form.region = gateway.region
  form.custom_parameters = gateway.custom_parameters || {}
  // Do not prefill sensitive fields
  form.api_key = ''
  form.api_secret = ''
  showModal.value = true
}
const save = () => {
  const providerLabel = {
    talksasa: 'TALKSASA',
    africastalking: "Africa's Talking",
    twilio: 'Twilio',
    custom: 'Custom',
  };
  const providerName = providerLabel[form.provider] || form.provider;
  const loadingToastId = toast.info(`Saving SMS gateway settings for ${providerName}...`, { ...toastOptions, timeout: 2000 });
  const handleSuccess = (page) => {
    showModal.value = false;
    toast.dismiss(loadingToastId);
    if (editing.value) {
      toast.success(`Successfully updated SMS gateway: ${providerName}.`, toastOptions);
    } else {
      toast.success(`Added new SMS gateway: ${providerName}.`, toastOptions);
    }
  };
  const handleError = (errs) => {
    toast.dismiss(loadingToastId);
    const msg = Object.values(errs).flat().join(' ');
    toast.error(msg || 'Failed to update SMS gateway.', { ...toastOptions, timeout: 7000 });
  };
  if (editing.value) {
    router.put(route('tenants.settings.sms_gateway.update'), form, {
      onSuccess: handleSuccess,
      onError: handleError
    })
  } else {
    router.post(route('tenants.settings.sms_gateway.update'), form, {
      onSuccess: handleSuccess,
      onError: handleError
    })
  }
}
const remove = (gateway) => {
  if (confirm('Delete this SMS gateway?')) {
    const loadingToastId = toast.info('Deleting SMS gateway...', { ...toastOptions, timeout: 2000 });
    router.delete(route('tenants.settings.sms.delete', gateway.id), {
      onSuccess: () => {
        toast.dismiss(loadingToastId);
        toast.success('Gateway deleted.', toastOptions);
      },
      onError: (errs) => {
        toast.dismiss(loadingToastId);
        toast.error(Object.values(errs).flat().join(' ') || 'Failed to delete gateway.', { ...toastOptions, timeout: 7000 });
      }
    })
  }
}
const activate = (gateway) => {
  const loadingToastId = toast.info('Activating SMS gateway...', { ...toastOptions, timeout: 2000 });
  router.post(route('tenants.settings.sms.activate', gateway.id), {
    onSuccess: () => {
      toast.dismiss(loadingToastId);
      toast.success('Gateway activated.', toastOptions);
    },
    onError: (errs) => {
      toast.dismiss(loadingToastId);
      toast.error(Object.values(errs).flat().join(' ') || 'Failed to activate gateway.', { ...toastOptions, timeout: 7000 });
    }
  })
}

const headers = [
  { key: 'provider', label: 'Provider' },
  { key: 'label', label: 'Label' },
  { key: 'is_active', label: 'Active' },
  { key: 'actions', label: '' }
]

</script>
<template>
  <div class="flex justify-center items-center min-h-[60vh] bg-gradient-to-br from-blue-50 to-indigo-100 animate-fade-in">
    <div class="w-full max-w-lg bg-white rounded-xl shadow-lg p-8 border border-indigo-100">
      <div class="flex items-center mb-6">
        <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2"></path><rect width="12" height="8" x="6" y="4" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M6 16h.01"/></svg>
        <h3 class="text-2xl font-bold text-indigo-700">SMS Gateway Selection</h3>
      </div>
      <label class="block font-semibold mb-2 text-indigo-600">Select SMS Gateway</label>
      <select v-model="form.provider" class="input input-bordered w-full mb-6 focus:ring-2 focus:ring-indigo-400">
        <option value="talksasa">TALKSASA (Default)</option>
        <option value="bytewave">Bytewave</option>
        <option value="africastalking">Africa's Talking</option>
        <option value="textsms">TextSMS</option>
        <option value="mobitech">Mobitech</option>
        <option value="twilio">Twilio</option>
        <option value="custom">Custom</option>
      </select>
      <transition name="fade">
        <div v-if="form.provider" class="space-y-4">
          <!-- TALKSASA & Bytewave: api_key, sender_id only -->
          <template v-if="['talksasa','bytewave'].includes(form.provider)">
            <div>
              <label class="block font-semibold text-gray-700">API Key</label>
              <input v-model="form.api_key" class="input input-bordered w-full" type="password" autocomplete="new-password" placeholder="Enter API Key" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700">Sender ID</label>
              <input v-model="form.sender_id" class="input input-bordered w-full" type="text" placeholder="Enter Sender ID" />
            </div>
          </template>
          <!-- AfricasTalking, TextSMS, Mobitech: username, api_key, sender_id -->
          <template v-else-if="['africastalking','textsms','mobitech'].includes(form.provider)">
            <div>
              <label class="block font-semibold text-gray-700">Username</label>
              <input v-model="form.username" class="input input-bordered w-full" type="text" placeholder="Enter Username" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700">API Key</label>
              <input v-model="form.api_key" class="input input-bordered w-full" type="password" autocomplete="new-password" placeholder="Enter API Key" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700">Sender ID</label>
              <input v-model="form.sender_id" class="input input-bordered w-full" type="text" placeholder="Enter Sender ID" />
            </div>
          </template>
          <!-- Other providers: fallback to API Key and API Secret -->
          <template v-else>
            <div>
              <label class="block font-semibold text-gray-700">API Key</label>
              <input v-model="form.api_key" class="input input-bordered w-full" type="password" autocomplete="new-password" placeholder="Enter API Key" />
            </div>
            <div>
              <label class="block font-semibold text-gray-700">API Secret</label>
              <input v-model="form.api_secret" class="input input-bordered w-full" type="password" autocomplete="new-password" placeholder="Enter API Secret" />
            </div>
          </template>
          <div class="flex justify-between mt-6">
            <button class="btn btn-outline btn-info mr-2" type="button" @click="openDetails">
              Show Details
            </button>
            <button class="btn btn-indigo btn-lg shadow transition hover:scale-105" type="button" @click="save">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              Save Gateway
            </button>
          </div>
  <Modal :show="showDetailsModal" @close="showDetailsModal = false">
    <template #header>
      <div class="flex items-center gap-3">
        <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2"></path><rect width="12" height="8" x="6" y="4" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M6 16h.01"/></svg>
        <h3 class="text-xl font-bold text-indigo-700">Current SMS Gateway Details</h3>
      </div>
    </template>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg p-6 shadow space-y-4">
      <div class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Provider:</span>
        <span class="px-2 py-1 rounded bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center gap-1">
          <svg v-if="detailsGateway.provider === 'talksasa'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
          {{ detailsGateway.provider?.toUpperCase() || 'N/A' }}
        </span>
      </div>
      <div v-if="detailsGateway.label" class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Label:</span>
        <span class="px-2 py-1 rounded bg-blue-100 text-blue-700 text-sm">{{ detailsGateway.label }}</span>
      </div>
      <div v-if="detailsGateway.username" class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Username:</span>
        <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-sm">{{ detailsGateway.username }}</span>
      </div>
      <div v-if="detailsGateway.api_key" class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">API Key:</span>
        <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-sm">{{ detailsGateway.api_key }}</span>
      </div>
      <div v-if="detailsGateway.sender_id" class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Sender ID:</span>
        <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 text-sm">{{ detailsGateway.sender_id }}</span>
      </div>
      <div v-if="detailsGateway.api_secret" class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">API Secret:</span>
        <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-sm">{{ detailsGateway.api_secret }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Active:</span>
        <span :class="detailsGateway.is_active ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600'" class="px-2 py-1 rounded font-bold text-sm">
          <svg v-if="detailsGateway.is_active" class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          {{ detailsGateway.is_active ? 'Active' : 'Inactive' }}
        </span>
      </div>
    </div>
    <template #footer>
      <div class="flex justify-end mt-4">
        <button class="btn btn-outline btn-lg" @click="showDetailsModal = false">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          Close
        </button>
      </div>
    </template>
  </Modal>
        </div>
      </transition>
    </div>
  </div>
</template>
