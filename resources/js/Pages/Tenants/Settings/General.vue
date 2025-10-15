<script setup>
import { useToast } from 'vue-toastification'
const toast = useToast()
import { ref, onMounted } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'
const { setTheme, setPrimaryColor } = useTheme()
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Building2, Mail, Phone, MapPin, Globe, Clock } from 'lucide-vue-next'

const page = usePage()
const form = ref({
  business_name: '',
  business_type: 'isp',
  logo: '',
  theme: 'system',
  support_email: '',
  support_phone: '',
  whatsapp: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  website: '',
  facebook: '',
  twitter: '',
  instagram: '',
  business_hours: 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 4:00 PM\nSunday: Closed',
  timezone: 'Africa/Nairobi',
  currency: 'KES',
  language: 'en',
});

function setFormFromBackend(settings) {
  form.value = {
    business_name: typeof settings.business_name === 'number' ? settings.business_name : (settings.business_name ?? ''),
    business_type: typeof settings.business_type === 'number' ? settings.business_type : (settings.business_type ?? 'isp'),
    logo: typeof settings.logo === 'number' ? settings.logo : (settings.logo ?? ''),
    theme: typeof settings.theme === 'number' ? settings.theme : (settings.theme ?? 'system'),
    support_email: typeof settings.support_email === 'number' ? settings.support_email : (settings.support_email ?? ''),
    support_phone: typeof settings.support_phone === 'number' ? settings.support_phone : (settings.support_phone ?? ''),
    whatsapp: typeof settings.whatsapp === 'number' ? settings.whatsapp : (settings.whatsapp ?? ''),
    address: typeof settings.address === 'number' ? settings.address : (settings.address ?? ''),
    city: typeof settings.city === 'number' ? settings.city : (settings.city ?? ''),
    state: typeof settings.state === 'number' ? settings.state : (settings.state ?? ''),
    postal_code: typeof settings.postal_code === 'number' ? settings.postal_code : (settings.postal_code ?? ''),
    country: typeof settings.country === 'number' ? settings.country : (settings.country ?? 'Kenya'),
    website: typeof settings.website === 'number' ? settings.website : (settings.website ?? ''),
    facebook: typeof settings.facebook === 'number' ? settings.facebook : (settings.facebook ?? ''),
    twitter: typeof settings.twitter === 'number' ? settings.twitter : (settings.twitter ?? ''),
    instagram: typeof settings.instagram === 'number' ? settings.instagram : (settings.instagram ?? ''),
    business_hours: typeof settings.business_hours === 'number' ? settings.business_hours : (settings.business_hours ?? 'Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 4:00 PM\nSunday: Closed'),
    timezone: typeof settings.timezone === 'number' ? settings.timezone : (settings.timezone ?? 'Africa/Nairobi'),
    currency: typeof settings.currency === 'number' ? settings.currency : (settings.currency ?? 'KES'),
    language: typeof settings.language === 'number' ? settings.language : (settings.language ?? 'en'),
  }
}

onMounted(() => {
  setFormFromBackend(page.props.settings || {})
  if (form.value.theme) setTheme(form.value.theme)
})

const logoFile = ref(null)
const logoPreview = ref('')

onMounted(() => {
  // Sync global theme/color from loaded settings
  if (form.value.theme) setTheme(form.value.theme)
  // if (form.value.primary_color) setPrimaryColor(form.value.primary_color)
})

function onLogoChange(e) {
  const file = e.target.files[0]
  if (file) {
    logoFile.value = file
    const reader = new FileReader()
    reader.onload = ev => {
      logoPreview.value = ev.target.result
    }
    reader.readAsDataURL(file)
  }
}
function removeLogo() {
  logoFile.value = null
  logoPreview.value = ''
  form.value.logo = ''
  form.value.remove_logo = true
}

const success = ref(page.props.flash?.success || '')
const error = ref('')
const loading = ref(false)

function submit() {
  loading.value = true
  error.value = ''
  success.value = ''

  const formData = new FormData()
  for (const [key, value] of Object.entries(form.value)) {
    if (typeof value !== 'undefined' && value !== null) {
      formData.append(key, value)
    }
  }
  if (logoFile.value) {
    formData.append('logo', logoFile.value)
  }

  router.post(route('tenants.settings.general.update'), formData, {
    forceFormData: true,
    onSuccess: (page) => {
      loading.value = false
      logoFile.value = null
      logoPreview.value = ''
      if (page?.props?.settings) {
        setFormFromBackend(page.props.settings)
        if (form.value.theme) setTheme(form.value.theme)
      }
      toast.success('General settings updated successfully.')
    },
    onError: (errs) => {
      error.value = Object.values(errs).flat().join(' ')
      loading.value = false
      toast.error(error.value)
    },
    onFinish: () => {
      loading.value = false
    }
  })
}
</script>

<template>
  <Head title="General Settings" />
  
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-sm">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <Building2 class="w-6 h-6 text-blue-600" />
        General Settings
      </h2>
      <p class="text-gray-600 mt-1">Manage your business information, contact details, and general preferences.</p>
    </div>

  <!-- Success/Error Messages removed, Toastify will handle notifications -->

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Business Information Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <Building2 class="w-5 h-5 text-blue-600" />
          Business Information
        </h3>

        <!-- Business Logo Upload -->
        <div class="mb-6">
          <InputLabel for="logo" value="Business Logo" />
          <div class="flex items-center gap-6 mt-2">
            <div v-if="form.logo || logoPreview" class="relative">
              <img :src="logoPreview || form.logo" alt="Logo Preview" class="w-20 h-20 object-contain rounded shadow border" />
              <button type="button" @click="removeLogo" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow">&times;</button>
            </div>
            <input type="file" id="logo" accept="image/*" @change="onLogoChange" class="block" />
          </div>
          <p class="text-xs text-gray-500 mt-1">Upload your business logo (max 2MB, PNG/JPG/GIF/SVG).</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
          </div>
          
          <!-- Business Description section removed -->
          
          <div>
            <InputLabel for="business_type" value="Business Type" />
            <select 
              id="business_type" 
              v-model="form.business_type" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="isp">Internet Service Provider</option>
              <option value="wisp">Wireless Internet Service Provider</option>
              <option value="telecom">Telecommunications</option>
              <option value="other">Other</option>
            </select>
          </div>
          
          <!-- Business Registration Number and Tax/VAT Number sections removed -->
        </div>

        <!-- Theme and Color Customization -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <InputLabel for="theme" value="Theme" />
            <select id="theme" v-model="form.theme" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="system">System Default</option>
              <option value="light">Light</option>
              <option value="dark">Dark</option>
            </select>
          </div>
          <!-- Primary Color section removed -->
        </div>
      </div>

      <!-- Contact Information Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <Phone class="w-5 h-5 text-green-600" />
          Contact Information
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <InputLabel for="support_email" value="Support Email" />
            <TextInput 
              id="support_email" 
              v-model="form.support_email" 
              type="email"
              class="mt-1 block w-full" 
              placeholder="support@yourisp.com"
            />
          </div>
          <div>
            <InputLabel for="support_phone" value="Support Phone" />
            <TextInput 
              id="support_phone" 
              v-model="form.support_phone" 
              class="mt-1 block w-full" 
              placeholder="+254 700 000 001"
            />
          </div>
          <div>
            <InputLabel for="whatsapp" value="WhatsApp Number" />
            <TextInput 
              id="whatsapp" 
              v-model="form.whatsapp" 
              class="mt-1 block w-full" 
              placeholder="+254 700 000 000"
            />
          </div>
        </div>
      </div>

      <!-- Address Information Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <MapPin class="w-5 h-5 text-red-600" />
          Business Address
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <InputLabel for="address" value="Street Address" />
            <TextInput 
              id="address" 
              v-model="form.address" 
              class="mt-1 block w-full" 
              placeholder="123 Main Street, Building A"
            />
          </div>
          
          <div>
            <InputLabel for="city" value="City" />
            <TextInput 
              id="city" 
              v-model="form.city" 
              class="mt-1 block w-full" 
              placeholder="Nairobi"
            />
          </div>
          
          <div>
            <InputLabel for="state" value="State/Region" />
            <TextInput 
              id="state" 
              v-model="form.state" 
              class="mt-1 block w-full" 
              placeholder="Nairobi County"
            />
          </div>
          
          <div>
            <InputLabel for="postal_code" value="Postal Code" />
            <TextInput 
              id="postal_code" 
              v-model="form.postal_code" 
              class="mt-1 block w-full" 
              placeholder="00100"
            />
          </div>
          
          <div>
            <InputLabel for="country" value="Country" />
            <select 
              id="country" 
              v-model="form.country" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="Kenya">Kenya</option>
              <option value="South Sudan">South Sudan</option>
              <option value="Uganda">Uganda</option>
              <option value="Tanzania">Tanzania</option>
              <option value="Rwanda">Rwanda</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Online Presence Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <Globe class="w-5 h-5 text-purple-600" />
          Online Presence
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <InputLabel for="website" value="Website URL" />
            <TextInput 
              id="website" 
              v-model="form.website" 
              type="url"
              class="mt-1 block w-full" 
              placeholder="https://www.yourisp.com"
            />
          </div>
          
          <div>
            <InputLabel for="facebook" value="Facebook Page" />
            <TextInput 
              id="facebook" 
              v-model="form.facebook" 
              type="url"
              class="mt-1 block w-full" 
              placeholder="https://facebook.com/yourisp"
            />
          </div>
          
          <div>
            <InputLabel for="twitter" value="Twitter/X Profile" />
            <TextInput 
              id="twitter" 
              v-model="form.twitter" 
              type="url"
              class="mt-1 block w-full" 
              placeholder="https://twitter.com/yourisp"
            />
          </div>
          
          <div>
            <InputLabel for="instagram" value="Instagram Profile" />
            <TextInput 
              id="instagram" 
              v-model="form.instagram" 
              type="url"
              class="mt-1 block w-full" 
              placeholder="https://instagram.com/yourisp"
            />
          </div>
        </div>
      </div>

      <!-- Business Hours & Preferences Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <Clock class="w-5 h-5 text-orange-600" />
          Business Hours & Preferences
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-3">
            <InputLabel for="business_hours" value="Business Hours" />
            <TextArea 
              id="business_hours" 
              v-model="form.business_hours" 
              class="mt-1 block w-full" 
              rows="4"
              placeholder="Monday - Friday: 8:00 AM - 6:00 PM&#10;Saturday: 9:00 AM - 4:00 PM&#10;Sunday: Closed"
            />
          </div>
          
          <div>
            <InputLabel for="timezone" value="Timezone" />
            <select 
              id="timezone" 
              v-model="form.timezone" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="Africa/Nairobi">Africa/Nairobi / SSP (EAT)</option>
              <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
              <option value="Africa/Dar_es_Salaam">Africa/Dar_es_Salaam (EAT)</option>
              <option value="Africa/Kigali">Africa/Kigali (CAT)</option>
            </select>
          </div>
          
          <div>
            <InputLabel for="currency" value="Currency" />
            <select 
              id="currency" 
              v-model="form.currency" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="KES">Kenyan Shilling (KES)</option>
              <option value="SSP">South Sudan Pounds (SSP)</option>
              <option value="UGX">Ugandan Shilling (UGX)</option>
              <option value="TZS">Tanzanian Shilling (TZS)</option>
              <option value="RWF">Rwandan Franc (RWF)</option>
              <option value="USD">US Dollar (USD)</option>
            </select>
          </div>
          
          <div>
            <InputLabel for="language" value="Language" />
            <select 
              id="language" 
              v-model="form.language" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="en">English</option>
              <option value="sw">Swahili</option>
              <option value="fr">French</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end pt-6 border-t border-gray-200">
        <PrimaryButton 
          type="submit" 
          :disabled="loading"
          class="px-8 py-2 flex items-center gap-2"
        >
          <span v-if="loading">Saving...</span>
          <span v-else>Save General Settings</span>
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
