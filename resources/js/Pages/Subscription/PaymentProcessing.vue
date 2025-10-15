<script setup>
import { Head } from '@inertiajs/vue3'
import { 
  CheckCircle, 
  Clock, 
  Calendar,
  DollarSign,
  CreditCard,
  ArrowRight
} from 'lucide-vue-next'

const props = defineProps({
  subscription: Object,
  tenant: Object,
  paymentReference: String,
  amount: Number,
  phone: String,
})

const formatPhone = (phone) => {
  if (!phone) return ''
  return phone.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3')
}
</script>

<template>
  <Head title="Payment Processing" />

  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
          <Clock class="w-8 h-8 text-blue-600 animate-pulse" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Processing</h1>
        <p class="text-gray-600">Please complete the payment on your phone to continue.</p>
      </div>

      <!-- Payment Details Card -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
          <CreditCard class="w-6 h-6 text-blue-600" />
          <h2 class="text-xl font-semibold text-gray-900">Payment Details</h2>
        </div>

        <div class="space-y-4">
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Amount</span>
            <span class="text-xl font-bold text-blue-600">KES {{ amount }}</span>
          </div>
          
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Phone Number</span>
            <span class="font-medium">{{ formatPhone(phone) }}</span>
          </div>
          
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Reference</span>
            <span class="font-mono text-sm">{{ paymentReference }}</span>
          </div>
        </div>
      </div>

      <!-- Instructions Card -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Instructions</h3>
        
        <div class="space-y-4">
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-blue-600">1</span>
            </div>
            <p class="text-gray-700">Check your phone for an M-Pesa prompt</p>
          </div>
          
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-blue-600">2</span>
            </div>
            <p class="text-gray-700">Enter your M-Pesa PIN when prompted</p>
          </div>
          
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-blue-600">3</span>
            </div>
            <p class="text-gray-700">Confirm the payment to complete your subscription renewal</p>
          </div>
        </div>
      </div>

      <!-- Status Card -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
        <div class="flex items-center space-x-3 mb-3">
          <Clock class="w-5 h-5 text-yellow-600" />
          <h3 class="font-medium text-yellow-800">Waiting for Payment</h3>
        </div>
        <p class="text-yellow-700 text-sm">
          Please complete the payment on your phone. This page will automatically update once payment is confirmed.
        </p>
      </div>

      <!-- Auto-refresh notice -->
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
          This page will automatically refresh every 10 seconds to check payment status.
        </p>
      </div>
    </div>
  </div>

  <!-- Auto-refresh script -->
  <script>
    // Auto-refresh every 10 seconds
    setTimeout(() => {
      window.location.reload()
    }, 10000)
  </script>
</template>
