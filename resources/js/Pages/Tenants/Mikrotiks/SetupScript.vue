<script setup>
import { ref, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  router: Object,
  script: String,
})

const copied = ref(false)

function copyScript() {
  navigator.clipboard.writeText(props.script)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

function downloadScript() {
  const blob = new Blob([props.script], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `setup_router_${props.router.id}.rsc`
  a.click()
  URL.revokeObjectURL(url)
}

const waiting = ref(false)
const online = ref(false)
const pollingError = ref('')
let pollInterval = null

function startPolling() {
  waiting.value = true
  pollingError.value = ''
  pollInterval = setInterval(() => {
    fetch(route('tenants.mikrotiks.ping', props.router.id))
      .then(res => res.json())
      .then(data => {
        if (data.status === 'online') {
          online.value = true
          waiting.value = false
          clearInterval(pollInterval)
        }
      })
      .catch(() => {
        pollingError.value = 'Error checking router status.'
      })
  }, 5000)
}

function stopPolling() {
  if (pollInterval) clearInterval(pollInterval)
}

onUnmounted(() => {
  stopPolling()
})

function proceed() {
  // Redirect to router index or next onboarding step
  router.visit(route('tenants.mikrotiks.index'))
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Mikrotik System Onboarding Script</h2>
    </template>
    <div class="max-w-3xl mx-auto py-8">
      <div class="bg-white shadow rounded p-6">
        <p class="mb-4 text-gray-700">
          <b>Step 1:</b> Copy or download the script below and run it in your Mikrotik terminal (Winbox, WebFig, or SSH).<br>
          <b>Step 2:</b> After running the script, copy the IP address shown in the output and paste it into the system to complete onboarding.<br>
          <b>Step 3:</b> Click <b>I've run the script</b> to begin checking if your router is online. Once detected, you can continue.
        </p>
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-900 rounded">
          <b>What does this script do?</b>
          <ul class="list-disc ml-6 mt-2 text-sm">
            <li>Sets the router's identity (name)</li>
            <li>Prints all current IP addresses for onboarding</li>
            <li>Enables API and adds a system API user</li>
            <li>Restricts API/Winbox/SSH to trusted IPs (edit as needed)</li>
            <li>Enables OVPN server and RADIUS authentication for PPP, Hotspot, and OVPN</li>
            <li>Adds a RADIUS client for full system authentication</li>
            <li>Adds a <b>disconnect-user</b> script for remote user management</li>
            <li>Adds a <b>health-check</b> scheduler for system monitoring</li>
            <li>Follows best security and automation practices</li>
          </ul>
        </div>
        <div class="flex gap-2 mb-4">
          <PrimaryButton @click="copyScript">{{ copied ? 'Copied!' : 'Copy Script' }}</PrimaryButton>
          <PrimaryButton @click="downloadScript">Download Script</PrimaryButton>
        </div>
        <pre class="bg-gray-900 text-green-200 rounded p-4 overflow-x-auto text-xs mb-6" style="min-height: 300px;">
{{ script }}
        </pre>
        <div class="flex gap-2 mb-4">
          <PrimaryButton @click="startPolling" :disabled="waiting || online">I've run the script</PrimaryButton>
          <span v-if="waiting" class="flex items-center gap-2 text-blue-600"><svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Waiting for router to come online...</span>
          <span v-if="online" class="text-green-600 font-semibold">Router is online! You can continue.</span>
          <span v-if="pollingError" class="text-red-600">{{ pollingError }}</span>
        </div>
        <div class="flex justify-end">
          <PrimaryButton @click="proceed" :disabled="!online">Next</PrimaryButton>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 