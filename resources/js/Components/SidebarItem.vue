<template>
  <Link
    :href="href"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
    :class="active
      ? 'bg-blue-600 text-white'
      : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
  >
    <span class="text-base leading-none">{{ icon }}</span>
    <span>{{ label }}</span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ href: String, icon: String, label: String, active: Boolean })
</script>
