<template>
  <div class="space-y-4">
    <div>
      <InputLabel for="ip_address" value="Router IP Address" />
      <TextInput id="ip_address" v-model="ip" placeholder="e.g. 192.168.88.1" class="mt-1 block w-full" />
      <InputError :message="error" />
      <div class="flex gap-2 mt-2">
        <PrimaryButton @click="setIp" :disabled="loading">Set IP</PrimaryButton>
        <SecondaryButton @click="autoDetect" :disabled="loading">Auto-Detect</SecondaryButton>
        <SecondaryButton @click="$emit('prev')">Back</SecondaryButton>
      </div>
    </div>
    <div v-if="status" class="mt-4">
      <div class="font-semibold">Status:</div>
      <div :class="statusClass">{{ statusText }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { route } from 'ziggy-js'

const props = defineProps({
  router: Object,
})
const emit = defineEmits(['ip-set'])

const ip = ref(props.router?.ip_address || '')
const error = ref('')
const status = ref(null)
const loading = ref(false)

function setIp() {
  if (!ip.value) {
    error.value = 'IP address is required.'
    return
  }
  error.value = ''
  loading.value = true
  fetch(route('tenants.mikrotiks.setIp', props.router.id), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ip_address: ip.value }),
  })
    .then(res => res.json())
    .then(data => {
      status.value = data.status
      emit('ip-set', data)
    })
    .catch(err => {
      error.value = err.message
    })
    .finally(() => {
      loading.value = false
    })
}

function autoDetect() {
  error.value = ''
  loading.value = true
  fetch(route('tenants.mikrotiks.autoDetect', props.router.id))
    .then(res => res.json())
    .then(data => {
      ip.value = data.ip_address
      status.value = data.status
      emit('ip-set', data)
    })
    .catch(err => {
      error.value = err.message
    })
    .finally(() => {
      loading.value = false
    })
}

const statusClass = computed(() => {
  if (!status.value) return ''
  return status.value === 'online' ? 'text-green-600' : 'text-red-600'
})
const statusText = computed(() => {
  if (!status.value) return 'Unknown'
  return status.value === 'online' ? 'Router is online!' : 'Router is offline.'
})
</script> 