<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardSection from '@/Components/DashboardSection.vue';
import Card from '@/Components/Card.vue';
import ChartCard from '@/Components/ChartCard.vue';
import {
  Users,
  UserPlus,
  User,
  Ticket,
  Inbox,
  RadioTower,
  DollarSign,
  Package,
  Coins,
  MessagesSquare,
  Server,
  FileText,
  Star,
  Smile,
  BarChart2,
  TrendingUp,
  Activity,
  Check,
  X,
  Clock,
  Calendar
} from 'lucide-vue-next';

const props = defineProps(['stats']);
const user = usePage().props.auth.user;

// Debug subscription data
console.log('Subscription data:', props.stats?.subscription);

// Dynamic greeting based on time of day
const getGreeting = () => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good morning';
  if (hour < 18) return 'Good afternoon';
  return 'Good evening';
};
const greeting = getGreeting();

// Icon mapping for card types
const cardIcons = {
  users: Users,
  leads: UserPlus,
  tickets: Ticket,
  mikrotiks: RadioTower,
  payments: DollarSign,
  packages: Package,
  equipment: Server,
  sms: MessagesSquare,
  subscription: Star,
  analytics: BarChart2,
  recent: Activity
};
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Tenant Dashboard" />

    <div class="p-6 space-y-6">
      <!-- Pay Subscription Button & Duration -->
      <div class="flex justify-between items-center mb-4">
        <div>
          <span v-if="stats.subscription && stats.subscription.current_period_days_remaining !== undefined">
            <span class="text-sm text-gray-700 font-semibold">Next subscription in:</span>
            <span class="text-base font-bold text-indigo-700">
              {{ stats.subscription.current_period_days_remaining }} days
              <span v-if="stats.subscription.current_period_hours_remaining !== undefined">
                {{ stats.subscription.current_period_hours_remaining }} hours
              </span>
            </span>
          </span>
        </div>
        <button
          class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-bold py-2 px-6 rounded-lg shadow hover:from-indigo-600 hover:to-blue-700 transition"
          @click="$inertia.visit('/tenant/subscription/payment')"
        >
          Pay Subscription
        </button>
      </div>
      <!-- Account Balance Card -->
      <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-xl shadow-lg p-6 flex items-center justify-between mb-8">
        <div>
          <h2 class="text-xl font-bold text-gray-800 mb-1">Account Balance</h2>
          <div class="text-3xl font-extrabold text-green-700">KES {{ stats.account_balance ?? '0.00' }}</div>
          <div class="text-xs text-gray-500 mt-1">Wallet ID: <span class="font-mono">{{ stats.wallet_id || 'Not Set' }}</span></div>
        </div>
        <DollarSign class="w-12 h-12 text-green-400" />
      </div>
      <!-- Trial Banner -->
      <div v-if="stats.subscription && stats.subscription.is_on_trial" 
           class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="bg-white/20 rounded-full p-3">
              <Clock class="w-8 h-8 text-white" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-white">Free Trial Active</h2>
              <p class="text-blue-100">
                {{ stats.subscription.trial_days_remaining }} days
                <span v-if="stats.subscription.trial_hours_remaining !== undefined">
                  {{ stats.subscription.trial_hours_remaining }} hours
                </span>
                remaining in your trial period
              </p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold text-white">
              {{ stats.subscription.trial_days_remaining }}
              <span v-if="stats.subscription.trial_hours_remaining !== undefined">
                .{{ stats.subscription.trial_hours_remaining }}h
              </span>
            </div>
            <div class="text-blue-100 text-sm">left</div>
          </div>
        </div>
      </div>

      <!-- USERS -->
      <DashboardSection title="Network Users">
        <Card title="Total" :value="stats.users.total" :icon="Users" />
        <Card title="Hotspot" :value="stats.users.hotspot" :icon="RadioTower" />
        <Card title="PPPoE" :value="stats.users.pppoe" :icon="User" />
        <Card title="Static" :value="stats.users.static" :icon="Server" />
        <Card title="Active" :value="stats.users.active" :icon="Smile" />
        <Card title="Expired" :value="stats.users.expired" :icon="X" />
      </DashboardSection>

      <!-- LEADS -->
      <DashboardSection title="Leads">
        <Card title="Total" :value="stats.leads.total" :icon="Inbox" />
        <Card title="Pending" :value="stats.leads.pending" :icon="Activity" />
        <Card title="Converted" :value="stats.leads.converted" :icon="Check" />
        <Card title="Lost" :value="stats.leads.lost" :icon="X" />
      </DashboardSection>

      <!-- TICKETS -->
      <DashboardSection title="Tickets">
        <Card title="Open" :value="stats.tickets.open" :icon="Ticket" />
        <Card title="Closed" :value="stats.tickets.closed" :icon="Check" />
        <Card title="Assigned to Me" :value="stats.tickets.assigned_to_me" :icon="User" />
      </DashboardSection>

      <!-- MIKROTIKS -->
      <DashboardSection title="MikroTik Devices">
        <Card title="Total" :value="stats.mikrotiks.total" :icon="RadioTower" />
        <Card title="Connected" :value="stats.mikrotiks.connected" :icon="Check" />
        <Card title="Disconnected" :value="stats.mikrotiks.disconnected" :icon="X" />
      </DashboardSection>

      <!-- PAYMENTS -->
      <DashboardSection title="Payments" v-if="user.role === 'admin' || user.role === 'cashier'">
        <Card title="Total Payments" :value="stats.payments.count" :icon="DollarSign" />
        <Card title="Total Amount" :value="`KES ${stats.payments.total_amount}`" :icon="Coins" />
        <Card v-if="stats.payments.latest" title="Latest Payment" :value="`KES ${stats.payments.latest.amount}`" :subtitle="stats.payments.latest.paid_at" :icon="DollarSign" />
        <Card title="Pending Disbursements" :value="stats.payments.pending_disbursement" :icon="FileText" />
      </DashboardSection>

      <!-- SMS -->

      <DashboardSection title="SMS" v-if="user.role === 'admin' || user.role === 'cashier'">
        <Card title="Total Sent" :value="stats.sms.total_sent" :icon="MessagesSquare" />
        <Card title="This Month" :value="stats.sms.sent_this_month" :icon="TrendingUp" />
      </DashboardSection>

      <!-- PACKAGES -->

      <DashboardSection title="Packages">
        <Card title="Total" :value="stats.packages.total" :icon="Package" />
        <Card title="Active" :value="stats.packages.active" :icon="Check" />
      </DashboardSection>

      <!-- EQUIPMENT -->

      <DashboardSection title="Equipment" v-if="user.role === 'admin' || user.role === 'technician'">
        <Card title="Total Items" :value="stats.equipment.total" :icon="Server" />
        <Card title="Total Value" :value="`KES ${stats.equipment.total_value}`" :icon="DollarSign" />
      </DashboardSection>

      <!-- SUBSCRIPTION -->
      <DashboardSection title="Subscription">
        <template v-if="stats.subscription">
          <Card 
            title="Status" 
            :value="stats.subscription.is_active ? 'Active' : stats.subscription.is_on_trial ? 'Trial' : 'Expired'" 
            :icon="stats.subscription.is_active ? Check : stats.subscription.is_on_trial ? Clock : X" 
          />
          <Card 
            v-if="stats.subscription.is_on_trial"
            title="Trial Days Remaining" 
            :value="stats.subscription.trial_days_remaining" 
            :icon="Clock" 
          />
          <Card 
            v-if="stats.subscription.is_active"
            title="Days Remaining" 
            :value="stats.subscription.current_period_days_remaining" 
            :icon="Calendar" 
          />
          <Card 
            title="Next Billing" 
            :value="stats.subscription.next_billing_date ? new Date(stats.subscription.next_billing_date).toLocaleDateString() : 'N/A'" 
            :icon="Calendar" 
          />
          <Card 
            title="Amount" 
            :value="`KES ${stats.subscription.amount}`" 
            :icon="DollarSign" 
          />
          <!-- Debug info - remove in production -->
          <Card 
            title="Debug - Trial Ends" 
            :value="stats.subscription.trial_ends_at ? new Date(stats.subscription.trial_ends_at).toLocaleDateString() : 'N/A'" 
            :icon="Clock" 
          />
        </template>
        <div class="mt-4 flex justify-end">
          <a href="/subscription/payment" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow transition">Pay Subscription</a>
        </div>
      </DashboardSection>

      <!-- Trial Warning Banner -->
      <div v-if="stats.subscription && stats.subscription.is_on_trial && stats.subscription.trial_days_remaining <= 3" 
           class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
          <Clock class="w-5 h-5 text-yellow-600 mr-2" />
          <div>
            <h3 class="text-sm font-medium text-yellow-800">Trial Ending Soon</h3>
            <p class="text-sm text-yellow-700">
              Your trial period ends in {{ stats.subscription.trial_days_remaining }} days. 
              <a href="/subscription/payment" class="underline hover:text-yellow-800">Renew now</a> to continue using our services.
            </p>
          </div>
        </div>
      </div>


      <!-- CHARTS -->
      <DashboardSection title="Analytics & Trends">
        <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
          <ChartCard
            title="User Type Distribution"
            :labels="stats.user_distribution ? Object.keys(stats.user_distribution) : []"
            :values="stats.user_distribution ? Object.values(stats.user_distribution) : []"
            type="donut"
            :icon="BarChart2"
            class="w-full min-w-[300px]!"
          />
          <ChartCard
            title="Monthly SMS Sent"
            :labels="stats.sms_chart ? Object.keys(stats.sms_chart) : []"
            :values="stats.sms_chart ? Object.values(stats.sms_chart) : []"
            type="bar"
            :icon="TrendingUp"
            class="w-full min-w-[300px]!"
          />
          <ChartCard
            title="Payments Over Time"
            :labels="stats.payments_chart ? Object.keys(stats.payments_chart) : []"
            :values="stats.payments_chart ? Object.values(stats.payments_chart) : []"
            type="line"
            :icon="DollarSign"
            class="w-full min-w-[300px]!"
          />
        </div>
      </DashboardSection>


      <!-- RECENT ACTIVITY -->
      <DashboardSection title="Recent Activity">
        <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
          <div class="bg-white rounded-lg shadow p-4 w-full min-w-[300px]!">
            <h3 class="font-semibold text-blue-600 flex items-center gap-2 mb-2"><Users class="w-5 h-5" /> New Users</h3>
            <ul class="text-sm text-gray-500">
              <li v-for="u in stats.recent_activity.latest_users" :key="u.username">{{ u.username }} - {{ u.type }}</li>
            </ul>
          </div>
          <div class="bg-white rounded-lg shadow p-4 w-full min-w-[300px]!">
            <h3 class="font-semibold text-green-600 flex items-center gap-2 mb-2"><DollarSign class="w-5 h-5" /> Recent Payments</h3>
            <ul class="text-sm text-gray-500">
              <li v-for="p in stats.recent_activity.latest_payments" :key="p.receipt_number">
                KES {{ p.amount }} - {{ p.paid_at }}
              </li>
            </ul>
          </div>
          <div class="bg-white rounded-lg shadow p-4 w-full min-w-[300px]!">
            <h3 class="font-semibold text-purple-600 flex items-center gap-2 mb-2"><Inbox class="w-5 h-5" /> Latest Leads</h3>
            <ul class="text-sm text-gray-500">
              <li v-for="l in stats.recent_activity.latest_leads" :key="l.name">{{ l.name }} - {{ l.status }}</li>
            </ul>
          </div>
        </div>
      </DashboardSection>

      <!-- EXPORT OPTIONS -->
      <div v-if="user.role === 'admin'" class="flex justify-end gap-3 mt-8">
        <button @click="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded">Print</button>
        <a :href="route('dashboard.export', { format: 'excel' })" class="bg-green-600 text-white px-4 py-2 rounded">Export Excel</a>
        <a :href="route('dashboard.export', { format: 'pdf' })" class="bg-red-600 text-white px-4 py-2 rounded">Export PDF</a>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
