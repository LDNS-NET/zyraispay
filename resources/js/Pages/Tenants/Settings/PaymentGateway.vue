<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
const props = defineProps({
  gateways: { type: Array, default: () => [] },
  phone_number: { type: String, default: '' },
})

const initial = (props.gateways && props.gateways.length > 0) ? props.gateways[0] : {};

const form = useForm({
  collection_method: initial.collection_method || 'phone',
  phone_number: initial.phone_number || props.phone_number || '',
  bank_name: initial.bank_name || '',
  bank_account: initial.bank_account || '',
  till_number: initial.till_number || '',
  paybill_business_number: initial.paybill_business_number || '',
  paybill_account_number: initial.paybill_account_number || '',
})

const save = () => {
  // Set provider and payout_method based on collection_method
  switch (form.collection_method) {
    case 'phone':
      form.provider = 'mpesa';
      form.payout_method = 'mpesa_phone';
      break;
    case 'bank':
      form.provider = 'bank';
      form.payout_method = 'bank';
      break;
    case 'mpesa_till':
      form.provider = 'mpesa';
      form.payout_method = 'till';
      break;
    case 'mpesa_paybill':
      form.provider = 'mpesa';
      form.payout_method = 'paybill';
      break;
    default:
      form.provider = 'custom';
      form.payout_method = '';
  }
  form.post(route('settings.payment_gateway.update'), {
    preserveScroll: true,
  })
}
function readableMethod(gateway) {
  if (!gateway) return '';
  switch (gateway.payout_method) {
    case 'mpesa_phone': return 'Mpesa Phone Number';
    case 'bank': return 'Bank';
    case 'till': return 'Mpesa Till';
    case 'paybill': return 'Mpesa Paybill';
    default: return (gateway.provider || '').charAt(0).toUpperCase() + (gateway.provider || '').slice(1);
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h3 class="text-xl font-bold mb-4">Tenant Payment Collection Details</h3>
    <div v-if="props.gateways && props.gateways.length > 0" class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
      <div class="font-semibold mb-2">Current Saved Gateway:</div>
      <div>
        <span class="font-medium">Method:</span> {{ readableMethod(props.gateways[0]) }}
      </div>
      <div v-if="props.gateways[0].phone_number">
        <span class="font-medium">Phone Number:</span> {{ props.gateways[0].phone_number }}
      </div>
      <div v-if="props.gateways[0].bank_name">
        <span class="font-medium">Bank:</span> {{ props.gateways[0].bank_name }}
      </div>
      <div v-if="props.gateways[0].bank_account">
        <span class="font-medium">Bank Account:</span> {{ props.gateways[0].bank_account }}
      </div>
      <div v-if="props.gateways[0].till_number">
        <span class="font-medium">Mpesa Till:</span> {{ props.gateways[0].till_number }}
      </div>
      <div v-if="props.gateways[0].paybill_business_number">
        <span class="font-medium">Paybill Business #:</span> {{ props.gateways[0].paybill_business_number }}
      </div>
      <div v-if="props.gateways[0].paybill_account_number">
        <span class="font-medium">Paybill Account #:</span> {{ props.gateways[0].paybill_account_number }}
      </div>
    </div>
    <form @submit.prevent="save">
      <div class="mb-4">
        <label class="block font-medium mb-1">Collection Method</label>
        <select v-model="form.collection_method" class="input input-bordered w-full">
          <option value="phone">Phone Number</option>
          <option value="bank">Bank</option>
          <option value="mpesa_till">Mpesa Till</option>
          <option value="mpesa_paybill">Mpesa Paybill</option>
        </select>
      </div>
      <div v-if="form.collection_method === 'phone'" class="mb-4">
        <label class="block font-medium mb-1">Phone Number</label>
        <input v-model="form.phone_number" class="input input-bordered w-full" />
      </div>
      <div v-if="form.collection_method === 'bank'" class="mb-4">
        <label class="block font-medium mb-1">Bank Name</label>
        <select v-model="form.bank_name" class="input input-bordered w-full">
          <option value="">Select Bank</option>
          <option value="equity">Equity</option>
          <option value="cooperative">Cooperative</option>
          <option value="kcb">KCB</option>
        </select>
        <label class="block font-medium mb-1 mt-2">Bank Account</label>
        <input v-model="form.bank_account" class="input input-bordered w-full" />
      </div>
      <div v-if="form.collection_method === 'mpesa_till'" class="mb-4">
        <label class="block font-medium mb-1">Mpesa Till Number</label>
        <input v-model="form.till_number" class="input input-bordered w-full" />
      </div>
      <div v-if="form.collection_method === 'mpesa_paybill'" class="mb-4">
        <label class="block font-medium mb-1">Paybill Business Number</label>
        <input v-model="form.paybill_business_number" class="input input-bordered w-full" />
        <label class="block font-medium mb-1 mt-2">Paybill Account Number</label>
        <input v-model="form.paybill_account_number" class="input input-bordered w-full" />
      </div>
      <div class="mt-6 flex justify-end">
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</template>
