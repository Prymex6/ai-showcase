<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Raporty AI — Dashboard" subtitle="Analiza wskaźników biznesowych z pomocą AI" />
    </template>
    <ModuleNav :tabs="tabs" />

    <!-- KPI Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div v-for="kpi in goals.slice(0, 4)" :key="kpi.id"
        class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="text-xs text-gray-400 mb-1 uppercase tracking-wide">{{ kpi.name }}</div>
        <div class="text-2xl font-bold text-gray-900">{{ formatValue(kpi.current_value, kpi.unit) }}</div>
        <div class="mt-2">
          <div class="flex justify-between text-xs text-gray-400 mb-1">
            <span>Cel: {{ formatValue(kpi.target_value, kpi.unit) }}</span>
            <span :class="kpi.progress >= 100 ? 'text-green-600 font-bold' : 'text-gray-500'">{{ kpi.progress }}%</span>
          </div>
          <div class="h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 rounded-full transition-all"
              :class="kpi.progress >= 100 ? 'bg-green-500' : kpi.progress >= 60 ? 'bg-cyan-500' : 'bg-amber-400'"
              :style="{ width: Math.min(kpi.progress, 100) + '%' }"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI Insights -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-900">Analiza AI</h3>
        <button @click="generateInsights" :disabled="loading"
          class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 disabled:opacity-50 font-medium">
          {{ loading ? 'Analizuję…' : '✨ Generuj insighty' }}
        </button>
      </div>
      <div v-if="insights" class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ insights }}</div>
      <div v-else class="text-sm text-gray-400 italic">Kliknij "Generuj insighty" aby uzyskać analizę AI na podstawie danych</div>
    </div>

    <!-- Recent reports -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-900 text-sm">Zaplanowane raporty</div>
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Raport</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Częstotliwość</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Odbiorca</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Następny</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="r in schedules" :key="r.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-900">{{ r.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ frequencyLabels[r.frequency] }}</td>
            <td class="px-5 py-3 text-gray-500 text-xs">{{ r.recipients }}</td>
            <td class="px-5 py-3 text-cyan-600 text-xs">{{ r.next_run }}</td>
          </tr>
          <tr v-if="schedules.length === 0">
            <td colspan="4" class="px-5 py-6 text-center text-gray-400">Brak zaplanowanych raportów</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'
import axios from 'axios'

defineProps({ goals: Array, schedules: Array })

const tabs = [
  { href: '/reports',          label: 'Dashboard' },
  { href: '/reports/goals',    label: 'Cele KPI' },
  { href: '/reports/schedule', label: 'Harmonogram' },
]

const frequencyLabels = { daily: 'Codziennie', weekly: 'Co tydzień', monthly: 'Co miesiąc' }
const loading = ref(false)
const insights = ref('')

function formatValue(v, unit) {
  if (unit === 'PLN') return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN', maximumFractionDigits: 0 }).format(v)
  if (unit === '%') return v + '%'
  return v
}

async function generateInsights() {
  loading.value = true
  try {
    const res = await axios.post('/reports/insights')
    insights.value = res.data.insights
  } finally {
    loading.value = false
  }
}
</script>
