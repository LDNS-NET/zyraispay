<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputError from '@/Components/InputError.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  messages: Object,
  balance: [Object, Number],
  subscribers: Array,
  templates: Array,
})

// --- Compose SMS ---
const showCompose = ref(false)
const composeForm = useForm({
  recipients: [],
  message: '',
  template_id: '',
})
const smsCostPerCharacter = 0.01 // 0.01 = 1 cent per character, matches backend
const costPerMessage = computed(() => Math.round(composeForm.message.length * smsCostPerCharacter * 100)) // in cents
const totalCost = computed(() => costPerMessage.value * composeForm.recipients.length)

watch(() => composeForm.template_id, (id) => {
  if (id) {
    const template = props.templates.find(t => t.id === id)
    if (template) {
      composeForm.message = template.content
    }
  }
})

function openCompose() {
  composeForm.reset()
  showCompose.value = true
}

function sendSMS() {
  composeForm.post(route('tenants.sms.store'), {
    onSuccess: () => {
      showCompose.value = false
      composeForm.reset()
      router.reload({ only: ['messages', 'balance'] })
    }
  })
}

// --- Template Management ---
const showTemplateModal = ref(false)
const editingTemplate = ref(null)
const templateForm = useForm({
  name: '',
  content: '',
})
function openTemplateModal(template = null) {
  editingTemplate.value = template ? template.id : null
  templateForm.name = template ? template.name : ''
  templateForm.content = template ? template.content : ''
  showTemplateModal.value = true
}
function saveTemplate() {
  if (editingTemplate.value) {
    templateForm.put(route('tenants.sms-templates.update', editingTemplate.value), {
      onSuccess: () => {
        showTemplateModal.value = false
        templateForm.reset()
        router.reload({ only: ['templates'] })
      }
    })
  } else {
    templateForm.post(route('tenants.sms-templates.store'), {
      onSuccess: () => {
        showTemplateModal.value = false
        templateForm.reset()
        router.reload({ only: ['templates'] })
      }
    })
  }
}
function deleteTemplate(id) {
  if (confirm('Delete this template?')) {
    router.delete(route('tenants.sms-templates.destroy', id), {
      onSuccess: () => router.reload({ only: ['templates'] })
    })
  }
}

// --- SMS History ---
const statusColors = {
  sent: 'bg-green-100 text-green-700',
  pending: 'bg-yellow-100 text-yellow-700',
  failed: 'bg-red-100 text-red-700',
}

const recipientSearch = ref('')
const filteredSubscribers = computed(() => {
  if (!recipientSearch.value) return props.subscribers
  return props.subscribers.filter(s =>
    s.full_name.toLowerCase().includes(recipientSearch.value.toLowerCase()) ||
    s.phone.includes(recipientSearch.value)
  )
})
const allSelected = computed({
  get() {
    return filteredSubscribers.value.length > 0 && filteredSubscribers.value.every(s => composeForm.recipients.includes(s.phone))
  },
  set(val) {
    if (val) {
      const phones = filteredSubscribers.value.map(s => s.phone)
      composeForm.recipients = Array.from(new Set([...composeForm.recipients, ...phones]))
    } else {
      composeForm.recipients = composeForm.recipients.filter(p => !filteredSubscribers.value.some(s => s.phone === p))
    }
  }
})
function toggleRecipient(phone) {
  if (composeForm.recipients.includes(phone)) {
    composeForm.recipients = composeForm.recipients.filter(p => p !== phone)
  } else {
    composeForm.recipients.push(phone)
  }
}
function removeRecipient(phone) {
  composeForm.recipients = composeForm.recipients.filter(p => p !== phone)
}

</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">SMS Management</h2>
        <PrimaryButton @click="openCompose">Send SMS</PrimaryButton>
      </div>
    </template>

    <!-- Balance -->
    <div class="my-4 flex items-center gap-4">
      <div class="bg-blue-50 border border-blue-200 rounded px-4 py-2 text-blue-800 font-semibold">
        SMS Balance: {{ balance?.balance ?? 0 }}
      </div>
    </div>

    <!-- Compose SMS Modal -->
    <Modal :show="showCompose" @close="showCompose = false">
      <div class="p-6 space-y-4">
        <h3 class="text-lg font-semibold mb-2">Compose SMS</h3>
        <form @submit.prevent="sendSMS" class="space-y-4">
          <div>
            <InputLabel value="Recipients" />
            <div class="mb-2 flex items-center gap-2">
              <input type="text" v-model="recipientSearch" placeholder="Search recipients..." class="border rounded px-2 py-1 w-full" />
            </div>
            <div class="mb-2 flex items-center gap-2">
              <input type="checkbox" :checked="allSelected" @change="e => allSelected = e.target.checked" id="selectAllRecipients" />
              <label for="selectAllRecipients" class="text-sm">Select All</label>
            </div>
            <div class="max-h-40 overflow-y-auto border rounded p-2 bg-gray-50">
              <div v-for="s in filteredSubscribers" :key="s.id" class="flex items-center gap-2 mb-1">
                <input type="checkbox" :id="'recipient-' + s.id" :value="s.phone" :checked="composeForm.recipients.includes(s.phone)" @change="() => toggleRecipient(s.phone)" />
                <label :for="'recipient-' + s.id">{{ s.full_name }} ({{ s.phone }})</label>
              </div>
              <div v-if="!filteredSubscribers.length" class="text-gray-400 text-sm">No recipients found.</div>
            </div>
            <InputError :message="composeForm.errors.recipients" />
            <div v-if="composeForm.recipients.length" class="mt-2 bg-blue-50 border border-blue-200 rounded p-2">
              <div class="font-semibold text-blue-800 mb-1 text-sm">Selected Recipients:</div>
              <div class="flex flex-wrap gap-2">
                <span v-for="phone in composeForm.recipients" :key="phone" class="bg-blue-100 text-blue-800 px-2 py-1 rounded flex items-center gap-1">
                  {{ (props.subscribers.find(s => s.phone === phone)?.full_name || phone) }}
                  <button type="button" @click="() => removeRecipient(phone)" class="ml-1 text-blue-600 hover:text-red-600">&times;</button>
                </span>
              </div>
            </div>
          </div>
          <div>
            <InputLabel value="Template (optional)" />
            <select v-model="composeForm.template_id" class="w-full border rounded">
              <option value="">-- Select Template --</option>
              <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div>
            <InputLabel value="Message" />
            <TextArea v-model="composeForm.message" rows="4" class="w-full" />
            <InputError :message="composeForm.errors.message" />
          </div>
          <div class="flex items-center gap-4">
            <span>Cost: <b>{{ totalCost }}</b> cents</span>
            <span v-if="balance && totalCost > balance.balance" class="text-red-600">Insufficient balance</span>
          </div>
          <div class="flex justify-end gap-2">
            <DangerButton @click="showCompose = false" type="button">Cancel</DangerButton>
            <PrimaryButton :disabled="composeForm.processing || (balance && totalCost > balance.balance)">
              Send
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Template Management -->
    <div class="my-8">
      <div class="flex justify-between items-center mb-2">
        <h3 class="text-lg font-semibold">SMS Templates</h3>
        <PrimaryButton @click="() => openTemplateModal()">Add Template</PrimaryButton>
      </div>
      <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Name</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Content</th>
              <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="t in templates" :key="t.id">
              <td class="px-4 py-2">{{ t.name }}</td>
              <td class="px-4 py-2">{{ t.content }}</td>
              <td class="px-4 py-2 text-right flex gap-2 justify-end">
                <PrimaryButton @click="() => openTemplateModal(t)">Edit</PrimaryButton>
                <DangerButton @click="() => deleteTemplate(t.id)">Delete</DangerButton>
              </td>
            </tr>
            <tr v-if="!(templates && templates.length)">
              <td colspan="3" class="text-center text-gray-500 py-6">No templates found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Template Modal -->
    <Modal :show="showTemplateModal" @close="showTemplateModal = false">
      <div class="p-6 space-y-4">
        <h3 class="text-lg font-semibold mb-2">{{ editingTemplate ? 'Edit' : 'Add' }} Template</h3>
        <form @submit.prevent="saveTemplate" class="space-y-4">
          <div>
            <InputLabel value="Name" />
            <TextInput v-model="templateForm.name" class="w-full" />
            <InputError :message="templateForm.errors.name" />
          </div>
          <div>
            <InputLabel value="Content" />
            <TextArea v-model="templateForm.content" rows="4" class="w-full" />
            <InputError :message="templateForm.errors.content" />
          </div>
          <div class="flex justify-end gap-2">
            <DangerButton @click="showTemplateModal = false" type="button">Cancel</DangerButton>
            <PrimaryButton :disabled="templateForm.processing">Save</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- SMS History -->
    <div class="my-8">
      <h3 class="text-lg font-semibold mb-2">SMS History</h3>
      <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Recipient</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Phone</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Message</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Cost</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Sent At</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="m in messages.data" :key="m.id">
              <td class="px-4 py-2">{{ m.recipient }}</td>
              <td class="px-4 py-2">{{ m.recipient_phone }}</td>
              <td class="px-4 py-2">{{ m.message }}</td>
              <td class="px-4 py-2">
                <span :class="['inline-block px-2 py-1 rounded text-xs font-semibold', statusColors[m.status] || 'bg-gray-100 text-gray-700']">
                  {{ m.status }}
                </span>
              </td>
              <td class="px-4 py-2">{{ m.cost }}</td>
              <td class="px-4 py-2">{{ m.sent_at ? new Date(m.sent_at).toLocaleString() : '—' }}</td>
            </tr>
            <tr v-if="!(messages.data && messages.data.length)">
              <td colspan="6" class="text-center text-gray-500 py-6">No messages found.</td>
            </tr>
          </tbody>
        </table>
        <Pagination class="mt-4" :links="messages.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
