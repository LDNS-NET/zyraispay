<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'
import Card from '@/Components/Card.vue'
import ChartCard from '@/Components/ChartCard.vue'
import {
  Users,
  DollarSign,
  TrendingUp,
  AlertTriangle,
  CheckCircle,
  Clock,
  Calendar,
  RefreshCw,
  Send,
  BarChart3,
  PieChart,
  Activity
} from 'lucide-vue-next'

const props = defineProps({
  analytics: Object,
  monthlyRevenue: Number,
  conversionRate: Number,
  endingSoon: Array,
  recentRenewals: Array,
  subscriptions: Array,
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusColor = (status) => {
  switch (status) {
    case 'trial': return 'text-blue-600 bg-blue-100'
    case 'active': return 'text-green-600 bg-green-100'
    case 'expired': return 'text-red-600 bg-red-100'
    case 'past_due': return 'text-yellow-600 bg-yellow-100'
    default: return 'text-gray-600 bg-gray-100'
  }
}

const getStatusIcon = (status) => {
  switch (status) {
    case 'trial': return Clock
    case 'active': return CheckCircle
    case 'expired': return AlertTriangle
    case 'past_due': return AlertTriangle
    default: return Activity
  }
}
</script>

<template>
  <SuperAdminLayout>
    <Head title="Subscription Analytics" />

    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold mb-2">Subscription Analytics</h1>
            <p class="text-purple-100">Monitor and manage all tenant subscriptions</p>
          </div>
          <BarChart3 class="w-16 h-16 text-white/70" />
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card 
          title="Total Subscriptions" 
          :value="analytics.total" 
          :icon="Users" 
          class="bg-gradient-to-r from-blue-50 to-blue-100"
        />
        <Card 
          title="Active Subscriptions" 
          :value="analytics.active" 
          :icon="CheckCircle" 
          class="bg-gradient-to-r from-green-50 to-green-100"
        />
        <Card 
          title="Monthly Revenue" 
          :value="formatCurrency(monthlyRevenue)" 
          :icon="DollarSign" 
          class="bg-gradient-to-r from-yellow-50 to-yellow-100"
        />
        <Card 
          title="Conversion Rate" 
          :value="`${conversionRate}%`" 
          :icon="TrendingUp" 
          class="bg-gradient-to-r from-purple-50 to-purple-100"
        />
      </div>

      <!-- Status Distribution Chart -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <PieChart class="w-5 h-5 mr-2 text-blue-600" />
          Subscription Status Distribution
        </h2>
        <ChartCard
          title=""
          :labels="['Active', 'Trial', 'Expired', 'Past Due']"
          :values="[analytics.active, analytics.trial, analytics.expired, 0]"
          type="donut"
          :icon="PieChart"
          class="h-64"
        />
      </div>

      <!-- Subscriptions Ending Soon -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 flex items-center">
            <AlertTriangle class="w-5 h-5 mr-2 text-yellow-600" />
            Subscriptions Ending Soon (7 days)
          </h2>
          <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
            {{ endingSoon.length }} subscriptions
          </span>
        </div>
        
        <div v-if="endingSoon.length > 0" class="space-y-3">
          <div v-for="subscription in endingSoon" :key="subscription.id" 
               class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200">
            <div class="flex items-center space-x-3">
              <Clock class="w-5 h-5 text-yellow-600" />
              <div>
                <p class="font-medium text-gray-900">{{ subscription.tenant.business_name }}</p>
                <p class="text-sm text-gray-600">{{ subscription.tenant.email }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-yellow-800">
                {{ subscription.current_period_days_remaining }} days left
              </p>
              <p class="text-xs text-gray-500">
                Ends: {{ formatDate(subscription.current_period_ends_at) }}
              </p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <CheckCircle class="w-12 h-12 mx-auto mb-2 text-green-500" />
          <p>No subscriptions ending soon</p>
        </div>
      </div>

      <!-- Recent Renewals -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <RefreshCw class="w-5 h-5 mr-2 text-green-600" />
          Recent Renewals (Last 30 days)
        </h2>
        
        <div v-if="recentRenewals.length > 0" class="space-y-3">
          <div v-for="renewal in recentRenewals" :key="renewal.id" 
               class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center space-x-3">
              <CheckCircle class="w-5 h-5 text-green-600" />
              <div>
                <p class="font-medium text-gray-900">{{ renewal.tenant.business_name }}</p>
                <p class="text-sm text-gray-600">{{ renewal.tenant.email }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-green-800">
                {{ formatCurrency(renewal.amount) }}
              </p>
              <p class="text-xs text-gray-500">
                {{ formatDate(renewal.last_payment_at) }}
              </p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <Activity class="w-12 h-12 mx-auto mb-2 text-gray-400" />
          <p>No recent renewals</p>
        </div>
      </div>

      <!-- All Subscriptions Table -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <Users class="w-5 h-5 mr-2 text-blue-600" />
          All Subscriptions
        </h2>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trial Ends</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period Ends</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Payment</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="subscription in subscriptions" :key="subscription.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ subscription.tenant_name }}</div>
                    <div class="text-sm text-gray-500">{{ subscription.tenant_id }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', getStatusColor(subscription.status)]">
                    {{ subscription.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(subscription.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(subscription.trial_ends_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(subscription.current_period_ends_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(subscription.last_payment_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-4">
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
          <Send class="w-4 h-4 mr-2" />
          Send Reminders
        </button>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
          <RefreshCw class="w-4 h-4 mr-2" />
          Process Expired
        </button>
      </div>
    </div>
  </SuperAdminLayout>
</template>

