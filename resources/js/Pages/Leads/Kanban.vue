<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Lead Manager — Kanban" subtitle="Przeciągaj karty między kolumnami" />
    </template>

    <ModuleNav :tabs="tabs" />

    <div class="flex gap-4 overflow-x-auto pb-4">
      <div v-for="(label, key) in statuses" :key="key" class="flex-shrink-0 w-64">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-sm font-semibold text-gray-700">{{ label }}</span>
          <span class="text-xs bg-gray-200 text-gray-600 rounded-full px-2 py-0.5">
            {{ (groups[key] || []).length }}
          </span>
        </div>

        <div class="space-y-2 min-h-24">
          <div v-for="lead in (groups[key] || [])" :key="lead.id"
            class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm cursor-pointer hover:shadow-md transition">
            <div class="font-medium text-sm text-gray-900 mb-1">{{ lead.name }}</div>
            <div class="text-xs text-gray-500 truncate">{{ lead.company }}</div>
            <div class="flex items-center justify-between mt-2">
              <span class="text-xs text-gray-400">{{ lead.service }}</span>
              <span class="text-xs font-bold text-indigo-600">{{ lead.score }}pts</span>
            </div>
            <div class="mt-2 flex gap-1">
              <button v-for="(lbl, s) in nextStatuses(key)" :key="s"
                @click="move(lead.id, s)"
                class="text-xs px-2 py-0.5 bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 rounded transition">
                → {{ lbl }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

const props = defineProps({ groups: Object, statuses: Object })

const tabs = [
  { href: '/leads',           label: 'Lista' },
  { href: '/leads/kanban',    label: 'Kanban' },
  { href: '/leads/templates', label: 'Szablony maili' },
  { href: '/leads/stats',     label: 'Statystyki' },
]

const statusOrder = ['new', 'contact', 'qualified', 'proposal', 'closed_won', 'closed_lost']

function nextStatuses(current) {
  const idx = statusOrder.indexOf(current)
  return Object.fromEntries(
    statusOrder
      .slice(idx + 1, idx + 3)
      .map(s => [s, props.statuses[s]])
  )
}

function move(id, status) {
  router.patch(`/leads/${id}/status`, { status })
}
</script>
