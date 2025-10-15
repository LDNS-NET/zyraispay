<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useToast } from 'vue-toastification';


const toast = useToast()

const props = defineProps({
    packages: Array,
});

const form = useForm({
    prefix: '',
    length: 10,
    quantity: 50,
    package_id: '',
});

const submitForm = () => {
  form.post(route('tenants.vouchers.store'), {
    onSuccess: () => {
      form.reset()
      toast.success('Vouchers generated successfully')
    },
    onError: () => {
      toast.error('Failed to generate vouchers. Please check the form.')
    }
  })
}

const cancelForm = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Generate Vouchers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generate Vouchers</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submitForm" class="space-y-6">

                            <div>
                                <InputLabel for="prefix" value="Voucher Prefix" />
                                <TextInput id="prefix" v-model="form.prefix" class="mt-1 block w-full" />
                                <InputError :message="form.errors.prefix" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="length" value="Voucher Code Length" />
                                <TextInput id="length" type="number" v-model="form.length" class="mt-1 block w-full" min="4" max="20" />
                                <InputError :message="form.errors.length" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="quantity" value="Number of Vouchers to Generate" />
                                <TextInput id="quantity" type="number" v-model="form.quantity" class="mt-1 block w-full" min="1" />
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="package_id" value="Internet Package" />
                                <select v-model="form.package_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="" disabled>Select Package</option>
                                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                        {{ pkg.name }} — KES {{ pkg.price }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.package_id" class="mt-2" />
                            </div>

                            <div class="flex justify-end">
                                <DangerButton type="button" @click="cancelForm" class="mr-4">Cancel</DangerButton>
                                <PrimaryButton :disabled="form.processing">Generate</PrimaryButton>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
