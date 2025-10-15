<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  users: { type: Array, default: () => [] }, // WiFi users for this tenant
  gateways: { type: Object, default: () => ({ sms: [], whatsapp: [] }) },
})

const selectedUsers = ref([])
const gateway = ref('sms')
const form = useForm({
  message: '',
  gateway: 'sms',
  user_ids: [],
})

const send = () => {
  form.gateway = gateway.value
  form.user_ids = selectedUsers.value
  form.post(route('tenants.send_message'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h3 class="text-xl font-bold mb-4">Send Message to WiFi Users</h3>
    <form @submit.prevent="send">
      <div class="mb-4">
        <label class="block font-medium mb-1">Select Recipients</label>
        <select v-model="selectedUsers" multiple class="input input-bordered w-full h-32">
          <option v-for="u in props.users" :key="u.id" :value="u.id">{{ u.name || u.username || u.phone_number }}</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Gateway</label>
        <select v-model="gateway" class="input input-bordered w-full">
          <option value="sms" :disabled="!props.gateways.sms.length">SMS</option>
          <option value="whatsapp" :disabled="!props.gateways.whatsapp.length">WhatsApp</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Message</label>
        <textarea v-model="form.message" class="input input-bordered w-full h-32" placeholder="Type your message..."></textarea>
      </div>
      <button class="btn btn-primary" type="submit">Send Message</button>
    </form>
  </div>
</template>
