<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { 
  CreditCard, 
  Clock, 
  AlertTriangle, 
  CheckCircle, 
  Calendar,
  DollarSign,
  Phone,
  Shield
} from 'lucide-vue-next'

const props = defineProps({
  subscription: Object,
  tenant: Object,
  trialDaysRemaining: Number,
  currentPeriodDaysRemaining: Number,
})

const form = useForm({
  phone: '',
  amount: props.subscription?.amount || 5000,
})

const isProcessing = ref(false)

const statusColor = computed(() => {
  if (props.subscription?.isOnTrial()) return 'text-blue-600'
  if (props.subscription?.isActive()) return 'text-green-600'
  if (props.subscription?.isExpired()) return 'text-red-600'
  return 'text-gray-600'
})

const statusText = computed(() => {
  if (props.subscription?.isOnTrial()) return 'Trial Period'
  if (props.subscription?.isActive()) return 'Active Subscription'
  if (props.subscription?.isExpired()) return 'Subscription Expired'
  return 'Unknown Status'
})

const submitPayment = () => {
  form.post(route('subscription.payment.process'), {
    onStart: () => isProcessing.value = true,
    onFinish: () => isProcessing.value = false,
  })
}
</script>

<template>
  <Head title="Subscription Expired" />

  <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
          <AlertTriangle class="w-8 h-8 text-red-600" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Subscription Expired</h1>
        <p class="text-gray-600">Your subscription has expired. Please renew to continue using our services.</p>
      </div>

      <!-- Subscription Status Card -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900">Current Status</h2>
          <span :class="['px-3 py-1 rounded-full text-sm font-medium', statusColor]">
            {{ statusText }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center space-x-3">
            <Calendar class="w-5 h-5 text-gray-400" />
            <div>
              <p class="text-sm text-gray-500">Trial Period</p>
              <p class="font-medium">{{ trialDaysRemaining }} days remaining</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <Clock class="w-5 h-5 text-gray-400" />
            <div>
              <p class="text-sm text-gray-500">Current Period</p>
              <p class="font-medium">{{ currentPeriodDaysRemaining }} days remaining</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Form -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center space-x-3 mb-6">
          <CreditCard class="w-6 h-6 text-blue-600" />
          <h2 class="text-xl font-semibold text-gray-900">Renew Subscription</h2>
        </div>

        <form @submit.prevent="submitPayment" class="space-y-6">
          <!-- Amount Display -->
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <DollarSign class="w-5 h-5 text-blue-600" />
                <span class="text-sm text-gray-600">Monthly Subscription Fee</span>
              </div>
              <span class="text-2xl font-bold text-blue-600">KES {{ form.amount }}</span>
            </div>
          </div>

          <!-- Phone Number Input -->
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
              <Phone class="w-4 h-4 inline mr-1" />
              Phone Number (M-Pesa)
            </label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              placeholder="254XXXXXXXXX"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Enter your M-Pesa registered phone number</p>
            <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
              {{ form.errors.phone }}
            </div>
          </div>

          <!-- Payment Method Info -->
          <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <Shield class="w-5 h-5 text-green-600" />
              <div>
                <h3 class="font-medium text-green-800">Secure Payment</h3>
                <p class="text-sm text-green-600">Your payment will be processed securely through M-Pesa</p>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isProcessing || form.processing"
            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="isProcessing || form.processing" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing Payment...
            </span>
            <span v-else>Pay KES {{ form.amount }} via M-Pesa</span>
          </button>
        </form>

        <!-- Help Text -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-500">
            Need help? Contact support at 
            <a href="mailto:support@zyraispay.com" class="text-blue-600 hover:underline">
              support@zyraispay.com
            </a>
          </p>
        </div>
      </div>

      <!-- Features Reminder -->
      <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">What you'll get with your subscription:</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">Full access to dashboard</span>
          </div>
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">Customer management</span>
          </div>
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">Payment processing</span>
          </div>
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">SMS notifications</span>
          </div>
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">Router management</span>
          </div>
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">24/7 support</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
