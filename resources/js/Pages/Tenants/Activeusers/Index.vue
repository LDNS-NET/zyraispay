<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useToast } from 'vue-toastification';
import { Activity } from 'lucide-vue-next';

const toast = useToast()

const props = defineProps({
    activeUsers: Object,
    filters: Object,
    counts: Object,
})

const showModal = ref(false);
const viewing = ref( null);

const form = useForm({
    username: '',
    user_type: '',
    ip_mac_address: '',
    session_start: '',
    session_end: '',
});

const userTypes = [
    { label: 'All', value: 'all' },
    { label: 'Hotspot', value: 'hotspot' },
    { label: 'PPPoE', value: 'pppoe' },
    { label: 'Static', value: 'static' }
]
const selectedType = ref('all')

const usersArray = computed(() => {
    return Array.isArray(props.activeUsers) ? props.activeUsers : props.activeUsers.data ?? []
})

const userCounts = computed(() => {
    const arr = usersArray.value
    return {
        all: arr.length,
        hotspot: arr.filter(u => (u.user_type || '').toLowerCase() === 'hotspot').length,
        pppoe: arr.filter(u => (u.user_type || '').toLowerCase() === 'pppoe').length,
        static: arr.filter(u => (u.user_type || '').toLowerCase() === 'static').length,
    }
})

const filteredUsers = computed(() => {
    if (selectedType.value === 'all') return usersArray.value
    return usersArray.value.filter(user => (user.user_type || '').toLowerCase() === selectedType.value)
})

const emptyMessage = computed(() => {
    switch (selectedType.value) {
        case 'hotspot':
            return 'No active hotspot users';
        case 'pppoe':
            return 'No active PPPoE users';
        case 'static':
            return 'No active static users';
        default:
            return 'No active users';
    }
})

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 flex items-center gap-2 leading-tight">
                <Activity class="w-6 h-6 text-green-600" />
                Active Users
            </h2>
        </template>

        <div class="py-6 px-6 lg:px-8">
            <!-- Filter Buttons with Counts -->
            <div class="mb-4 flex gap-2">
                <button
                    v-for="type in userTypes"
                    :key="type.value"
                    :class="['px-4 py-2 rounded flex items-center gap-2', selectedType === type.value ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
                    @click="selectedType = type.value"
                >
                    <span>{{ type.label }}</span>
                    <span class="ml-1 px-2 py-1 rounded bg-gray-300 text-gray-700 text-xs font-semibold">{{ userCounts[type.value] }}</span>
                </button>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP / MAC Address</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Start</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session End</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template v-if="filteredUsers.length === 0">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    {{ emptyMessage }}
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr v-for="user in filteredUsers" :key="user.id ?? user.username">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.username }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.user_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ user.ip }}</div>
                                    <div class="text-xs text-gray-500">{{ user.mac }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.session_start }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.session_end }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>