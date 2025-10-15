<template>
  <div
    class="bg-gradient-to-br from-green-100 via-white to-cyan-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900 border border-cyan-100 dark:border-indigo-800 shadow-xl rounded-2xl p-6 flex flex-col gap-3 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-700 dark:text-white">{{ title }}</h3>
      <!--<span
        class="px-2 py-0.5 text-xs uppercase font-bold bg-cyan-100 text-cyan-700 dark:bg-indigo-700 dark:text-white rounded shadow">
        {{ type }}
      </span>-->
    </div>
    <apexchart :options="options" :series="series" :type="type" :height="chartHeight" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ApexCharts from 'vue3-apexcharts';

const props = defineProps({
  title: String,
  labels: Array,
  values: Array,
  type: {
    type: String,
    default: 'bar',
  },
});

const chartHeight = computed(() => {
  // If values are empty or all zero/null, use a minimal height (prevents negative height rendering)
  if (!props.values || !props.values.length || props.values.every(v => v === 0 || v === null)) {
    return 120;
  }
  return 280;
});

const options = computed(() => ({
  chart: {
    toolbar: { show: false },
    foreColor: '#64748B', // default light gray
  },
  ...(props.type !== 'donut' ? {
    xaxis: {
      categories: props.labels,
      labels: {
        style: {
          colors: ['#6B7280'], // text color
        },
      },
    }
  } : {}),
  ...(props.type === 'donut' ? {
    labels: props.labels
  } : {}),
  dataLabels: { enabled: false },
  theme: {
    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
  },
  grid: {
    borderColor: '#E5E7EB', // soft gray for light mode
  },
}));

const series = computed(() => props.type === 'donut' ? props.values : [{ name: 'Data', data: props.values }]);
</script>
