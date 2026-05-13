<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Opinie Manager — Trendy" subtitle="Analiza sentymentu i trendów w opiniach" />
    </template>
    <ModuleNav :tabs="tabs" />

    <!-- Summary cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
        <div class="text-xs text-gray-500 mt-1">Wszystkich opinii</div>
      </div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-amber-500">{{ stats.avg_rating?.toFixed(1) || '–' }}</div>
        <div class="text-xs text-gray-500 mt-1">Śr. ocena</div>
      </div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-green-600">{{ stats.sentiment?.positive || 0 }}</div>
        <div class="text-xs text-gray-500 mt-1">Pozytywnych</div>
      </div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-red-500">{{ stats.sentiment?.negative || 0 }}</div>
        <div class="text-xs text-gray-500 mt-1">Negatywnych</div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- By rating -->
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Rozkład ocen</h3>
        <div v-for="r in [5,4,3,2,1]" :key="r" class="flex items-center gap-3 mb-3">
          <div class="w-16 text-sm text-gray-600 flex items-center gap-1">
            <span class="text-amber-400">★</span> {{ r }}
          </div>
          <div class="flex-1 bg-gray-100 rounded-full h-2.5">
            <div class="h-2.5 bg-amber-400 rounded-full transition-all"
              :style="{ width: stats.total > 0 ? ((stats.by_rating?.[r] || 0) / stats.total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ stats.by_rating?.[r] || 0 }}</div>
        </div>
      </div>

      <!-- Sentiment breakdown -->
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Sentyment</h3>
        <div v-for="(count, sentiment) in stats.sentiment" :key="sentiment" class="flex items-center gap-3 mb-3">
          <div class="w-28 text-sm text-gray-600">{{ sentimentLabels[sentiment] || sentiment }}</div>
          <div class="flex-1 bg-gray-100 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all"
              :class="sentimentBarColors[sentiment] || 'bg-gray-400'"
              :style="{ width: stats.total > 0 ? (count / stats.total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ count }}</div>
        </div>

        <!-- Reply rate -->
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-600">Wskaźnik odpowiedzi</span>
            <span class="font-bold text-cyan-600">{{ stats.reply_rate || 0 }}%</span>
          </div>
          <div class="h-2 bg-gray-100 rounded-full">
            <div class="h-2 bg-cyan-500 rounded-full" :style="{ width: (stats.reply_rate || 0) + '%' }"></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

defineProps({ stats: Object })

const tabs = [
  { href: '/reviews',           label: 'Opinie' },
  { href: '/reviews/templates', label: 'Szablony' },
  { href: '/reviews/trends',    label: 'Trendy' },
]

const sentimentLabels = { positive: '😊 Pozytywna', neutral: '😐 Neutralna', negative: '😞 Negatywna' }
const sentimentBarColors = { positive: 'bg-green-500', neutral: 'bg-gray-400', negative: 'bg-red-500' }
</script>
