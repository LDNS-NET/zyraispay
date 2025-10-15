<script setup>
import { ref, computed, watch } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import Pagination from "@/Components/Pagination.vue";
import Checkbox from "@/Components/Checkbox.vue";
import VueToastificationPlugin from "vue-toastification";
import { useToast } from "vue-toastification";

const toast = useToast();

import { UserPlus, Trash2, Edit, Eye, UserCheck } from "lucide-vue-next";

const props = defineProps({
  users: Object,
  filters: Object,
  counts: Object,
  packages: Object, // comes from controller
});

const showModal = ref(false);
const editing = ref(null);
const viewing = ref(null);
const selectedFilter = ref("all");

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

watch(selectedFilter, (value) => {
  router.get(
    route("tenants.users.index"),
    { type: value },
    { preserveScroll: true }
  );
});

function openCreate() {
  editing.value = null;
  form.reset();
  form.type = "hotspot";
  showModal.value = true;
}

const selected = ref([]);

const bulkForm = useForm({ ids: [] });

const confirmBulkDelete = () => {
  bulkForm.ids = selected.value;
  if (bulkForm.ids.length) {
    bulkForm.post(route("tenants.users.bulk-delete"), {
      onSuccess: () => {
        selected.value = [];
      },
    });
  }
};

function openEdit(user) {
  editing.value = user.id;
  form.full_name  = user.full_name  ?? "";
  form.username   = user.username   ?? "";
  form.password   = "";
  form.phone      = user.phone      ?? "";
  form.email      = user.email      ?? "";
  form.location   = user.location   ?? "";
  form.package_id = user.package_id ?? "";
  form.type       = user.type       ?? "hotspot";
  form.expires_at = user.expires_at
    ? user.expires_at.slice(0, 16)
    : "";
  showModal.value = true;
}


function submit() {
  const options = {
    onSuccess: () => {
      showModal.value = false;
      router.reload({ only: ["users"], preserveScroll: true });
      toast.success(
        editing.value
          ? "User updated successfully"
          : "User created successfully"
      );
    },
    onError: () => {
      toast.error("Something went wrong. Please check the form.");
    },
  };

  if (editing.value) {
    form.put(route("tenants.users.update", editing.value), options);
  } else {
    form.post(route("tenants.users.store"), options);
  }
}

function remove(id) {
  if (confirm("Are you sure you want to delete this User?")) {
    router.delete(route("tenants.users.destroy", id), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success("User deleted successfully");
      },
      onError: () => {
        toast.error("Failed to delete user");
      },
    });
  }
}

const selectedUsers = ref([]);

const bulkDelete = () => {
  if (selectedUsers.value.length && confirm("Delete selected Users?")) {
    router.delete(route("tenants.users.bulk-delete"), {
      data: { ids: selectedUsers.value },
      onSuccess: () => {
        selectedUsers.value = [];
        router.visit(route("tenants.users.index"), {
          preserveScroll: true,
          preserveState: false,
        });
        toast.success("Users successfully deleted");
      },
    });
  }
};

function viewUser(user) {
  viewing.value = user;
}

const packagesByType = computed(() => {
  return props.packages[form.type] || [];
});
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
          <UserCheck class="w-6 h-6 text-blue-600" />
          Users
        </h2>
        <PrimaryButton @click="openCreate" class="flex items-center bg-green-700 gap-2">
          <UserPlus class="w-4 h-4" />
          Add User
        </PrimaryButton>
      </div>
    </template>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <!-- Filters -->
      <div class="flex gap-4 mb-4">
        <button v-for="type in ['all', 'hotspot', 'pppoe', 'static']" :key="type" @click="selectedFilter = type"
          class="px-4 py-1 rounded-full border" :class="{
            'bg-blue-600 text-white': selectedFilter === type,
            'bg-white text-gray-800 border-gray-300':
              selectedFilter !== type,
          }">
          {{ type.charAt(0).toUpperCase() + type.slice(1) }} ({{
            counts[type] || 0
          }})
        </button>
      </div>

      <!-- bulk delete actions-->
      <div v-if="selectedUsers.length"
        class="mb-4 flex items-center justify-between bg-yellow-50 p-3 border border-yellow-200 rounded">
        <div class="flex gap-2">
          <DangerButton @click="bulkDelete">Delete ({{ selectedUsers.length }})</DangerButton>
          <!-- You can add more bulk actions here -->
        </div>
      </div>

      <!-- Users Table -->

      <div class="overflow-x-auto bg-white shadow rounded-xl">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3">
                <input type="checkbox" :checked="selectedUsers.length ===
                  users.data.length
                  " @change="
                                      selectedUsers = $event.target.checked
                                        ? users.data.map((u) => u.id)
                                        : []
                                      " />
              </th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">
                Username
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Account No
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Phone
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Package
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Expiry
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Status
              </th>
              <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <input type="checkbox" :value="user.id" v-model="selectedUsers" />
              </td>
              <td class="px-6 py-3 font-medium">
                  <Link :href="route('tenants.users.show', user.id)" class="block">
                    <div class="font-bold text-gray-900">{{ user.username }}</div>
                    <div class="text-sm text-gray-600">{{ user.full_name }}</div>
                  </Link>
              </td>

              <td class="px-4 py-3 font-mono text-xs text-gray-700">
                <span v-if="user.account_number">{{
                  user.account_number?.substring(0, 10)
                }}</span>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3">{{ user.phone }}</td>
              <td class="px-4 py-3">
                {{ user.package?.name || "-" }}
              </td>
              <td class="px-4 py-3">{{ user.expiry_human }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="user.is_online
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-200 text-gray-700'
                  ">
                  {{ user.is_online ? "Online" : "Offline" }}
                </span>
              </td>
              <td class="px-4 py-3 text-right flex justify-end gap-2">
                <div class="relative inline-block text-left">
                  <button @click="
                    user.showActions = !user.showActions
                    " class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span class="sr-only">Open actions</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="1.5" />
                      <circle cx="19.5" cy="12" r="1.5" />
                      <circle cx="4.5" cy="12" r="1.5" />
                    </svg>
                  </button>
                  <div v-if="user.showActions"
                    class="origin-top-right absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                      <button @click="
                        viewUser(user);
                      user.showActions = false;
                      "
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        View
                      </button>
                      <button @click="
                        openEdit(user);
                      user.showActions = false;
                      "
                        class="block w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                        Edit
                      </button>
                      <button @click="
                        remove(user.id);
                      user.showActions = false;
                      "
                        class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="6" class="text-center text-gray-500 py-6">
                No users found.
              </td>
            </tr>
          </tbody>
        </table>
        <Pagination class="mt-4" :links="users.links" />
      </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">
          {{ editing ? "Edit User" : "Create User" }}
        </h3>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="full_name" value="Full Name" />
            <TextInput v-model="form.full_name" id="full_name" class="mt-1 block w-full" />
            <InputError :message="form.errors.full_name" />
          </div>

          <div>
            <InputLabel for="username" value="Username" />
            <TextInput v-model="form.username" id="username" class="mt-1 block w-full" />
            <InputError :message="form.errors.username" />
          </div>

          <div>
            <InputLabel for="password" value="Password" />
            <TextInput v-model="form.password" id="password" class="mt-1 block w-full" type="text" autocomplete="off" />
            <InputError :message="form.errors.password" />
          </div>

          <div>
            <InputLabel for="phone" value="Phone" />
            <TextInput v-model="form.phone" id="phone" class="mt-1 block w-full" />
            <InputError :message="form.errors.phone" />
          </div>

          <div>
            <InputLabel for="email" value="Email" />
            <TextInput v-model="form.email" id="email" class="mt-1 block w-full" />
            <InputError :message="form.errors.email" />
          </div>

          <div>
            <InputLabel for="location" value="Location" />
            <TextInput v-model="form.location" id="location" class="mt-1 block w-full" />
            <InputError :message="form.errors.location" />
          </div>

          <div>
            <InputLabel for="expires_at" value="Expiry Date" />
            <TextInput id="expires_at" type="datetime-local" v-model="form.expires_at" class="mt-1 block w-full" />
            <InputError :message="form.errors.expires_at" />
          </div>

          <div>
            <InputLabel for="type" value="User Type" />
            <select v-model="form.type" id="type" class="mt-1 w-full border-gray-300 rounded-md">
              <option value="hotspot">Hotspot</option>
              <option value="pppoe">PPPoE</option>
              <option value="static">Static</option>
            </select>
            <InputError :message="form.errors.type" />
          </div>

          <div>
            <InputLabel for="package_id" value="Package" />
            <select v-model="form.package_id" id="package_id" class="mt-1 w-full border-gray-300 rounded-md">
              <option v-for="pkg in packagesByType" :key="pkg.id" :value="pkg.id">
                {{ pkg.name }}
              </option>
            </select>
            <InputError :message="form.errors.package_id" />
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <DangerButton @click="showModal = false" type="button">Cancel</DangerButton>
            <PrimaryButton :disabled="form.processing">{{
              editing ? "Update" : "Save"
            }}</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- View Modal -->
    <Modal :show="viewing" @close="viewing = null">
      <div class="bg-gradient-to-r from-cyan-100 to-violet-100 rounded-lg shadow-xl p-6 max-w-2xl mx-auto space-y-6">
        <div class="text-center">
          <h2 class="text-2xl font-bold text-gray-800">
            User Profile
          </h2>
          <p class="text-sm text-gray-500 mt-1">
            Complete access details for client configuration
          </p>
        </div>

        <!-- Credentials Section -->
        <div class="bg-indigo-200 rounded-md border p-4">
          <h3 class="text-sm font-semibold text-blue-700 mb-2">
            Login Credentials
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-800">
            <div>
              <span class="text-blue-700">Username:</span>
              <div class="font-mono text-green-700">
                {{ viewing?.username }}
              </div>
              <div v-if="viewing?.account_number" class="text-xs text-gray-500">
                Account No:
                {{ viewing?.account_number.substring(0, 6) }}
              </div>
            </div>
            <div>
              <span class="text-blue-700">Password:</span>
              <div class="flex items-center space-x-2">
                <span class="font-mono text-red-600">{{
                  viewing?.password
                }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Personal Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
          <div>
            <span class="font-semibold text-black">Full Name:</span>
            <p>{{ viewing?.full_name }}</p>
          </div>
          <div>
            <span class="font-semibold text-black">Phone:</span>
            <p>{{ viewing?.phone }}</p>
          </div>
          <div>
            <span class="font-semibold text-black">Email:</span>
            <p>{{ viewing?.email || "—" }}</p>
          </div>
          <div>
            <span class="font-semibold text-black">Location:</span>
            <p>{{ viewing?.location || "—" }}</p>
          </div>
          <div>
            <span class="font-semibold text-black">Package:</span>
            <p>{{ viewing?.package?.name || "—" }}</p>
          </div>
          <div>
            <span class="font-semibold text-black">Type:</span>
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
              {{ viewing?.type }}
            </span>
          </div>
          <div>
            <span class="font-semibold text-black">Expiry:</span>
            <p>{{ viewing?.expiry_human }}</p>
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <PrimaryButton @click="viewing = null">Close</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
