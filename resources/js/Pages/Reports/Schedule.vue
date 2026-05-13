<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Raporty AI — Harmonogram" subtitle="Automatyczna wysyłka raportów e-mail">
        <button @click="showForm = true" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">+ Nowy harmonogram</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Raport</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Rodzaj</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Częstotliwość</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Odbiorcy</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Następny</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="s in schedules" :key="s.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-900">{{ s.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ reportTypes[s.report_type] || s.report_type }}</td>
            <td class="px-5 py-3 text-gray-500">{{ frequencyLabels[s.frequency] }}</td>
            <td class="px-5 py-3 text-gray-500 text-xs">{{ s.recipients }}</td>
            <td class="px-5 py-3 text-cyan-600 text-xs">{{ s.next_run }}</td>
            <td class="px-5 py-3">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                :class="s.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ s.is_active ? 'Aktywny' : 'Wstrzymany' }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <button @click="deleteSchedule(s.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </td>
          </tr>
          <tr v-if="schedules.length === 0">
            <td colspan="7" class="px-5 py-8 text-center text-gray-400">Brak harmonogramów</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy harmonogram raportu</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <input v-model="form.name" placeholder="Nazwa raportu *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <select v-model="form.report_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="kpi_summary">Podsumowanie KPI</option>
            <option value="leads_report">Raport leadów</option>
            <option value="invoices_report">Raport faktur</option>
            <option value="social_report">Raport social media</option>
          </select>
          <select v-model="form.frequency" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="daily">Codziennie</option>
            <option value="weekly">Co tydzień</option>
            <option value="monthly">Co miesiąc</option>
          </select>
          <input v-model="form.recipients" placeholder="E-maile odbiorców (przecinkami) *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
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

defineProps({ schedules: Array })

const tabs = [
  { href: '/reports',          label: 'Dashboard' },
  { href: '/reports/goals',    label: 'Cele KPI' },
  { href: '/reports/schedule', label: 'Harmonogram' },
]

const frequencyLabels = { daily: 'Codziennie', weekly: 'Co tydzień', monthly: 'Co miesiąc' }
const reportTypes = {
  kpi_summary: 'Podsumowanie KPI',
  leads_report: 'Raport leadów',
  invoices_report: 'Raport faktur',
  social_report: 'Raport social media',
}

const showForm = ref(false)
const form = ref({ name: '', report_type: 'kpi_summary', frequency: 'weekly', recipients: '' })

function submit() {
  router.post('/reports/schedule', form.value, { onSuccess: () => { showForm.value = false } })
}

function deleteSchedule(id) {
  if (confirm('Usunąć harmonogram?')) router.delete(`/reports/schedule/${id}`)
}
</script>
