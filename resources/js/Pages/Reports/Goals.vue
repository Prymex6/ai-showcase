<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Raporty AI — Cele KPI" subtitle="Definiuj i śledź kluczowe wskaźniki biznesowe">
        <button @click="showForm = true" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">+ Nowy cel</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="space-y-4">
      <div v-for="goal in goals" :key="goal.id" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ goal.name }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ periodLabels[goal.period] }} · {{ goal.unit }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="openEdit(goal)" class="text-xs text-cyan-600 border border-cyan-200 px-2 py-1 rounded-lg hover:bg-cyan-50">Edytuj</button>
            <button @click="deleteGoal(goal.id)" class="text-xs text-red-400 border border-red-100 px-2 py-1 rounded-lg hover:bg-red-50">✕</button>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Aktualnie: <strong class="text-gray-900">{{ formatValue(goal.current_value, goal.unit) }}</strong></span>
              <span>Cel: <strong class="text-gray-900">{{ formatValue(goal.target_value, goal.unit) }}</strong></span>
              <span :class="goal.progress >= 100 ? 'text-green-600 font-bold' : ''">{{ goal.progress }}%</span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full">
              <div class="h-2.5 rounded-full transition-all"
                :class="goal.progress >= 100 ? 'bg-green-500' : goal.progress >= 60 ? 'bg-cyan-500' : 'bg-amber-400'"
                :style="{ width: Math.min(goal.progress, 100) + '%' }"></div>
            </div>
          </div>
          <div class="text-2xl font-bold ml-4" :class="goal.progress >= 100 ? 'text-green-500' : 'text-gray-300'">
            {{ goal.progress >= 100 ? '✓' : '' }}
          </div>
        </div>
      </div>
      <div v-if="goals.length === 0" class="text-center py-12 text-gray-400">Brak zdefiniowanych celów KPI</div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">{{ editGoal ? 'Edytuj cel' : 'Nowy cel KPI' }}</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <input v-model="form.name" placeholder="Nazwa wskaźnika *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500 block mb-1">Wartość aktualna</label>
              <input v-model.number="form.current_value" type="number" step="0.01"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
            <div>
              <label class="text-xs text-gray-500 block mb-1">Cel *</label>
              <input v-model.number="form.target_value" type="number" step="0.01" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <select v-model="form.unit" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="PLN">PLN</option>
              <option value="%">%</option>
              <option value="szt">szt</option>
              <option value="h">h</option>
            </select>
            <select v-model="form.period" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="monthly">Miesięczny</option>
              <option value="quarterly">Kwartalny</option>
              <option value="yearly">Roczny</option>
            </select>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-cyan-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-cyan-700">Zapisz</button>
            <button type="button" @click="showForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

defineProps({ goals: Array })

const tabs = [
  { href: '/reports',          label: 'Dashboard' },
  { href: '/reports/goals',    label: 'Cele KPI' },
  { href: '/reports/schedule', label: 'Harmonogram' },
]

const periodLabels = { monthly: 'Miesięczny', quarterly: 'Kwartalny', yearly: 'Roczny' }
const showForm = ref(false)
const editGoal = ref(null)
const form = ref({ name: '', current_value: 0, target_value: 0, unit: 'PLN', period: 'monthly' })

function formatValue(v, unit) {
  if (unit === 'PLN') return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN', maximumFractionDigits: 0 }).format(v)
  if (unit === '%') return v + '%'
  return v + ' ' + unit
}

function openEdit(goal) {
  editGoal.value = goal
  form.value = { name: goal.name, current_value: goal.current_value, target_value: goal.target_value, unit: goal.unit, period: goal.period }
  showForm.value = true
}

function submit() {
  if (editGoal.value) {
    router.patch(`/reports/goals/${editGoal.value.id}`, form.value, { onSuccess: () => { showForm.value = false } })
  } else {
    router.post('/reports/goals', form.value, { onSuccess: () => { showForm.value = false } })
  }
}

function deleteGoal(id) {
  if (confirm('Usunąć cel?')) router.delete(`/reports/goals/${id}`)
}
</script>
