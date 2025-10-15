<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { Trash, Eye, Pencil, ListCheck, List } from "lucide-vue-next";

// Basic UI Components
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Modal from "@/Components/Modal.vue";
import DangerButton from "@/Components/DangerButton.vue"; // Good to have for "Cancel"
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    vouchers: Object, // Paginated list of vouchers
    voucherCount: Number,
    creating: { type: Boolean, default: false }, // From controller: true if creating
    flash: Object, // To display success/error messages
});

// --- UI State Management ---
// Initialize showFormModal directly from props.creating
const showFormModal = ref(false); // Start false, let the watcher control it
const showFlash = ref(
    props.flash?.success || props.flash?.error ? true : false
);
const selected = ref([]);
const selectAll = ref(false);

//selection
const toggleSelectAll = () => {
    if (selectAll.value) {
        selected.value = props.vouchers.data.map((v) => v.id);
    } else {
        selected.value = [];
    }
};

//bulk deletion
const bulkDelete = () => {
    if (selected.value.length === 0) return;

    if (confirm("Are you sure you want to delete selected vouchers?")) {
        router.delete(route("tenants.vouchers.bulk-delete"), {
            data: { ids: selected.value },
            preserveScroll: true,
            onSuccess: () => {
                selected.value = [];
                selectAll.value = false;
                toast.success("Vouchers deleted successfully");
            },
        });
    }
};

const confirmVoucherDeletion = (id) => {
    if (confirm("Are you sure you want to delete this voucher?")) {
        router.delete(route("tenants.vouchers.destroy", id), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Voucher deleted successfully");
            },
        });
    }
};

// --- Form State Management for Create ---
const form = useForm({
    code: "",
    name: "",
    value: "",
    type: "fixed",
    usage_limit: "",
    expires_at: "",
    is_active: true,
    note: "", // Ensure 'note' is included if you plan to use it in the form
});

// --- Helper Functions ---
const resetForm = () => {
    form.reset();
    form.clearErrors(); // Clear any previous errors
};

const closeFormModal = () => {
    showFormModal.value = false;
    router.get(
        route("tenants.vouchers.index"),
        {},
        {
            replace: true, // Clean URL (remove ?create=true)
            preserveState: true,
            preserveScroll: true,
        }
    );
    // It's crucial to reset the form AFTER the Inertia visit finishes,
    // or when the modal is definitively closed and the new page renders.
    // For a modal on the same page, resetting it here is fine.
    resetForm();
};

const submitForm = () => {
    form.post(route("tenants.vouchers.store"), {
        onSuccess: () => {
            closeFormModal();
            toast.success("Voucher created successfully");
        },
        onError: () => {
            toast.error("Failed to create, check your form");
            // useForm automatically populates form.errors
            // The modal will stay open with errors
        },
        onFinish: () => {
            // Optional: If you want to reset processing state or do final cleanup
        },
    });
};

// Function to format date
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// --- Watchers ---
// Watch for changes in `props.creating` to open/close modal and reset form
watch(
    () => props.creating,
    (newCreatingValue) => {
        if (newCreatingValue) {
            // Only reset form and show modal if it's truly entering the 'create' state
            resetForm();
            showFormModal.value = true;
        } else {
            showFormModal.value = false;
        }
    },
    { immediate: true }
); // `immediate: true` runs the watch on component mount

const openCreateModal = () => {
    // This is the correct way to open the modal via Inertia and query parameter
    router.get(route("tenants.vouchers.index", { create: true }), {
        preserveScroll: true, // Keep scroll position
        preserveState: true, // Keep component state (important for modal)
    });
};
</script>

<template>
    <Head title="Vouchers" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex items-center gap-3 text-3xl font-extrabold text-gray-800 leading-tight"
            >
                <List class="w-7 h-7 gap-3 flex text-blue-600" /> Vouchers
            </div>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Bulk Delete & Create -->
                    <div class="flex justify-between items-center mb-6">
                        <!-- ✅ Only show bulk delete if at least one voucher is selected -->
                        <div v-if="selected.length > 0">
                            <PrimaryButton
                                class="bg-red-900"
                                @click="bulkDelete"
                            >
                                <Trash class="w-5 h-5 text-red-600" />
                                <span
                                    >Bulk Delete
                                    <span class="text-sm text-white"
                                        >({{ selected.length }})</span
                                    ></span
                                >
                            </PrimaryButton>
                        </div>

                        <Link :href="route('tenants.vouchers.create')">
                            <PrimaryButton
                                class="bg-green-500 hover:bg-green-700"
                                @click="openCreateModal"
                            >
                                New Voucher
                            </PrimaryButton>
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">
                                        <Checkbox
                                            v-model:checked="selectAll"
                                            @change="toggleSelectAll"
                                        />
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Code
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Value
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Type
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Usage Limit
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Expires At
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Active
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="voucher in vouchers.data"
                                    :key="voucher.id"
                                >
                                    <td class="px-6 py-4">
                                        <Checkbox
                                            :value="voucher.id"
                                            v-model:checked="selected"
                                        />
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ voucher.code }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ voucher.value }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ voucher.type }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ voucher.usage_limit || "Unlimited" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(voucher.expires_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ voucher.is_active ? "Yes" : "No" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'tenants.vouchers.show',
                                                    voucher.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900 mr-2"
                                            ><Eye
                                                class="m-5 h-5 inline-block align-middle"
                                            />
                                        </Link>
                                        <!--<Link :href="route('tenants.vouchers.edit', voucher.id)" class="text-blue-600 hover:text-blue-900 mr-2"><Pencil class="w-5 h-5 text-green-800 inline-block align-middle"/> </Link> -->
                                        <button
                                            @click="
                                                confirmVoucherDeletion(
                                                    voucher.id
                                                )
                                            "
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <Trash
                                                class="w-5 h-5 inline-block align-middle"
                                            />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="vouchers.data.length === 0">
                                    <td
                                        colspan="8"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"
                                    >
                                        No vouchers found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ✅ Pagination (only show when more than one page exists) -->
                    <div
                        v-if="vouchers.total > vouchers.per_page"
                        class="mt-4 flex justify-end"
                    >
                        <template
                            v-for="link in vouchers.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-4 py-2 text-sm leading-5 font-medium rounded-md focus:outline-none transition ease-in-out duration-150"
                                :class="{
                                    'bg-indigo-600 text-white': link.active,
                                    'text-gray-700 hover:bg-gray-200':
                                        !link.active,
                                }"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span
                                v-else
                                class="px-4 py-2 text-sm leading-5 font-medium rounded-md text-gray-400 cursor-not-allowed"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showFormModal" @close="closeFormModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Create New Voucher
                </h2>

                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="code" value="Voucher Code" />
                            <TextInput
                                id="code"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.code"
                                required
                                autofocus
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.code"
                            />
                        </div>

                        <div>
                            <InputLabel for="name" value="Voucher Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <div>
                            <InputLabel for="value" value="Value" />
                            <TextInput
                                id="value"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="form.value"
                                required
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.value"
                            />
                        </div>

                        <div>
                            <InputLabel for="type" value="Type" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="fixed">
                                    Fixed Amount (e.g., KES 100)
                                </option>
                                <option value="percentage">
                                    Percentage (e.g., 10%)
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.type"
                            />
                        </div>

                        <div>
                            <InputLabel
                                for="usage_limit"
                                value="Usage Limit (Optional)"
                            />
                            <TextInput
                                id="usage_limit"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.usage_limit"
                                min="1"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.usage_limit"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Leave empty for unlimited usage.
                            </p>
                        </div>

                        <div>
                            <InputLabel
                                for="expires_at"
                                value="Expires At (Optional)"
                            />
                            <TextInput
                                id="expires_at"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.expires_at"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.expires_at"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Leave empty for no expiry date.
                            </p>
                        </div>

                        <div class="md:col-span-2 flex items-center mt-4">
                            <Checkbox
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <InputLabel for="is_active" class="ml-2"
                                >Is Active</InputLabel
                            >
                            <InputError
                                class="mt-2"
                                :message="form.errors.is_active"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <DangerButton
                            type="button"
                            @click="closeFormModal"
                            class="mr-4"
                        >
                            Cancel
                        </DangerButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Create Voucher
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
