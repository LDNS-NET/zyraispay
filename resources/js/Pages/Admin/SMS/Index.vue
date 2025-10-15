<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { CheckCircle, XCircle } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';


const { props } = usePage();
const users = props.users || [];
const messages = props.smsList?.data || [];
const paginationLinks = props.smsList?.links || [];
const flash = props.flash || {};
const balance = props.balance?.balance ?? 'Unknown';
const filters = ref({ ...props.filters });

const message = ref('');
const filtersExpanded = ref(false);
const selectedPhones = ref([]);
const sending = ref(false);
const showForm = ref(false);
const selectedMessages = ref([]);
const userSearch = ref('');
const selectAll = ref(false);

// When Select All is toggled, update selectedPhones
watch(selectAll, (checked) => {
  if (checked) {
    selectedPhones.value = filteredUsers.value.map(u => u.phone);
  } else {
    selectedPhones.value = [];
  }
});

// Keep Select All checkbox in sync with manual selection
watch(selectedPhones, (newSelection) => {
  const allVisible = filteredUsers.value.map(u => u.phone);
  selectAll.value = newSelection.length === allVisible.length && allVisible.length > 0;
});


// Filter users for scrollable list
const filteredUsers = computed(() => {
  const query = userSearch.value.toLowerCase();
  return users.filter(u =>
    u.name.toLowerCase().includes(query) || u.phone.toLowerCase().includes(query)
  );
});

//clear search logic
const clearSearch = () => {
  filters.value.search = '';
  applyFilters(); // refresh immediately
};

const applyFilters = () => {
  router.get(route('admin.sms.index'), filters.value, {
    preserveState: false,
    preserveScroll: true,
    replace: true,
  });
};



// Send SMS logic
const messageError = ref(false);

const sendSMS = () => {
  // Basic validation
  messageError.value = false;

  if (!message.value.trim()) {
    messageError.value = true;
    return;
  }

  sending.value = true;

  const form = useForm({
    message: message.value,
    recipients: selectedPhones.value
  });

  form.post(route('admin.sms.send'), {
    onFinish: () => {
      sending.value = false;
      message.value = '';
      selectedPhones.value = [];
      showForm.value = false;

      router.visit(route('admin.sms.index'), {
        preserveScroll: true,
        preserveState: false,
      });
    }
  });
};

//truncate helper for shortening messages in list to four words
const truncateMessage = (text, wordLimit = 2) => {
    if ( !text) return '';
    const words = text.trim().split(/\s+/);
    return words.lents > wordLimit
    ?wordLimit.slice(0, wordLimit).join(' ') + '...'
    :text;
};

// Delete single message
const deleteMessage = (id) => {
  if (confirm('Are you sure you want to delete this message?')) {
    router.delete(route('admin.sms.destroy', id), {
      onSuccess: () => {
        router.visit(route('admin.sms.index'), {
            preserveScroll:true,
            preserveState: false,
        })
      }
    });
  }
};


// Bulk delete
const bulkDelete = () => {
  if (selectedMessages.value.length && confirm('Delete selected messages?')) {
    router.delete(route('admin.sms.bulk-destroy'), {
      data: { ids: selectedMessages.value },
      onSuccess: () => {
        selectedMessages.value = [];
        router.visit(route('admin.sms.index'), {
        preserveScroll: true,
        preserveState: false,
        })
      }
    });
  }
};

</script>

<template>
  <SuperAdminLayout>
    <div class="p-6 space-y-6">

      <!-- Header with balance -->
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">SMS Messages</h1>
        <span class="bg-blue-100 text-green-800 px-3 py-1 rounded text-sm font-mono">Balance: {{ balance }}</span>
      </div>

      <!-- Flash Message -->
      <div v-if="flash.success" class="text-green-600">{{ flash.success }}</div>




      <!-- Simple Search -->
        <div class="flex items-center gap-2 mt-4">
        <input
            v-model="filters.search"
            @keydown.enter.prevent="applyFilters"
            placeholder="Search by name or phone..."
            class="border rounded px-2 py-2 max-w-xs"
        />

        <!-- Search Button -->
        <button
            @click="applyFilters"
            class="bg-blue-600 text-white px-3 py-2 rounded"
        >
            Search
        </button>

        <!-- Clear Button -->
        <button
            v-if="filters.search"
            @click="clearSearch"
            class="text-gray-500 text-sm hover:text-red-600">
            Clear
        </button>
        </div>



      <!-- Toggle Send Form -->
        <div class = "flex justify-end">
            <button @click="showForm = !showForm"
                    class="bg-green-700 text-white hover:text-blue-900 hover:bg-green-400 px-4 py-2 rounded">
                {{ showForm ? 'Close' : 'Send SMS' }}
            </button>
        </div>

      <!-- SMS Send Form -->
      <Modal :show="showForm" @close="showForm = false">
  <div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold">Create SMS</h2>

    <!-- Selected Recipients -->
    <div v-if="selectedPhones.length" class="space-y-1">
      <h3 class="font-semibold">Selected Users ({{ selectedPhones.length }}):</h3>
      <div class="max-h-32 overflow-y-auto border rounded p-2 bg-green-200 flex flex-wrap gap-2">
        <span
          v-for="phone in selectedPhones"
          :key="phone"
          class="bg-yellow-300 px-2 py-1 rounded text-sm whitespace-nowrap"
        >
          {{ users.find(u => u.phone === phone)?.name || 'Unknown' }} ({{ phone }})
        </span>
      </div>
    </div>

    <!-- Search + Scrollable List -->
    <div class="space-y-2">
      <label class="block font-semibold">Search Users</label>
      <input v-model="userSearch" type="text"
             placeholder="Search by name or phone..."
             class="border rounded px-3 py-2 w-full max-w-md" />

      <div class="border rounded max-h-40 overflow-y-auto bg-green-200 p-2 shadow-inner">
        <ul class="space-y-1">
            <!-- Select All Checkbox -->
            <div class="flex items-center gap-2 mb-2">
                <input
                    type="checkbox"
                    v-model="selectAll"
                    class="form-checkbox text-green-700"
                />
                <label class="text-sm">Select All ({{ filteredUsers.length }})</label>
            </div>

          <li v-for="u in filteredUsers" :key="u.id" class="flex items-center">
            <input type="checkbox"
                   :value="u.phone"
                   v-model="selectedPhones"
                   class="form-checkbox text-green-900" />
            <span class="ml-2 truncate">{{ u.name }} — {{ u.phone }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Message -->
    <div>
      <label class="block mb-1 font-semibold">Message</label>
      <textarea v-model="message" rows="4" required class="w-full border bg-green-100 p-2 rounded"></textarea>
      <p v-if="messageError" class="text-red-600 text-sm mt-1">Message is required.</p>

    </div>

    <!-- Send Button -->
    <div class="text-right">
      <button @click="sendSMS" class="bg-green-900 text-white px-4 py-2 rounded shadow">
        {{ sending ? 'Sending...' : 'Send SMS' }}
      </button>
    </div>
  </div>
</Modal>


      <!-- Sent Messages -->
<div v-if="messages.length" class="space-y-4">
  <div class="flex justify-between items-center">
    <h2 class="text-lg font-semibold">Sent Messages</h2>
    <button
      @click="bulkDelete"
      :disabled="!selectedMessages.length"
      class="bg-red-600 text-white px-3 py-1 rounded disabled:opacity-50"
    >
      Delete Selected
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-x-green-900 border-yellow-500 bg-gradient-to-r from-cyan-100 to-purple-100  text-sm">
      <thead class="bg-green-200">
        <tr>
          <th class="px-3 py-2 text-left">
            <input
              type="checkbox"
              :checked="selectedMessages.length === messages.length"
              @change="selectedMessages = $event.target.checked ? messages.map(m => m.id) : []"
            />
          </th>
          <th class="px-3 py-2 text-left">Recipient</th>
          <th class="px-3 py-2 text-left">Message</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Sent By</th>
          <th class="px-3 py-2 text-left">Sent At</th>
          <th class="px-3 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="msg in messages" :key="msg.id" class="border-t">
          <td class="px-3 py-2">
            <input
              type="checkbox"
              v-model="selectedMessages"
              :value="msg.id"
            />
          </td>
          <td class="px-3 py-2 whitespace-nowrap">
            {{ msg.recipient_name }}<br />
            <span class="text-gray-600 text-xs">{{ msg.recipient_phone }}</span>
          </td>
          <td class="px-3 py-2 whitespace-nowrap max-w-xs overflow-hidden text-ellipsis" :title="msg.message">
            {{truncateMessage( msg.message, 2) }}</td>
            <td class="px-3 py-2 capitalize flex items-center gap-1">
                <CheckCircle v-if="msg.status === 'sent'" class="text-green-600 w-4 h-4" />
                <XCircle v-else-if="msg.status === 'failed'" class="text-red-600 w-4 h-4" />
                <svg v-else class="w-4 h-4 text-yellow-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="8" />
                </svg>
                <span class="text-sm">{{ msg.status }}</span>
            </td>

          <td class="px-3 py-2">{{ msg.user?.name || 'System' }}</td>
          <td class="px-3 py-2">{{ new Date(msg.sent_at).toLocaleString() }}</td>
          <td class="px-3 py-2">
            <button
              @click="deleteMessage(msg.id)"
              class="text-red-600 hover:underline"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>


        <!-- Pagination -->
        <div v-if="paginationLinks.length" class="mt-4">
          <nav class="flex flex-wrap gap-2">
            <button
            v-for="link in paginationLinks"
               :key="link.url  || link.label"
               v-html="link.label"
               @click.prevent="link.url ? router.get(link.url, {}, {
                preserveScroll:true,
                preserveState: false }) : null"
               :disabled="!link.url"
                class="px-3 py-1 border rounded bg-white hover:bg-blue-400"></button>
          </nav>
        </div>
      </div>

      <p v-else class="text-gray-600">No SMS messages found.</p>
    </div>
  </SuperAdminLayout>
</template>
