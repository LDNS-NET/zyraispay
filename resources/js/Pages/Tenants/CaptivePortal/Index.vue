<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const tenant = ref({ business_name: 'Loading...', phone: '' })
const activeTab = ref(null)
const loginForm = ref({ username: '', password: '' })
const voucherCode = ref('')
const packages = ref([])
const phoneForPayment = ref('')
const selectedPackage = ref(null)
const showPaymentModal = ref(false)
const paymentLoading = ref(false)
const paymentError = ref('')
const paymentSuccess = ref('')
const generatedCredentials = ref(null)

const toggleTab = (tab) => {
  activeTab.value = activeTab.value === tab ? null : tab
  generatedCredentials.value = null
}

const fetchTenantDetails = async () => {
  try {
    const { data } = await axios.get('/captive-portal/tenant')
    tenant.value = data
  } catch (e) {
    console.error('Failed to fetch tenant', e)
  }
}

const fetchPackages = async () => {
  try {
    const { data } = await axios.get('/hotspot/packages')
    packages.value = data.packages
  } catch (e) {
    console.error('Failed to fetch packages', e)
  }
}

const loginUser = async () => {
  try {
    const { data } = await axios.post('/api/captive-portal/login', loginForm.value)
    if (data.success && data.user) {
      generatedCredentials.value = {
        username: data.user.username,
        password: loginForm.value.password
      }
    } else {
      alert('Invalid credentials')
    }
  } catch {
    alert('Invalid credentials')
  }
}

const submitVoucher = async () => {
  try {
    const { data } = await axios.post('/api/captive-portal/voucher', { voucher_code: voucherCode.value })
    if (data.success) {
      generatedCredentials.value = {
        username: data.user.username,
        password: data.user.password
      }
      alert('You are now connected!')
    } else {
      alert(data.message || 'Invalid voucher')
    }
  } catch {
    alert('Invalid voucher code')
  }
}


const openPaymentModal = (pkg) => {
  selectedPackage.value = pkg
  paymentError.value = ''
  paymentSuccess.value = ''
  generatedCredentials.value = null
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  phoneForPayment.value = ''
}

const buyPackage = async () => {
  paymentError.value = ''
  paymentSuccess.value = ''
  generatedCredentials.value = null
  if (!phoneForPayment.value) {
    paymentError.value = 'Please enter your phone number (07xxxxxxxx or 01xxxxxxxx).'
    return
  }
  paymentLoading.value = true
  try {
    const { data } = await axios.post('/hotspot/pay', {
      package_id: selectedPackage.value.id,
      phone: phoneForPayment.value,
    })
    if (data.success) {
      paymentSuccess.value = data.message || 'STK Push sent. Complete payment on your phone.'
      if (data.credentials) {
        generatedCredentials.value = data.credentials
      }
    } else {
      paymentError.value = data.message || 'Payment failed.'
    }
  } catch (e) {
    paymentError.value = e.response?.data?.message || 'Payment error.'
  } finally {
    paymentLoading.value = false
  }
}

const formatPrice = (price) => `Ksh${price}`

const formatDuration = (pkg) => {
  if (!pkg.duration_value || !pkg.duration_unit) return 'N/A'
  const plural = pkg.duration_value > 1 ? 's' : ''
  return `${pkg.duration_value} ${pkg.duration_unit}${plural}`
}

onMounted(() => {
  fetchTenantDetails()
  fetchPackages()
})
</script>

<template>
  <div class="min-h-screen bg-gray-900 text-gray-100 flex flex-col items-center justify-center p-4">
    <!-- Business Header -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold">{{ tenant.business_name }}</h1>
      <p class="text-gray-400">Support number: {{ tenant.phone }}</p>
    </div>

    <!-- Login / Voucher Toggle -->
    <div class="flex space-x-4 mb-6">
      <button
        @click="toggleTab('login')"
        :class="['px-4 py-2 rounded-lg font-medium', activeTab === 'login' ? 'bg-indigo-600' : 'bg-gray-700 hover:bg-gray-600']"
      >
        Login
      </button>
      <button
        @click="toggleTab('voucher')"
        :class="['px-4 py-2 rounded-lg font-medium', activeTab === 'voucher' ? 'bg-indigo-600' : 'bg-gray-700 hover:bg-gray-600']"
      >
        Voucher
      </button>
    </div>

    <!-- Login Form -->
    <div v-if="activeTab === 'login'" class="w-full max-w-md bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Login</h2>
      <input
        v-model="loginForm.username"
        type="text"
        placeholder="Username"
        class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 mb-3"
      />
      <input
        v-model="loginForm.password"
        type="password"
        placeholder="Password"
        class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 mb-4"
      />
      <button
        @click="loginUser"
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium"
      >
        Log In
      </button>

      <div v-if="generatedCredentials" class="bg-gray-700 p-3 rounded-lg mt-4 text-left">
        <p class="font-bold">Your Wi-Fi Access</p>
        <p>Username: {{ generatedCredentials.username }}</p>
        <p>Password: {{ generatedCredentials.password }}</p>
      </div>
    </div>

    <!-- Voucher Form -->
    <div v-if="activeTab === 'voucher'" class="w-full max-w-md bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Redeem Voucher</h2>
      <input
        v-model="voucherCode"
        type="text"
        placeholder="Enter voucher code"
        class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 mb-4"
      />
      <button
        @click="submitVoucher"
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium"
      >
        Redeem
      </button>

      <div v-if="generatedCredentials" class="bg-gray-700 p-3 rounded-lg mt-4 text-left">
        <p class="font-bold">Your Wi-Fi Access</p>
        <p>Username: {{ generatedCredentials.username }}</p>
        <p>Password: {{ generatedCredentials.password }}</p>
      </div>
    </div>

    <!-- Packages -->
    <div class="w-full max-w-4xl bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
      <h3 class="text-lg font-semibold text-blue-600 mb-6 text-center">Available Packages</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
        <div
          v-for="pkg in packages"
          :key="pkg.id"
          class="bg-gray-700 rounded-xl shadow-lg p-6 flex flex-col justify-between text-center w-full max-w-xs"
        >
          <div>
            <h3 class="text-lg font-semibold">{{ pkg.name }}</h3>
            <p class="text-gray-400 text-sm mb-1">{{ formatDuration(pkg) }}</p>
            <p class="text-gray-400 text-sm mb-2">
              {{ pkg.download_speed }} Mbps ↓ / {{ pkg.upload_speed }} Mbps ↑
            </p>
            <p class="text-2xl font-bold mb-4">{{ formatPrice(pkg.price) }}</p>
          </div>
          <button
            @click="openPaymentModal(pkg)"
            class="bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium"
          >
            Buy
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
      <div class="bg-gray-800 p-6 rounded-xl shadow-xl w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Buy Package</h2>
        <p class="mb-2">Selected: <span class="font-bold">{{ selectedPackage?.name }}</span></p>
        <p class="mb-2">Duration: <span class="font-bold">{{ formatDuration(selectedPackage) }}</span></p>
        <p class="mb-4">Price: <span class="font-bold">{{ formatPrice(selectedPackage?.price) }}</span></p>

        <input
          v-model="phoneForPayment"
          type="text"
          placeholder="Enter phone number (07xxxxxxxx)"
          class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 mb-3"
        />

        <div v-if="paymentError" class="text-red-400 mb-2">{{ paymentError }}</div>
        <div v-if="paymentSuccess" class="text-green-400 mb-2">{{ paymentSuccess }}</div>

        <div v-if="generatedCredentials" class="bg-gray-700 p-3 rounded-lg mt-3 text-left">
          <p class="font-bold">Your Wi-Fi Access</p>
          <p>Username: {{ generatedCredentials.username }}</p>
          <p>Password: {{ generatedCredentials.password }}</p>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button @click="closePaymentModal" class="px-4 py-2 bg-gray-600 rounded-lg hover:bg-gray-500">Cancel</button>
          <button
            @click="buyPackage"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-medium"
            :disabled="paymentLoading"
          >
            <span v-if="paymentLoading">Processing...</span>
            <span v-else>Pay</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
