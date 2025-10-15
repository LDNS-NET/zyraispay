<script setup>
const props = defineProps({
  links: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <nav
    v-if="links.length > 3"
    class="flex justify-center mt-6"
    aria-label="Pagination"
  >
    <ul class="inline-flex items-center gap-1 rounded-lg shadow-sm bg-white p-1 border border-gray-200">
      <li
        v-for="(link, key) in links"
        :key="key"
      >
        <!-- Disabled state -->
        <span
          v-if="!link.url"
          class="px-3 py-2 min-w-[36px] flex justify-center rounded-md text-gray-400 text-sm cursor-not-allowed select-none"
          v-html="link.label"
        />

        <!-- Active or normal link -->
        <a
          v-else
          :href="link.url"
          class="px-3 py-2 min-w-[36px] flex justify-center rounded-md text-sm font-medium transition-all duration-150"
          :class="{
            'bg-blue-600 text-white shadow-sm': link.active,
            'text-gray-700 hover:bg-gray-100': !link.active,
          }"
          v-html="link.label"
        />
      </li>
    </ul>
  </nav>
</template>
