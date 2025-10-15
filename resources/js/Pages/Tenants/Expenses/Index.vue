<script setup>

import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { format } from 'date-fns';
import Button from '@/Components/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SelectInput from '@/Components/SelectInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from 'vue-toastification';
import { BanknoteArrowDown, Eye, EyeOff, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Card from '@/Components/Card.vue';

const toast = useToast();
const props = defineProps({
    expenses: Object,
    filters: Object,
});

function openEditModal(expense) {
    openModal(expense);
}


const showAmounts = ref(false);
const showWeekly = ref(false);
const showMonthly = ref(false);
const showYearly = ref(false);


// Expense summary cards
const allExpenses = computed(() => props.expenses.allData ?? []);
const weeklyExpense = computed(() => {
    if (!allExpenses.value.length) return 'Ksh 0.00';
    const now = new Date();
    const startOfWeek = new Date(now);
    startOfWeek.setDate(now.getDate() - now.getDay());
    let total = 0;
    allExpenses.value.forEach(e => {
        const date = new Date(e.incurred_on);
        if (date >= startOfWeek && date <= now) {
            total += Number(e.amount);
        }
    });
    return `Ksh ${total.toFixed(2)}`;
});

const monthlyExpense = computed(() => {
    if (!allExpenses.value.length) return 'Ksh 0.00';
    const now = new Date();
    const month = now.getMonth();
    const year = now.getFullYear();
    let total = 0;
    allExpenses.value.forEach(e => {
        const date = new Date(e.incurred_on);
        if (date.getMonth() === month && date.getFullYear() === year) {
            total += Number(e.amount);
        }
    });
    return `Ksh ${total.toFixed(2)}`;
});

const yearlyExpense = computed(() => {
    if (!allExpenses.value.length) return 'Ksh 0.00';
    const year = new Date().getFullYear();
    let total = 0;
    allExpenses.value.forEach(e => {
        const date = new Date(e.incurred_on);
        if (date.getFullYear() === year) {
            total += Number(e.amount);
        }
    });
    return `Ksh ${total.toFixed(2)}`;
});

const editing = ref(null);
const showModal = ref(false);
const selectedExpense = ref([]);
const selectAll = ref(false);

const categories = [
    { label: 'Utilities', value: 'Utilities' },
    { label: 'Maintenance', value: 'Maintenance' },
    { label: 'Supplies', value: 'Supplies' },
    { label: 'System Renewal', value: 'System Renewal' },
    { label: 'Other', value: 'Other' },
];

const form = useForm({
    description: '',
    amount: '',
    incurred_on: format(new Date(), 'yyyy-MM-dd'),
    category: '',
});

function openModal(expense = null) {
    if (expense) {
        editing.value = expense.id;
        form.description = expense.description ?? '';
        form.amount = expense.amount ?? '';
        form.incurred_on = expense.incurred_on ?? format(new Date(), 'yyyy-MM-dd');
        form.category = expense.category ?? '';
    } else {
        editing.value = null;
        form.description = '';
        form.amount = '';
        form.incurred_on = format(new Date(), 'yyyy-MM-dd');
        form.category = '';
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    editing.value = null;
}

function submit() {
    if (editing.value) {
        form.put(route('tenants.expenses.update', editing.value), {
            onSuccess: () => {
                toast.success('Expense updated successfully');
                closeModal();
            },
            onError: () => {
                toast.error('Failed to update expense');
            },
        });
    } else {
        form.post(route('tenants.expenses.store'), {
            onSuccess: () => {
                toast.success('Expense added successfully');
                closeModal();
            },
            onError: () => {
                toast.error('Failed to add expense');
            },
        });
    }
}

function deleteExpense(expenseId) {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(route('tenants.expenses.destroy', expenseId), {
            onSuccess: () => toast.success('Expense deleted successfully'),
            onError: () => toast.error('Failed to delete expense'),
        });
    }
}

function bulkDelete() {
    if (selectedExpense.value.length === 0) return;
    if (confirm(`Are you sure you want to delete ${selectedExpense.value.length} selected expenses?`)) {
    router.delete(route('tenants.expenses.bulk-delete'), {
            data: { ids: selectedExpense.value.map(e => e.id) },
            preserveScroll: true,
            onSuccess: () => {
                selectedExpense.value = [];
                selectAll.value = false;
                toast.success('Selected expenses deleted successfully');
                router.reload();
            },
        });
    }
}

function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedExpense.value = props.expenses && props.expenses.data ? [...props.expenses.data] : [];
    } else {
        selectedExpense.value = [];
    }
}

watch(selectAll, (val) => {
    if (val) {
        selectedExpense.value = props.expenses && props.expenses.data ? [...props.expenses.data] : [];
    } else {
        selectedExpense.value = [];
    }
});

watch(selectedExpense, val => {
    selectAll.value = props.expenses && props.expenses.data ? val.length === props.expenses.data.length : false;
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <BanknoteArrowDown class="inline w-6 h-6 text-gray-600 dark:text-gray-300 mr-1" />
                    Expenses
                </h2>
                <PrimaryButton @click="openModal">
                    <Plus class="inline w-5 h-5 mr-1" />
                    Add Expense
                </PrimaryButton>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                
                <Card
                    title="Weekly Expense"
                    subtitle="Total for this week"
                    :icon="BanknoteArrowDown"
                >
                    <template #value>
                        <div class="flex items-center justify-between">
                            <span>{{ showWeekly ? weeklyExpense : '******' }}</span>
                            <button @click="showWeekly = !showWeekly" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition">
                                <component :is="showWeekly ? Eye : EyeOff" class="w-5 h-5" />
                            </button>
                        </div>
                    </template>
                </Card>
                <Card
                    title="Monthly Expense"
                    subtitle="Total for this month"
                    :icon="BanknoteArrowDown"
                >
                    <template #value>
                        <div class="flex items-center justify-between">
                            <span>{{ showMonthly ? monthlyExpense : '******' }}</span>
                            <button @click="showMonthly = !showMonthly" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition">
                                <component :is="showMonthly ? Eye : EyeOff" class="w-5 h-5" />
                            </button>
                        </div>
                    </template>
                </Card>
                <Card
                    title="Yearly Expense"
                    subtitle="Total for this year"
                    :icon="BanknoteArrowDown"
                >
                    <template #value>
                        <div class="flex items-center justify-between">
                            <span>{{ showYearly ? yearlyExpense : '******' }}</span>
                            <button @click="showYearly = !showYearly" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white transition">
                                <component :is="showYearly ? Eye : EyeOff" class="w-5 h-5" />
                            </button>
                        </div>
                    </template>
                </Card>
            </div>
        </template>

        <div v-if="selectedExpense.length > 0" class="mb-4 flex items-center justify-between bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded">
            <DangerButton @click="bulkDelete">
                Delete({{ selectedExpense.length }})
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Incurred On</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="expense in expenses.data" :key="expense.id" :class="{ 'bg-blue-50 dark:bg-blue-900': selectedExpense.includes(expense) }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="expense" v-model="selectedExpense" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ expense.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Ksh {{ Number(expense.amount).toFixed(2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ new Date(expense.incurred_on).toLocaleDateString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ expense.category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex gap-2 justify-end">
                                        <button @click="openEditModal(expense)" class="group relative text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                            <Pencil class="w-5 h-5" />
                                            <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-1 px-2 py-1 text-xs rounded bg-gray-800 text-white opacity-0 group-hover:opacity-100 transition pointer-events-none">Edit</span>
                                        </button>
                                        <button @click="deleteExpense(expense.id)" class="group relative text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                                            <Trash2 class="w-5 h-5" />
                                            <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-1 px-2 py-1 text-xs rounded bg-gray-800 text-white opacity-0 group-hover:opacity-100 transition pointer-events-none">Delete</span>
                                        </button>
                                    </td>
                                </tr>   
                                <tr v-if="expenses && expenses.data && expenses.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">No expenses found.</td>
                                </tr>   
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="expenses && expenses.links ? expenses.links : []" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Modal :show="showModal" @close="closeModal">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            {{ editing ? 'Edit Expense' : 'Add Expense' }}
        </h3>
        <form id="expense-form" @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="description" value="Description" />
                <TextInput id="description" v-model="form.description" type="text" class="mt-1 block w-full" required autofocus />
                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
            </div>
            <div>
                <InputLabel for="amount" value="Amount" />
                <TextInput id="amount" v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full" required />
                <div v-if="form.errors.amount" class="text-red-600 text-sm mt-1">{{ form.errors.amount }}</div>
            </div>
            <div>
                <InputLabel for="incurred_on" value="Incurred On" />
                <TextInput id="incurred_on" v-model="form.incurred_on" type="date" class="mt-1 block w-full" required />
                <div v-if="form.errors.incurred_on" class="text-red-600 text-sm mt-1">{{ form.errors.incurred_on }}</div>
            </div>
            <div>
                <InputLabel for="category" value="Category" />
                <SelectInput id="category" v-model="form.category" :options="categories" required />
                <div v-if="form.errors.category" class="text-red-600 text-sm mt-1">{{ form.errors.category }}</div> 
            </div>
            <div class="flex justify-end mt-6">
                <Button @click="closeModal" type="button">Cancel</Button>
                <Button type="submit" form="expense-form" class="ml-2">Save</Button>
            </div>
        </form>
    </Modal>


    </AuthenticatedLayout>
</template>