<script setup>
import { ref,watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import Card from '@/Components/Card.vue';
import { useToast } from 'vue-toastification';
import { Plus, ReceiptText } from 'lucide-vue-next';
import DangerButton from '@/Components/DangerButton.vue';



const toast = useToast();
const editing = ref(null);
const showModal = ref(false);
const selectedInvoice = ref([]);
const selectAll = ref(false);

const props = defineProps({
    auth: Object,
    invoices: Object,
    filters: Object,
    can: Object,
    flash: Object,
});

const invoices = ref([]);
const form = useForm({});

// Watch for user selection and auto-fill package and amount
watch(() => form.user_id, (userId) => {
    if (!userId) {
        form.package = '';
        form.amount = '';
        return;
    }
    const user = props.users.find(u => u.id === userId);
    if (user && user.package) {
        form.package = user.package.type || user.package.name || '';
        form.amount = user.package.price || user.package.amount || '';
    } else {
        form.package = '';
        form.amount = '';
    }
});


function openModal(invoice = null) {
    form.reset();
    if (invoice) {
        editing.value = true;
        form.id = invoice.id;
        form.user = invoice.user;
        form.amount = invoice.amount;
        form.package = invoice.package;
        form.issued_on = invoice.issued_on;
        form.due_on = invoice.due_on;
        form.status = invoice.status;
    } else {
        editing.value = false;
        form.user = '';
        form.amount = '';
        form.package = '';
        form.issued_on = '';
        form.due_on = '';
        form.status = '';
    }
    modalOpen.value = true;
}

const modalOpen = ref(false);
function closeModal() {
    modalOpen.value = false;
}

function submit() {
    if (editing.value) {
        form.put(route('tenant.invoices.update', form.id), {
            onSuccess: () => {
                toast.success('Invoice updated successfully.');
                closeModal();
            },
            onError: () => {
                toast.error('There was an error updating the invoice.');
            },
        });
    } else {
        form.post(route('tenant.invoices.store'), {
            onSuccess: () => {
                toast.success('Invoice created successfully.');
                closeModal();
            },
            onError: () => {
                toast.error('There was an error creating the invoice.');
            },
        });
    }
}

const deleteInvoice = (invoice) => {
    if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        form.delete(route('tenant.invoices.destroy', invoice.id), {
            onSuccess: () => {
                toast.success('Invoice deleted successfully.');
            },
            onError: () => {
                toast.error('There was an error deleting the invoice.');
            },
        });
    }
};



function bulkDelete() {
    if (selectedInvoice.value.length === 0) return;

    if (confirm(`Are you sure you want to delete ${selectedInvoice.value.length} selected invoices?`)) {
        router.delete(route('tenant.invoices.bulk-destroy'), {
            data: { ids: selectedInvoice.value.map(e => e.id) },
            preserveScroll: true,
            onSuccess: () => {
                selectedInvoice.value = [];
                selectAll.value = false;
                toast.success('Selected invoices deleted successfully.');
                router.reload();
            },
            onError: () => {
                toast.error('There was an error deleting the selected invoices.');
            },
        });
    }
}

function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedInvoice.value = props.invoices && props.invoices.data ? [...props.invoices.data] : [];
    } else {
        selectedInvoice.value = [];
    }
}

watch(selectAll, (val) => {
    if (val) {
        selectedInvoice.value = props.invoices && props.invoices.data ? [...props.invoices.data] : [];
    } else {
        selectedInvoice.value = [];
    }
});

watch(selectedInvoice, val => {
    selectAll.value = props.invoices && props.invoices.data && val.length === props.invoices.data.length;
});

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <ReceiptText class="inline w-6 h-6 mr-2 text-cyan-700 dark:text-indigo-400" />
                    Invoices
                </h2>
                <PrimaryButton @click="openModal()" v-if="props.can && props.can.create_invoice">
                    <Plus class="w-5 h-5 mr-2" />
                    Create Invoice
                </PrimaryButton>
            </div>

        </template>

        <div v-if="selectedInvoice.length > 0" class="mb-4 flex items-center justify-between bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded">
            <DangerButton @click="bulkDelete">
                Delete({{ selectedInvoice.length }})
            </DangerButton>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Invoice #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invoice in props.invoices.data" :key="invoice.id" :class="{ 'bg-blue-50 dark:bg-blue-900': selectedInvoice.includes(invoice) }">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" :value="invoice" v-model="selectedInvoice" />    
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ invoice.invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ invoice.customer.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ invoice.amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ invoice.due_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ invoice.status }}
                                        </td>
                                        <td class="relative px-6 py-4 flex gap-2">
                                            <PrimaryButton size="sm" @click="openModal(invoice)">Edit</PrimaryButton>
                                            <DangerButton size="sm" @click="deleteInvoice(invoice)">Delete</DangerButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="modalOpen" @close="closeModal">
            <template #header>
                <h3>{{ editing ? 'Edit Invoice' : 'Create Invoice' }}</h3>
            </template>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <InputLabel for="user_id" value="Customer/User" />
                    <SelectInput
                        id="user_id"
                        v-model="form.user_id"
                        :options="[{ value: '', label: 'Select a user' }, ...((Array.isArray(props.networkUsers) ? props.networkUsers : []).map(user => ({ value: user.id, label: user.full_name })))]"
                        class="mt-1 block w-full"
                        required
                    />
                </div>
                <div class="mb-4">
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" v-model="form.amount" type="number" class="mt-1 block w-full" required />
                </div>
                <div class="mb-4">
                    <InputLabel for="package" value="Package" />
                    <TextInput id="package" v-model="form.package" type="text" class="mt-1 block w-full" required />
                </div>
                <div class="mb-4">
                    <InputLabel for="issued_on" value="Issued On" />
                    <TextInput id="issued_on" v-model="form.issued_on" type="date" class="mt-1 block w-full" required />
                </div>
                <div class="mb-4">
                    <InputLabel for="due_on" value="Due On" />
                    <TextInput id="due_on" v-model="form.due_on" type="date" class="mt-1 block w-full" required />
                </div>
                <div class="mb-4">
                    <InputLabel for="status" value="Status" />
                    <TextInput id="status" v-model="form.status" type="text" class="mt-1 block w-full" required />
                </div>
                <div class="flex justify-end">
                    <PrimaryButton type="submit">{{ editing ? 'Update' : 'Create' }}</PrimaryButton>
                    <DangerButton type="button" class="ml-2" @click="closeModal">Cancel</DangerButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
