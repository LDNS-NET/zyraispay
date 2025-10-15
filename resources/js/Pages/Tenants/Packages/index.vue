<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Plus, Edit, Trash2, Wifi, Save, X } from 'lucide-vue-next'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputError from '@/Components/InputError.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification';


const toast =useToast()


const props = defineProps({
  packages: Object,
  counts: Object,
  filters: Object,
  pagination: Object,
})

const editing = ref(null)
const showModal = ref(false)
const viewing = ref(null)
const SelectedFilter = ref('all')
const selectedPackages = ref([])

const form = useForm({
  name: '',
  price: '',
  duration_value: '',
  duration_unit: 'days',
  type: 'hotspot',
  upload_speed: '',
  download_speed: '',
  burst_limit: '',
  device_limit: '',
})


function formatDuration(days) {
  if (days % 30 === 0) return `${days / 30} months`
  if (days % 7 === 0) return `${days / 7} weeks`
  return `${days} days`
}

function openCreate() {
  editing.value = null
  form.reset()
  form.type = 'hotspot'
  form.duration_unit = 'days'
  showModal.value = true
}

function openEdit(pkg) {
  editing.value = pkg.id
  form.name = pkg.name
  form.price = pkg.price
  form.duration_value = pkg.duration_value
  form.duration_unit = pkg.duration_unit || 'days'
  form.type = pkg.type
  form.upload_speed = pkg.upload_speed
  form.download_speed = pkg.download_speed
  form.burst_limit = pkg.burst_limit
  form.device_limit = pkg.device_limit
  showModal.value = true
}


const selectAll = ref(false)

watch(selectAll, (val) => {
  if (val) {
    selectedPackages.value = props.packages.map(pkg => pkg.id)
  } else {
    selectedPackages.value = []
  }
})

watch(selectedPackages, (val) => {
  selectAll.value = val.length === props.packages.length
})

const bulkDelete = () => {
  if (!selectedPackages.value.length) return
  if (!confirm('Are you sure you want to delete selected packages?')) return

  router.delete(route('tenants.packages.bulk-delete'), {
    data: { ids: selectedPackages.value },
    onSuccess: () => {
      selectedPackages.value = []
      router.visit(route('tenants.packages.index'), {
        preserveScroll: true,
      })
      toast.success('package deleted succesfully')
    }
  })
}

function submit() {
  const payload = {
    ...form.data(),
  }

    if (editing.value) {
    form.put(route('tenants.packages.update', editing.value), {
      data: payload,
      onSuccess: () => {
        showModal.value = false
        toast.success('Package updated successfully')
      },
      onError: () => {
        toast.error('Failed to update package')
      }
    })
  } else {
    form.post(route('tenants.packages.store'), {
      data: payload,
      onSuccess: () => {
        showModal.value = false
        toast.success('Package created successfully')
      },
      onError: () => {
        toast.error('Failed to create package')
      }
    })
  }
}

function remove(id) {
  if (confirm('Are you sure you want to delete this package?')) {
    router.delete(route('tenants.packages.destroy', id), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Package deleted successfully')
      },
      onError: () => {
        toast.error('Failed to delete package')
      }
    })
  }
}



</script>


<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <Wifi class="w-6 h-6 text-blue-500" />
          Internet Packages
        </h2>
        <PrimaryButton @click="openCreate" class="flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Add Package
        </PrimaryButton>
      </div>
    </template>


    <div v-if="selectedPackages.length" class="mb-4 flex items-center justify-between bg-yellow-50 p-3 border border-yellow-200 rounded">
      <div class="flex gap-2">
        <DangerButton @click="bulkDelete">Delete ({{ selectedPackages.length }})</DangerButton>
        </div>
    </div>


    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="overflow-x-auto bg-white shadow rounded-xl">

        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <td class="px-4 py-3">
                <input
                  type="checkbox"
                  v-model="selectAll"
                />

              </td>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Speed</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Price</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Duration</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-3"> <input type="checkbox" :value="pkg.id" v-model="selectedPackages"/></td>
              <td class="px-6 py-3 font-medium">{{ pkg.name }}</td>
              <td class="px-4 py-3 capitalize">{{ pkg.type }}</td>
              <td class="px-4 py-3 text-sm">
                Up: {{ pkg.upload_speed }} Mbps / Down: {{ pkg.download_speed }} Mbps
                <div v-if="pkg.burst_limit" class="text-xs text-gray-500">
                  Burst: {{ pkg.burst_limit }} Mbps
                </div>
              </td>
              <td class="px-4 py-3">Ksh {{ pkg.price }}</td>
              <td class="px-4 py-3">
                  {{ pkg.duration_value }} {{ pkg.duration_unit }}
                  <span class="text-xs text-gray-500">(~{{ pkg.duration_in_days }} days)</span> 
              </td>

              <td class="px-4 py-3 text-right flex justify-end gap-2">
                <button class="text-blue-500 hover:text-blue-700" @click="openEdit(pkg)">
                  <Edit class="w-4 h-4" />
                </button>
                <button class="text-red-500 hover:text-red-700" @click="remove(pkg.id)">
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="packages.length === 0">
              <td colspan="7" class="text-center text-sm text-gray-500 py-6">No packages available.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="pagination.links" />

    </div>

    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">
          {{ editing ? 'Edit Package' : 'Create Package' }}
        </h3>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="name" value="Package Name" />
            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" />
            <InputError :message="form.errors.name" class="mt-1" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <InputLabel for="price" value="Price (KES)" />
              <TextInput id="price" v-model="form.price" type="number" class="mt-1 w-full" />
              <InputError :message="form.errors.price" class="mt-1" />
            </div>

            <div>
              <InputLabel for="duration" value="Duration" />
              <div class="flex gap-2">
                <TextInput id="duration_value" v-model="form.duration_value" type="number" min="1" class="mt-1 w-1/2" />
                <select v-model="form.duration_unit" class="mt-1 w-1/2 border-gray-300 rounded-md">
                  <option value="hours">Hours</option>
                  <option value="days">Days</option>
                  <option value="weeks">Weeks</option>
                  <option value="months">Months</option>
                </select>
              </div>
              <InputError :message="form.errors.duration" class="mt-1" />
            </div>
          </div>

          <div>
            <InputLabel for="type" value="Package Type" />
            <select v-model="form.type" id="type" class="mt-1 w-full border-gray-300 rounded-md">
              <option value="hotspot">Hotspot</option>
              <option value="pppoe">PPPoE</option>
              <option value="static">Static</option>
            </select>
            <InputError :message="form.errors.type" class="mt-1" />
          </div>

          <div v-if="form.type === 'hotspot'">
            <InputLabel for="device_limit" value="Device Limit" />
            <TextInput id="device_limit" v-model="form.device_limit" type="number" class="mt-1 w-full" />
            <InputError :message="form.errors.device_limit" class="mt-1" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <InputLabel for="upload_speed" value="Upload Speed (Mbps)" />
              <TextInput id="upload_speed" v-model="form.upload_speed" type="number" class="mt-1 w-full" />
              <InputError :message="form.errors.upload_speed" class="mt-1" />
            </div>
            <div>
              <InputLabel for="download_speed" value="Download Speed (Mbps)" />
              <TextInput id="download_speed" v-model="form.download_speed" type="number" class="mt-1 w-full" />
              <InputError :message="form.errors.download_speed" class="mt-1" />
            </div>
          </div>

          <div>
            <InputLabel for="burst_limit" value="Burst Limit (Optional)" />
            <TextInput id="burst_limit" v-model="form.burst_limit" type="number" class="mt-1 w-full" />
            <InputError :message="form.errors.burst_limit" class="mt-1" />
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <DangerButton @click="showModal = false" type="button">
              <X class="w-4 h-4 mr-1" />
              Cancel
            </DangerButton>
            <PrimaryButton :disabled="form.processing">
              <Save class="w-4 h-4 mr-1" />
              {{ editing ? 'Update' : 'Save' }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
