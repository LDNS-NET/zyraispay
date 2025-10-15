<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { 
  CheckCircle, 
  Calendar,
  DollarSign,
  ArrowRight,
  Shield
} from 'lucide-vue-next'

const props = defineProps({
  subscription: Object,
  tenant: Object,
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

<template>
  <Head title="Payment Successful" />

  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
      <!-- Success Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
          <CheckCircle class="w-8 h-8 text-green-600" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
        <p class="text-gray-600">Your subscription has been renewed successfully.</p>
      </div>

      <!-- Success Details Card -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
          <Shield class="w-6 h-6 text-green-600" />
          <h2 class="text-xl font-semibold text-gray-900">Subscription Details</h2>
        </div>

        <div class="space-y-4">
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Status</span>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
              Active
            </span>
          </div>
          
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Amount Paid</span>
            <span class="text-xl font-bold text-green-600">KES {{ subscription?.amount }}</span>
          </div>
          
          <div class="flex justify-between items-center py-3 border-b border-gray-100">
            <span class="text-gray-600">Next Billing Date</span>
            <span class="font-medium">{{ formatDate(subscription?.next_billing_date) }}</span>
          </div>
          
          <div class="flex justify-between items-center py-3">
            <span class="text-gray-600">Period Ends</span>
            <span class="font-medium">{{ formatDate(subscription?.current_period_ends_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Features Card -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">You now have access to:</h3>
        
        <div class="grid grid-cols-1 gap-3">
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-green-500" />
            <span class="text-gray-700">Full dashboard access</span>
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

      <!-- Action Button -->
      <div class="text-center">
        <Link
          :href="route('tenants.dashboard.index')"
          class="inline-flex items-center space-x-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        >
          <span>Go to Dashboard</span>
          <ArrowRight class="w-5 h-5" />
        </Link>
      </div>

      <!-- Support Info -->
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
          Need help? Contact support at 
          <a href="mailto:support@zyraispay.com" class="text-blue-600 hover:underline">
            support@zyraispay.com
          </a>
        </p>
      </div>
    </div>
  </div>
</template>
