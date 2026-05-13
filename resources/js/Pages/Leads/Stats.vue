<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Lead Manager — Statystyki" subtitle="Analiza konwersji i efektywności" />
    </template>

    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <StatCard label="Wszystkie leady" :value="total" color="indigo" />
      <StatCard label="Wygrane" :value="wonCount" color="green" />
      <StatCard label="Śr. score" :value="avgScore" color="blue" />
      <StatCard label="Konwersja" :value="conversionRate + '%'" color="purple" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Wg statusu</h3>
        <div v-for="(count, status) in byStatus" :key="status" class="flex items-center gap-3 mb-3">
          <div class="w-28 text-sm text-gray-600 truncate">{{ statusLabels[status] || status }}</div>
          <div class="flex-1 bg-gray-100 rounded-full h-2">
            <div class="h-2 bg-indigo-500 rounded-full transition-all"
              :style="{ width: total > 0 ? (count / total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ count }}</div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Wg usługi</h3>
        <div v-for="(count, service) in byService" :key="service" class="flex items-center gap-3 mb-3">
          <div class="w-32 text-sm text-gray-600 truncate">{{ service || 'Nieokreślona' }}</div>
          <div class="flex-1 bg-gray-100 rounded-full h-2">
            <div class="h-2 bg-purple-500 rounded-full transition-all"
              :style="{ width: total > 0 ? (count / total * 100) + '%' : '0%' }"></div>
          </div>
          <div class="w-6 text-sm font-bold text-gray-700 text-right">{{ count }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

defineProps({ total: Number, byStatus: Object, byService: Object, avgScore: Number, wonCount: Number, conversionRate: Number })

const tabs = [
  { href: '/leads',           label: 'Lista' },
  { href: '/leads/kanban',    label: 'Kanban' },
  { href: '/leads/templates', label: 'Szablony maili' },
  { href: '/leads/stats',     label: 'Statystyki' },
]

const statusLabels = {
  new: 'Nowy', contact: 'Kontakt', qualified: 'Kwalifikowany',
  proposal: 'Propozycja', closed_won: 'Wygrany', closed_lost: 'Przegrany',
}

const StatCard = {
  props: ['label', 'value', 'color'],
  template: `
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
      <div class="text-2xl font-bold text-gray-900">{{ value }}</div>
      <div class="text-sm text-gray-500 mt-0.5">{{ label }}</div>
    </div>`
}
</script>
