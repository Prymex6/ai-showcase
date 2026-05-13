<template>
  <div class="flex gap-1 border-b border-gray-200 mb-6">
    <Link
      v-for="tab in tabs"
      :key="tab.href"
      :href="tab.href"
      class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
      :class="isActive(tab.href)
        ? 'border-blue-600 text-blue-600'
        : 'border-transparent text-gray-500 hover:text-gray-700'"
    >
      {{ tab.label }}
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ tabs: Array })
function isActive(href) {
  return window.location.pathname === href || window.location.pathname.startsWith(href + '/')
}
</script>
