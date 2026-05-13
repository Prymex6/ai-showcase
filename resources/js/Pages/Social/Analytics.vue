<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Social Planner — Analityka" subtitle="Statystyki aktywności w mediach społecznościowych" />
    </template>
    <ModuleNav :tabs="tabs" />

    <!-- Summary cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div v-for="card in summaryCards" :key="card.label" class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold" :class="card.color">{{ card.value }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ card.label }}</div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- By platform -->
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Posty wg platformy</h3>
        <div v-for="(count, platform) in stats.by_platform" :key="platform" class="flex items-center gap-3 mb-3">
          <div class="w-28 text-sm text-gray-600 flex items-center gap-2">
            <span>{{ platformIcons[platform] || '📢' }}</span>{{ platform }}
          </div>
          <div class="flex-1 bg-gray-100 rounded-full h-2">
            <div class="h-2 bg-violet-500 rounded-full"
              :style="{ width: stats.total > 0 ? (count / stats.total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ count }}</div>
        </div>
      </div>

      <!-- By status -->
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Posty wg statusu</h3>
        <div v-for="(count, status) in stats.by_status" :key="status" class="flex items-center gap-3 mb-3">
          <div class="w-28 text-sm text-gray-600 capitalize">{{ statusLabels[status] || status }}</div>
          <div class="flex-1 bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full" :class="statusBarColors[status] || 'bg-gray-400'"
              :style="{ width: stats.total > 0 ? (count / stats.total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ count }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

const props = defineProps({ stats: Object })

const tabs = [
  { href: '/social',           label: 'Kalendarz' },
  { href: '/social/templates', label: 'Szablony' },
  { href: '/social/analytics', label: 'Analityka' },
]

const platformIcons = { Facebook: '📘', Instagram: '📸', LinkedIn: '💼', Twitter: '🐦' }
const statusLabels = { draft: 'Szkic', scheduled: 'Zaplanowany', published: 'Opublikowany', failed: 'Błąd' }
const statusBarColors = { draft: 'bg-gray-400', scheduled: 'bg-blue-500', published: 'bg-green-500', failed: 'bg-red-500' }

const summaryCards = computed(() => [
  { label: 'Wszystkich postów', value: props.stats.total || 0, color: 'text-violet-600' },
  { label: 'Opublikowanych', value: props.stats.by_status?.published || 0, color: 'text-green-600' },
  { label: 'Zaplanowanych', value: props.stats.by_status?.scheduled || 0, color: 'text-blue-600' },
  { label: 'Szkice', value: props.stats.by_status?.draft || 0, color: 'text-gray-500' },
])
</script>
