<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Define the prop this component expects
const props = defineProps({
    voucher: Object, // The single voucher object passed from the controller
});

// Helper to format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US');
};

</script>

<template>
    <Head :title="`Voucher: ${voucher.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Voucher Details: {{ voucher.code }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold mb-4">Voucher Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-600"><strong>Code:</strong></p>
                                    <p class="text-gray-900">{{ voucher.code }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Name:</strong></p>
                                    <p class="text-gray-900">{{ voucher.name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Value:</strong></p>
                                    <p class="text-gray-900">{{ voucher.value }} {{ voucher.type === 'percentage' ? '%' : 'KES' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Type:</strong></p>
                                    <p class="text-gray-900">{{ voucher.type }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Usage Limit:</strong></p>
                                    <p class="text-gray-900">{{ voucher.usage_limit || 'Unlimited' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Expires At:</strong></p>
                                    <p class="text-gray-900">{{ formatDate(voucher.expires_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Active:</strong></p>
                                    <p class="text-gray-900">{{ voucher.is_active ? 'Yes' : 'No' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Status:</strong></p>
                                    <p class="text-gray-900">{{ voucher.status }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-600"><strong>Note:</strong></p>
                                    <p class="text-gray-900">{{ voucher.note || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Created By:</strong></p>
                                    <p class="text-gray-900">{{ voucher.creator?.name || 'Unknown' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600"><strong>Created At:</strong></p>
                                    <p class="text-gray-900">{{ formatDate(voucher.created_at) }}</p>
                                </div>
                                <div v-if="voucher.sent_to">
                                    <p class="text-gray-600"><strong>Sent To:</strong></p>
                                    <p class="text-gray-900">{{ voucher.recipient?.name || 'Unknown' }}</p>
                                </div>
                                <div v-if="voucher.sent_at">
                                    <p class="text-gray-600"><strong>Sent At:</strong></p>
                                    <p class="text-gray-900">{{ formatDate(voucher.sent_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <Link :href="route('tenants.vouchers.index')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                                Back to List
                            </Link>
                           <!-- <Link :href="route('tenants.vouchers.edit', voucher.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit Voucher
                            </Link>-->
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
