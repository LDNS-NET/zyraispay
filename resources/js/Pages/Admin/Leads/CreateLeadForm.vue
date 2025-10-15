<script setup>
import { useForm } from '@inertiajs/vue3'

const emit = defineEmits(['close'])

const form = useForm({
  isp_name: '',
  email: '',
  phone: '',
})

const submit = () => {
  form.post(route('leads.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('close')
    },
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <input v-model="form.isp_name" placeholder="ISP Name" class="w-full border p-2 rounded" />
    <input v-model="form.email" type="email" placeholder="Email" class="w-full border p-2 rounded" />
    <input v-model="form.phone" placeholder="Phone" class="w-full border p-2 rounded" />
    <div class="flex justify-between">
      <button class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">
        Create
      </button>
      <button @click.prevent="$emit('close')" class="text-red-600">Cancel</button>
    </div>
  </form>
</template>
