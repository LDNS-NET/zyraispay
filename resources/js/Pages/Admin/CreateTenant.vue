<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Accept optional lead from backend to prefill
const props = defineProps({
  lead: {
    type: Object,
    default: null,
  },
});

const showPassword = ref(false);

// Set initial form values, optionally using lead data
const form = useForm({
  name: props.lead?.isp_name ?? '',
  domain: '',
  email: props.lead?.email ?? '',
  phone: props.lead?.phone ?? '',
  password: '',
});

// Auto-generate domain from name (only if not editing from lead)
watch(() => form.name, (newVal) => {
  if (!props.lead) {
    form.domain = newVal
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9-]/g, '');
  }
});

const submit = () => {
  form.post('/admin/tenants');
};
</script>

<template>
  <SuperAdminLayout>
    <Head title="Create ISP Tenant" />

    <section class="min-h-screen flex items-center justify-center px-4 py-4">
      <div class="w-full max-w-md mx-md-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 space-y-3">
        <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-white">
          Create New
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FF2D20] to-orange-500">ZYRAISPAY</span> Tenant
        </h2>
        <p class="text-center text-sm text-gray-900 dark:text-gray-300">
          Fill in ISP details
        </p>

        <form @submit.prevent="submit" class="space-y-5">
          <!-- ISP Name -->
          <div>
            <InputLabel for="name" value="ISP Name" />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="mt-2 w-full"
              required
              placeholder="MyISP Ltd"
            />
            <InputError class="mt-1" :message="form.errors.name" />
          </div>

          <!-- Subdomain -->
          <div>
            <InputLabel for="domain" value="Subdomain" />
            <TextInput
              id="domain"
              v-model="form.domain"
              type="text"
              class="mt-2 w-full"
              required
              placeholder="myisp"
            />
            <p class="mt-1 text-sm text-gray-600 italic dark:text-gray-400">
              Will be:
              <span class="font-mono text-fuchsia-700">{{ form.domain || 'yourisp' }}.zyraispay.test</span>
            </p>
            <InputError class="mt-1" :message="form.errors.domain" />
          </div>

          <!-- Admin Email -->
          <div>
            <InputLabel for="email" value="Admin Email" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="mt-2 w-full"
              required
              placeholder="admin@myisp.com"
            />
            <InputError class="mt-1" :message="form.errors.email" />
          </div>

          <!-- Admin Phone -->
          <div>
            <InputLabel for="phone" value="Admin Phone" />
            <TextInput
              id="phone"
              v-model="form.phone"
              type="text"
              class="mt-2 w-full"
              required
              placeholder="+254 123 456 789"
            />
            <InputError class="mt-1" :message="form.errors.phone" />
          </div>

          <!-- Admin Password -->
          <div>
            <InputLabel for="password" value="Admin Password" />
            <div class="relative">
              <TextInput
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="form.password"
                class="mt-2 w-full pr-10"
                required
                placeholder="••••••••"
              />
              <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
                @click="showPassword = !showPassword"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.974 9.974 0 013.73-4.59m3.772-1.401a10.05 10.05 0 015.448 0m3.772 1.4A9.974 9.974 0 0121.542 12c-.297.949-.737 1.833-1.294 2.628M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3l18 18"/>
                </svg>
              </button>
            </div>
            <InputError class="mt-1" :message="form.errors.password" />
          </div>

          <!-- Submit -->
          <div class="pt-4">
            <PrimaryButton
              class="w-md  bg-gradient-to-r from-fuchsia-600 to-blue-700 hover:from-fuchsia-700 hover:to-blue-800 text-white"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              🎉 Create ISP Tenant
            </PrimaryButton>
          </div>
        </form>
      </div>
    </section>
  </SuperAdminLayout>
</template>
