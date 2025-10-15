<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { route } from 'ziggy-js'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputError from '@/Components/InputError.vue'
import Dropdown from '@/Components/Dropdown.vue'
import { Plus, Edit, Eye, Trash2, Wifi, Download, ExternalLink, Activity, MoreHorizontal } from 'lucide-vue-next'

const props = defineProps({
  routers: Array,
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDetails = ref(false)
const showRemoteModal = ref(false)
const selectedRouter = ref(null)
const remoteLinks = ref({})
const pinging = ref({})
const testing = ref({})
const formError = ref('')
const actionsOpen = ref({})

function toggleActions(id) {
  actionsOpen.value[id] = !actionsOpen.value[id]
}

function closeAllActions() {
  actionsOpen.value = {}
}

// Add event listener to close actions on outside click
if (typeof window !== 'undefined') {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.router-actions-toggle')) {
      closeAllActions()
    }
  })
}

const form = useForm({
  name: '',
  router_username: '',
  router_password: '',
  notes: '',
  ip_address: '',
  api_port: '',
  ssh_port: '',
  connection_type: 'api',
  openvpn_profile_id: null,
})

function closeModal() {
  showAddModal.value = false
  showEditModal.value = false
  form.reset()
  formError.value = ''
}

async function submitForm() {
  await form.post(route('tenants.mikrotiks.store'), {
    onSuccess: () => {
      // Do not close the modal here; let Inertia handle the redirect to SetupScript.vue
      // closeModal() removed
    },
    onError: (errors) => {
      alert('Error adding router: ' + Object.values(errors).flat().join(', '))
    }
  })
}

function editForm() {
  if (selectedRouter.value) {
    form.put(route('tenants.mikrotiks.update', selectedRouter.value.id), {
      onSuccess: () => {
        closeModal()
        Inertia.reload({ only: ['routers'], preserveScroll: true })
      },
      onError: (errors) => {
        formError.value = 'Error updating router: ' + Object.values(errors).flat().join(', ')
      }
    })
  }
}

function editRouter(router) {
  selectedRouter.value = router
  form.name = router.name
  form.ip_address = router.ip_address || ''
  form.api_port = router.api_port || ''
  form.ssh_port = router.ssh_port || ''
  form.router_username = router.router_username
  form.router_password = ''
  form.connection_type = router.connection_type || 'api'
  form.openvpn_profile_id = router.openvpn_profile_id || null
  form.notes = router.notes
  showEditModal.value = true
  formError.value = ''
}

function viewRouter(router) {
  selectedRouter.value = router
  showDetails.value = true
}

function deleteRouter(mikrotik) {
  if (confirm('Delete this router?')) {
    Inertia.delete(route('tenants.mikrotiks.destroy', mikrotik.id), {
      onSuccess: () => {
        // Optionally reload or show a message
      }
    })
  }
}

function pingRouter(router) {
  pinging.value[router.id] = true
  formError.value = ''
  fetch(route('tenants.mikrotiks.ping', router.id))
    .then(async res => {
      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.message || 'Unknown error')
      }
      return res.json()
    })
    .then(data => {
      alert(data.message)
      router.status = data.status
      router.last_seen_at = data.last_seen_at
    })
    .catch(err => {
      formError.value = 'Error pinging router: ' + err.message
    })
    .finally(() => {
      pinging.value[router.id] = false
    })
}

function testRouterConnection(router) {
  testing.value[router.id] = true
  formError.value = ''
  fetch(route('tenants.mikrotiks.testConnection', router.id))
    .then(async res => {
      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.message || 'Unknown error')
      }
      return res.json()
    })
    .then(data => {
      alert(data.message)
    })
    .catch(err => {
      formError.value = 'Error testing connection: ' + err.message
    })
    .finally(() => {
      testing.value[router.id] = false
    })
}

function showRemote(router) {
  formError.value = ''
  fetch(route('tenants.mikrotiks.remoteManagement', router.id))
    .then(async res => {
      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.message || 'Unknown error')
      }
      return res.json()
    })
    .then(data => {
      remoteLinks.value = data
      showRemoteModal.value = true
    })
    .catch(err => {
      formError.value = 'Error loading remote management links: ' + err.message
    })
}

function formatUptime(uptime) {
  if (!uptime) return '-'
  const days = Math.floor(uptime / 86400)
  const hours = Math.floor((uptime % 86400) / 3600)
  const minutes = Math.floor((uptime % 3600) / 60)
  return `${days}d ${hours}h ${minutes}m`
}

function formatBytes(bytes) {
  if (!bytes && bytes !== 0) return '-'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024))
  pow = Math.min(pow, units.length - 1)
  const val = bytes / Math.pow(1024, pow)
  return `${val.toFixed(2)} ${units[pow]}`
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
          <Wifi class="w-6 h-6 text-blue-600" />
          Mikrotik Routers
        </h2>
        <PrimaryButton @click="showAddModal = true" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Add Router
        </PrimaryButton>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Router Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS Version</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Seen</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="router in routers" :key="router.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ router.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ router.ip_address }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                        router.status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ router.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ router.model || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ router.os_version || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ router.last_seen_at ? new Date(router.last_seen_at).toLocaleString() : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="relative flex items-center justify-end">
                        <button @click.stop="toggleActions(router.id)" class="router-actions-toggle p-2 hover:bg-gray-100 rounded" :aria-expanded="actionsOpen[router.id] ? 'true' : 'false'" title="Show actions">
                          <MoreHorizontal class="w-5 h-5 text-gray-600" />
                        </button>
                        <transition name="fade">
                          <div v-if="actionsOpen[router.id]" class="absolute right-0 mt-2 flex space-x-2 bg-white border rounded shadow-lg p-2 z-50">
                            <button @click="viewRouter(router); closeAllActions()" title="View" class="p-2 hover:bg-gray-100 rounded">
                              <Eye class="w-5 h-5 text-blue-600" />
                            </button>
                            <button @click="editRouter(router); closeAllActions()" title="Edit" class="p-2 hover:bg-gray-100 rounded">
                              <Edit class="w-5 h-5 text-yellow-600" />
                            </button>
                            <button @click="pingRouter(router); closeAllActions()" :disabled="pinging[router.id]" title="Ping" class="p-2 hover:bg-gray-100 rounded">
                              <Activity class="w-5 h-5 text-green-600" />
                            </button>
                            <button @click="Inertia.visit(route('tenants.mikrotiks.reprovision', router.id)); closeAllActions()" title="Reprovision/Show Script" class="p-2 hover:bg-gray-100 rounded">
                              <Download class="w-5 h-5 text-indigo-600" />
                            </button>
                            <button @click="deleteRouter(router); closeAllActions()" title="Delete" class="p-2 hover:bg-red-50 rounded">
                              <Trash2 class="w-5 h-5 text-red-600" />
                            </button>
                          </div>
                        </transition>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!routers.length">
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No Mikrotik routers found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="formError" class="mb-4 p-2 bg-red-100 text-red-700 rounded">{{ formError }}</div>

    <!-- Add Router Wizard Modal -->
    <Modal :show="showAddModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Add Mikrotik Router</h3>
        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <InputLabel for="name" value="Router Name" />
            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
            <InputError :message="form.errors.name" />
          </div>
          <div class="mb-4">
            <InputLabel for="router_username" value="Username" />
            <TextInput id="router_username" v-model="form.router_username" class="mt-1 block w-full" required autocomplete="username" />
            <InputError :message="form.errors.router_username" />
          </div>
          <div class="mb-4">
            <InputLabel for="router_password" value="Password" />
            <TextInput id="router_password" v-model="form.router_password" class="mt-1 block w-full" type="password" required autocomplete="current-password" />
            <InputError :message="form.errors.router_password" />
          </div>
          <div class="mb-4">
            <InputLabel for="notes" value="Notes (optional)" />
            <TextArea id="notes" v-model="form.notes" class="mt-1 block w-full" />
            <InputError :message="form.errors.notes" />
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <PrimaryButton type="submit">Add Router</PrimaryButton>
            <DangerButton type="button" @click="closeModal">Cancel</DangerButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Edit Router Modal -->
    <Modal :show="showEditModal" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Mikrotik Router</h2>
        <form @submit.prevent="editForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel value="Router Name" />
              <TextInput v-model="form.name" class="mt-1 block w-full" />
              <InputError :message="form.errors.name" />
            </div>
            <div>
              <InputLabel value="IP Address" />
              <TextInput v-model="form.ip_address" class="mt-1 block w-full" />
              <InputError :message="form.errors.ip_address" />
            </div>
            <div>
              <InputLabel value="API Port" />
              <TextInput v-model="form.api_port" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.api_port" />
            </div>
            <div>
              <InputLabel value="SSH Port" />
              <TextInput v-model="form.ssh_port" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.ssh_port" />
            </div>
            <div>
              <InputLabel value="Username" />
              <TextInput v-model="form.router_username" class="mt-1 block w-full" autocomplete="username" />
              <InputError :message="form.errors.router_username" />
            </div>
            <div>
              <InputLabel value="Password (leave blank to keep current)" />
              <TextInput v-model="form.router_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
              <InputError :message="form.errors.router_password" />
            </div>
            <div class="md:col-span-2">
              <InputLabel value="Connection Type" />
              <select v-model="form.connection_type" class="mt-1 block w-full border-gray-300 rounded-md">
                <option value="api">API</option>
                <option value="ssh">SSH</option>
                <option value="ovpn">OVPN</option>
              </select>
              <InputError :message="form.errors.connection_type" />
            </div>
            <div class="md:col-span-2">
              <InputLabel value="OpenVPN Profile (optional)" />
              <select v-model="form.openvpn_profile_id" class="mt-1 block w-full border-gray-300 rounded-md">
                <option :value="null">None</option>
                <option v-for="profile in $page.props.openvpnProfiles || []" :key="profile.id" :value="profile.id">
                  {{ profile.config_path }}
                </option>
              </select>
              <InputError :message="form.errors.openvpn_profile_id" />
            </div>
            <div class="md:col-span-2">
              <InputLabel value="Notes" />
              <TextArea v-model="form.notes" class="mt-1 block w-full" rows="3" />
              <InputError :message="form.errors.notes" />
            </div>
          </div>
          <div class="flex justify-end gap-3 mt-4">
            <DangerButton type="button" @click="closeModal">
              Cancel
            </DangerButton>
            <PrimaryButton :disabled="form.processing">
              Update
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Router Details Modal -->
    <Modal :show="showDetails" @close="showDetails = false" max-width="4xl">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Router Details: {{ selectedRouter?.name }}</h2>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div><span class="font-medium">IP:</span> {{ selectedRouter?.ip_address }}</div>
          <div><span class="font-medium">Model:</span> {{ selectedRouter?.model || '-' }}</div>
          <div><span class="font-medium">OS Version:</span> {{ selectedRouter?.os_version || '-' }}</div>
          <div><span class="font-medium">Uptime:</span> {{ selectedRouter?.uptime ? formatUptime(selectedRouter.uptime) : '-' }}</div>
          <div><span class="font-medium">CPU Usage:</span> {{ selectedRouter?.cpu_usage ?? '-' }}%</div>
          <div><span class="font-medium">Memory Usage:</span> {{ selectedRouter?.memory_usage ?? '-' }}%</div>
          <div v-if="selectedRouter?.temperature"><span class="font-medium">Temperature:</span> {{ selectedRouter.temperature }}°C</div>
          <div><span class="font-medium">Last Seen:</span> {{ selectedRouter?.last_seen_at ? new Date(selectedRouter.last_seen_at).toLocaleString() : '-' }}</div>
        </div>
        <div v-if="selectedRouter?.notes" class="mb-4">
          <span class="font-medium">Notes:</span> {{ selectedRouter.notes }}
        </div>
        
        <div class="mb-4">
          <h3 class="font-semibold mb-2">Logs</h3>
          <div class="max-h-32 overflow-y-auto bg-gray-50 p-2 rounded">
            <div v-for="log in selectedRouter?.logs" :key="log.id" class="text-sm mb-1">
              [{{ log.created_at ? new Date(log.created_at).toLocaleString() : '' }}] {{ log.action }}: {{ log.message }} ({{ log.status }})
            </div>
            <div v-if="!selectedRouter?.logs?.length" class="text-gray-500">No logs found.</div>
          </div>
        </div>
        
        <div class="mb-4">
          <h3 class="font-semibold mb-2">Bandwidth Usage (last 5 records)</h3>
          <div class="max-h-32 overflow-y-auto bg-gray-50 p-2 rounded">
            <div v-for="bw in selectedRouter?.bandwidth_usage?.slice(0,5)" :key="bw.id" class="text-sm mb-1">
              {{ bw.interface_name }}: {{ formatBytes(bw.bytes_in) }} in / {{ formatBytes(bw.bytes_out) }} out @ {{ new Date(bw.timestamp).toLocaleString() }}
            </div>
            <div v-if="!selectedRouter?.bandwidth_usage?.length" class="text-gray-500">No bandwidth data.</div>
          </div>
        </div>
        
        <div class="mb-4">
          <h3 class="font-semibold mb-2">Alerts</h3>
          <div class="max-h-32 overflow-y-auto bg-gray-50 p-2 rounded">
            <div v-for="alert in selectedRouter?.alerts" :key="alert.id" class="text-sm mb-1">
              [{{ alert.severity }}] {{ alert.alert_type }}: {{ alert.message }}
            </div>
            <div v-if="!selectedRouter?.alerts?.length" class="text-gray-500">No alerts.</div>
          </div>
        </div>
        
        <div class="flex justify-end">
          <PrimaryButton @click="showDetails = false">Close</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Remote Management Modal -->
    <Modal :show="showRemoteModal" @close="showRemoteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Remote Management Links</h2>
        <div class="space-y-2">
          <a :href="remoteLinks.winbox" target="_blank" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Open in Winbox
          </a>
          <a :href="remoteLinks.ssh" target="_blank" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Open SSH
          </a>
          <a :href="remoteLinks.api" target="_blank" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Open API
          </a>
        </div>
        <div class="flex justify-end mt-4">
          <PrimaryButton @click="showRemoteModal = false">Close</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

