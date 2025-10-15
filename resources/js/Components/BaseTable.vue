<script setup>
defineProps({
  headers: {
    type: Array,
    required: true,
    // Example: [{ key: 'code', label: 'Code', class: 'w-1/4' }]
  },
  rows: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  rowKey: {
    type: String,
    default: null,
  },
});
</script>

<template>
  <div class="relative overflow-x-auto border border-gray-200 rounded-xl shadow-sm">
    <table class="min-w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th
            v-for="(header, index) in headers"
            :key="index"
            class="px-4 py-3 whitespace-nowrap font-semibold"
            :class="header.class || ''"
          >
            {{ header.label }}
          </th>
        </tr>
      </thead>

      <tbody v-if="!loading && rows.length > 0">
        <tr
          v-for="(row, rowIndex) in rows"
          :key="rowKey ? row[rowKey] : rowIndex"
          class="border-t hover:bg-gray-50 transition"
        >
          <td
            v-for="(header, colIndex) in headers"
            :key="colIndex"
            class="px-4 py-3"
          >
            <!-- Custom slot if provided -->
            <slot
              v-if="$slots[header.key]"
              :name="header.key"
              :row="row"
              :value="row[header.key]"
            />
            <template v-else>
              {{ row[header.key] }}
            </template>
          </td>
        </tr>
      </tbody>

      <!-- Empty State -->
      <tbody v-if="!loading && rows.length === 0">
        <tr>
          <td :colspan="headers.length" class="text-center p-4 text-gray-500">
            No records found.
          </td>
        </tr>
      </tbody>

      <!-- Loading State -->
      <tbody v-if="loading">
        <tr>
          <td :colspan="headers.length" class="text-center p-4 text-gray-500">
            Loading...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
