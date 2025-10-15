<template>
  <Head title="Hotspot Settings" />
    <div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 flex items-center gap-2">
        <span>Hotspot Settings</span>
      </h2>
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Captive Portal Template Selection -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <div class="mb-4">
            <InputLabel for="portal_template" value="Captive Portal Template" class="font-semibold text-lg" />
            <select id="portal_template" v-model="form.portal_template" class="w-full mt-2 rounded border-gray-300 dark:bg-gray-800 dark:text-gray-100">
              <option value="default">Default</option>
              <option value="modern-dark">Modern Dark</option>
            </select>
            <InputError class="mt-2" :message="page.props.errors.portal_template" />
            <div v-if="form.portal_template === 'default'" class="mt-2 text-xs text-gray-500">Classic light theme with blue accent, logo, and simple layout.</div>
            <div v-else-if="form.portal_template === 'modern-dark'" class="mt-2 text-xs text-gray-500">Modern dark theme with bold accent, large buttons, and sleek card design.</div>
          </div>

          <div class="mb-4">
            <InputLabel for="logo_url" value="Logo URL" class="font-semibold" />
            <TextInput id="logo_url" v-model="form.logo_url" class="w-full mt-2" placeholder="https://your-logo-url.com/logo.png" />
            <InputError class="mt-2" :message="page.props.errors.logo_url" />
          </div>

        </div>
        <!-- User Prefix for Hotspot Users -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <InputLabel for="user_prefix" value="User Prefix for New Hotspot Users" class="font-semibold text-lg" />
          <TextInput id="user_prefix" v-model="form.user_prefix" class="w-full mt-2" placeholder="e.g. WIFI-, HS-, NET-" />
          <InputError class="mt-2" :message="page.props.errors.user_prefix" />
        </div>
        <!-- Pruning Inactive Hotspot Users -->
        <div class="p-4 border dark:border-gray-700 rounded-lg">
          <InputLabel for="prune_inactive_days" value="Prune Inactive Users After (days)" class="font-semibold text-lg" />
          <TextInput id="prune_inactive_days" type="number" min="1" step="1" v-model="form.prune_inactive_days" class="w-full mt-2" placeholder="e.g. 30" />
          <InputError class="mt-2" :message="page.props.errors.prune_inactive_days" />
          <div class="mt-2 text-xs text-gray-500">Inactive hotspot users will be automatically deleted after this many days of no activity. Leave blank to disable pruning.</div>
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

<script setup>
import { ref, watch } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextArea from '@/Components/TextArea.vue'

const page = usePage()
const defaultForm = {
  portal_template: 'default',
  logo_url: '',
  user_prefix: '',
  prune_inactive_days: ''
}
const form = ref(page.props.settings ? { ...defaultForm, ...page.props.settings } : { ...defaultForm })
const success = ref(page.props.flash?.success || '')
const loading = ref(false)

function submit() {
  loading.value = true
  success.value = ''
  router.post(route('tenants.settings.hotspot.update'), form.value, {
    onSuccess: (page) => {
      success.value = page.props.flash.success || 'Settings updated successfully.'
      // Only update form if backend returns new settings
      if (page.props.settings) {
        Object.assign(form.value, { ...form.value, ...page.props.settings })
      }
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

watch(() => page.props.settings, (val) => {
  if (val) {
    Object.assign(form.value, { ...defaultForm, ...val })
  }
})
</script>
