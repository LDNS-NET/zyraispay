<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import { useToast } from "vue-toastification";
import Card from "@/Components/Card.vue";

const toast = useToast();

const props = defineProps({
  user: Object,
  lifetimeTotal: Number,
  paymentReliability: Number,
  clientValue: Number,
  tenant: Object,
  filters: Object,
  payments: Array, // 👈 payments come from controller
});

const showModal = ref(false);
const editing = ref(null);
const activeTab = ref("general");

const form = useForm({
  full_name: "",
  username: "",
  password: "",
  phone: "",
  email: "",
  location: "",
  package_id: "",
  type: "hotspot",
  expires_at: "",
});

function openEdit(user) {
  editing.value = user.id;
  form.full_name = user.full_name ?? "";
  form.username = user.username ?? "";
  form.password = "";
  form.phone = user.phone ?? "";
  form.email = user.email ?? "";
  form.location = user.location ?? "";
  form.package_id = user.package_id ?? "";
  form.type = user.type ?? "hotspot";
  form.expires_at = user.expires_at ? user.expires_at.slice(0, 16) : "";
  showModal.value = true;
}

function submit() {
  if (editing.value) {
    form.put(route("tenants.users.update", { user: editing.value }), {
      onSuccess: () => {
        showModal.value = false;
        toast.success("User updated successfully");
      },
      onError: () => toast.error("Failed to update user"),
    });
  } else {
    form.post(route("tenants.users.store"), {
      onSuccess: () => {
        showModal.value = false;
        toast.success("User created successfully");
      },
      onError: () => toast.error("Failed to create user"),
    });
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-extrabold text-xl text-blue-800 flex items-center gap-2">
          {{ props.user.full_name }}
          <span class="text-gray-500">({{ props.user.username }})</span>
        </h2>
        <PrimaryButton @click="openEdit(props.user)">
          Edit User
        </PrimaryButton>
      </div>
    </template>

    <!-- Tabs -->
    <div class="px-4 sm:px-6 lg:px-8 mt-6">
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            @click="activeTab = 'general'"
            :class="[
              activeTab === 'general'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            General Information
          </button>

          <button
            @click="activeTab = 'payments'"
            :class="[
              activeTab === 'payments'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Payments
          </button>

          <button
            @click="activeTab = 'reports'"
            :class="[
              activeTab === 'reports'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Reports
          </button>

          <button
            @click="activeTab = 'sessions'"
            :class="[
              activeTab === 'sessions'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Sessions
          </button>
        </nav>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <!-- General Info Tab -->
      <div v-if="activeTab === 'general'">
        <div class="bg-white dark:bg-gray-900 shadow rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 
                   4.797.63 6.879 1.804M15 11a3 3 0 
                   11-6 0 3 3 0 016 0z" />
            </svg>
            User Information
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Personal details & account info</p>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="(value, label) in {
              'Full Name': props.user.full_name,
              'Username': props.user.username,
              'Email': props.user.email ?? '—',
              'Password': props.user.password ?? '—',
              'Account': props.user.account_number ?? '—',
              'Phone': props.user.phone ?? '—',
              'Location': props.user.location ?? '—',
              'User Type': props.user.type,
              'Package ID': props.user.package_id ?? '—',
              'Expires At': props.user.expires_at ? new Date(props.user.expires_at).toLocaleString() : '—'
            }" :key="label"
              class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ label }}</span>
              <span class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-medium capitalize">
                {{ value }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments Tab -->
      <div v-if="activeTab === 'payments'" class="bg-white dark:bg-gray-900 shadow rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payments</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">All payments related to this user</p>

            <!-- Payment Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <!-- Lifetime Total -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Lifetime Total</h3>
                <p class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ lifetimeTotal }}
                </p>
            </div>

            <!-- Payment Reliability -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Payment Reliability</h3>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                    {{  paymentReliability }}%
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    (Average speed of payment after expiry)
                </p>
            </div>

            <!-- Client Value -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Client Value</h3>
                <p class="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ clientValue }} / 100
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Compared to all clients
                </p>
            </div>
        </div>


        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Phone</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Receipt #</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Checked</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Paid At</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="payment in props.payments" :key="payment.id">
                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ payment.amount }}</td>
                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ payment.phone ?? '—' }}</td>
                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ payment.receipt_number ?? '—' }}</td>
                <td class="px-4 py-2 text-sm">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="payment.checked ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                  >
                    {{ payment.checked ? 'Yes' : 'No' }}
                  </span>
                </td>
                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                  {{ payment.paid_at ? new Date(payment.paid_at).toLocaleString() : '—' }}
                </td>
              </tr>
              <tr v-if="!props.payments || props.payments.length === 0">
                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                  No payments found for this user.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Reports Tab -->
      <div v-if="activeTab === 'reports'" class="bg-white dark:bg-gray-900 shadow rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reports</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">User reports and usage statistics.</p>
      </div>

      <!-- Sessions Tab -->
      <div v-if="activeTab === 'sessions'" class="bg-white dark:bg-gray-900 shadow rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sessions</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Active and past session details.</p>
      </div>
    </div>

    <!-- Edit Modal -->
    <Modal :show="showModal" @close="showModal = false">
      <form @submit.prevent="submit" class="space-y-4 p-6">
        <div>
          <label class="block text-sm font-medium">Full Name</label>
          <TextInput v-model="form.full_name" type="text" />
          <InputError :message="form.errors.full_name" />
        </div>

        <div>
          <label class="block text-sm font-medium">Username</label>
          <TextInput v-model="form.username" type="text" />
          <InputError :message="form.errors.username" />
        </div>

        <div>
          <label class="block text-sm font-medium">Password</label>
          <TextInput v-model="form.password" type="password" />
          <InputError :message="form.errors.password" />
        </div>

        <div>
          <label class="block text-sm font-medium">Phone</label>
          <TextInput v-model="form.phone" type="text" />
          <InputError :message="form.errors.phone" />
        </div>

        <div>
          <label class="block text-sm font-medium">Email</label>
          <TextInput v-model="form.email" type="email" />
          <InputError :message="form.errors.email" />
        </div>

        <div>
          <label class="block text-sm font-medium">Location</label>
          <TextInput v-model="form.location" type="text" />
          <InputError :message="form.errors.location" />
        </div>

        <div>
          <label class="block text-sm font-medium">Package</label>
          <TextInput v-model="form.package_id" type="text" />
          <InputError :message="form.errors.package_id" />
        </div>

        <div>
          <label class="block text-sm font-medium">Type</label>
          <TextInput v-model="form.type" type="text" />
          <InputError :message="form.errors.type" />
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <DangerButton @click="showModal = false" type="button">Cancel</DangerButton>
          <PrimaryButton :disabled="form.processing">
            {{ editing ? "Update" : "Save" }}
          </PrimaryButton>
        </div>
      </form>
    </Modal>
  </AuthenticatedLayout>
</template>
