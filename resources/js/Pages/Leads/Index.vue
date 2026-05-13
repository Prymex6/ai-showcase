<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Lead Manager Pro" subtitle="Zarządzaj zapytaniami klientów">
        <button @click="showForm = true"
          class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
          + Nowy lead
        </button>
      </PageHeader>
    </template>

    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Lista leadów -->
      <div class="lg:col-span-2 space-y-3">
        <div v-if="leads.length === 0" class="bg-white rounded-xl p-8 text-center text-gray-400 border border-dashed">
          Brak leadów. Dodaj pierwszy!
        </div>
        <div v-for="lead in leads" :key="lead.id"
          class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="font-semibold text-gray-900 truncate">{{ lead.name }}</span>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusColors[lead.status]">
                {{ statuses[lead.status] }}
              </span>
            </div>
            <div class="text-sm text-gray-500 truncate">{{ lead.company }} · {{ lead.email }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ lead.service }} · Score: {{ lead.score }}</div>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <select @change="changeStatus(lead, $event.target.value)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-gray-50">
              <option v-for="(label, key) in statuses" :key="key" :value="key" :selected="lead.status === key">
                {{ label }}
              </option>
            </select>
            <button @click="deleteLead(lead.id)" class="text-red-400 hover:text-red-600 text-xs px-2 py-1">✕</button>
          </div>
        </div>
      </div>

      <!-- Statystyki boczne -->
      <div class="space-y-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
          <h3 class="font-semibold text-gray-900 mb-4">Status leadów</h3>
          <div v-for="(label, key) in statuses" :key="key" class="flex justify-between items-center text-sm py-1.5 border-b border-gray-50 last:border-0">
            <span class="text-gray-600">{{ label }}</span>
            <span class="font-bold text-gray-900">{{ countByStatus(key) }}</span>
          </div>
        </div>
        <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
          <div class="text-2xl font-bold text-indigo-700">{{ leads.length }}</div>
          <div class="text-sm text-indigo-600">Wszystkich leadów</div>
        </div>
      </div>
    </div>

    <!-- Modal nowego leada -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy lead</h3>
        <form @submit.prevent="submitLead" class="space-y-3">
          <input v-model="form.name" placeholder="Imię i nazwisko *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
          <input v-model="form.company" placeholder="Firma"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
          <input v-model="form.email" type="email" placeholder="E-mail *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
          <input v-model="form.phone" placeholder="Telefon"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
          <input v-model="form.service" placeholder="Usługa / zainteresowanie"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
          <textarea v-model="form.message" placeholder="Wiadomość" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none resize-none"></textarea>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
              Dodaj lead
            </button>
            <button type="button" @click="showForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200">
              Anuluj
            </button>
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

const props = defineProps({ leads: Array, statuses: Object })

const tabs = [
  { href: '/leads',           label: 'Lista' },
  { href: '/leads/kanban',    label: 'Kanban' },
  { href: '/leads/templates', label: 'Szablony maili' },
  { href: '/leads/stats',     label: 'Statystyki' },
]

const statusColors = {
  new:         'bg-blue-100 text-blue-700',
  contact:     'bg-yellow-100 text-yellow-700',
  qualified:   'bg-purple-100 text-purple-700',
  proposal:    'bg-orange-100 text-orange-700',
  closed_won:  'bg-green-100 text-green-700',
  closed_lost: 'bg-red-100 text-red-700',
}

const showForm = ref(false)
const form = ref({ name: '', company: '', email: '', phone: '', service: '', message: '' })

function countByStatus(status) {
  return props.leads.filter(l => l.status === status).length
}

function submitLead() {
  router.post('/leads', form.value, {
    onSuccess: () => { showForm.value = false; form.value = { name: '', company: '', email: '', phone: '', service: '', message: '' } }
  })
}

function changeStatus(lead, status) {
  router.patch(`/leads/${lead.id}/status`, { status })
}

function deleteLead(id) {
  if (confirm('Usunąć tego leada?')) {
    router.delete(`/leads/${id}`)
  }
}
</script>
