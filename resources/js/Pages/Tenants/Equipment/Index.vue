<script setup>
import { ref, watch, computed } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { Plus, Edit, Trash2, Save, X } from 'lucide-vue-next'

const props = defineProps({
    equipment: Object,
    totalPrice: Number,
    Count: Object,
    filters: Object,
    Pagination: Object,
})

const showModal = ref(false)
const editing = ref(null)
const selectedTenantEquipment = ref([])
const selectAll = ref(false)



const form = useForm({
    name: '',
    type: '',
    serial_number: '',
    location: '',
    model: '',
    price: '',
    total_price: '',
    assigned_to: '',
})

function openAddModal() {
    form.reset()
    editing.value = null
    showModal.value = true
}

function openEditModal(equip) {
    form.name = equip.name
    form.type = equip.type
    form.serial_number = equip.serial_number
    form.location = equip.location
    form.model = equip.model
    form.price = equip.price
    form.total_price = equip.total_price
    form.assigned_to = equip.assigned_to
    editing.value = equip.id
    showModal.value = true
}

function submit() {
    if (editing.value) {
        form.put(route('tenants.equipment.update', editing.value), {
            onSuccess: () => showModal.value = false
        })
    } else {
        form.post(route('tenants.equipment.store'), {
            onSuccess: () => showModal.value = false
        })
    }
}

function remove(id) {
    if (confirm("Delete this Equipment")){
        router.delete(route('tenants.equipment.destroy',id))
    }
}

watch(selectAll, (val) => {
  if (val) {
    selectedTenantEquipment.value = props.equipment.data.map(equipment => equipment.id)
  } else {
    selectedTenantEquipment.value = []
  }
})

const allIds = computed(() => props.equipment.data.map(l => l.id))


//bulk delete
const bulkDelete = () => {
  if (!selectedTenantEquipment.value.length) return
  if (!confirm('Are you sure you want to delete Equipment?')) return

  router.delete(route('tenants.equipment.bulk-delete'), {
    data: { ids: selectedTenantEquipment.value },
    onSuccess: () => {
      selectedTenantEquipment.value = []
      router.visit(route('tenants.equipment.index'), {
        preserveScroll: true,
      })
    }
  })
}

</script>

<template>
<AuthenticatedLayout>
    <Head title="Equipment" />

    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold flex">Equipment</h2>
            <PrimaryButton @click="openAddModal" class="flex items-center gap-2" >
                <Plus class="h-4 w-4" /> Equipment</PrimaryButton>
        </div>

        <div class="bg-white p-4 rounded shadow text-xl font-semibold">
            Total Equipment Cost: KES {{ totalPrice.toLocaleString() }}
        </div>

        <!--bulk delete option opens if checkbox value is true-->
        <div v-if="selectedTenantEquipment.length" class="mb-4 flex items-center justify-between bg-yellow-50 p-3 border border-yellow-200 rounded">
            <div class="flex gap-2">
                <DangerButton @click="bulkDelete">Delete ({{ selectedTenantEquipment.length }})</DangerButton>
            </div>
        </div>

        <table class="w-full bg-white rounded shadow overflow-hidden">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <td class="px-4 py-3"> <input type="checkbox" v-model="selectAll"/></td>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Serial</th>
                    <th class="px-4 py-2">Location</th>
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">Price (Ksh)</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in equipment.data" :key="item.id" class="border-t">
                    <td class="px-6 py-3"> <input type="checkbox" :value="item.id" v-model="selectedTenantEquipment"/></td>
                    <td class="px-4 py-2">{{ item.name }}</td>
                    <td class="px-4 py-2">{{ item.type }}</td>
                    <td class="px-4 py-2">{{ item.serial_number }}</td>
                    <td class="px-4 py-2">{{ item.location ?? '-' }}</td>
                    <td class="px-4 py-2">{{ item.assigned_to ?? '-' }}</td>
                    <td class="px-4 py-2">{{ item.total_price?.toLocaleString() ?? '0.00' }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <button @click="openEditModal(item)" class="text-blue-600 hover:underline"><Edit class="w-4 h-4" /> </button>
                        <button @click="remove(item.id)" >
                            <Trash2 class="w-4 h-4 text-red-600" />


                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination :links="equipment.links" />

    <!-- Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">
                {{ editing ? 'Edit Equipment' : 'Add Equipment' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput v-model="form.name" id="name" class="mt-1 block w-full" />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="type" value="Type" />
                    <TextInput v-model="form.type" id="type" class="mt-1 block w-full" />
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="serial_number" value="Serial Number" />
                    <TextInput v-model="form.serial_number" id="serial_number" class="mt-1 block w-full" />
                    <InputError :message="form.errors.serial_number" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="location" value="Location" />
                    <TextInput v-model="form.location" id="location" class="mt-1 block w-full" />
                    <InputError :message="form.errors.location" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="model" value="Model" />
                    <TextInput v-model="form.model" id="model" class="mt-1 block w-full" />
                    <InputError :message="form.errors.model" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="price" value="Price" />
                    <TextInput v-model="form.price" id="price" type="number" step="0.01" class="mt-1 block w-full" />
                    <InputError :message="form.errors.price" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="total_price" value="Total Price" />
                    <TextInput v-model="form.total_price" id="total_price" type="number" step="0.01" class="mt-1 block w-full" />
                    <InputError :message="form.errors.total_price" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="assigned_to" value="Assigned To" />
                    <TextInput v-model="form.assigned_to" id="assigned_to" class="mt-1 block w-full" />
                    <InputError :message="form.errors.assigned_to" class="mt-2" />
                </div>
            </div>

            <div class="mt-4 text-right">
                <PrimaryButton :disabled="form.processing">
                    {{ editing ? 'Update' : 'Save' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</AuthenticatedLayout>
</template>
