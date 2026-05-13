<template>
  <AppLayout>
    <template #header>
      <PageHeader title="FakturaAI — Faktury Cykliczne" subtitle="Automatyczne wystawianie faktur">
        <button @click="showForm = true" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">+ Nowa cykliczna</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="r in recurring" :key="r.id" class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ r.name }}</h3>
            <p class="text-sm text-gray-500">{{ r.client?.name }}</p>
          </div>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="r.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ r.is_active ? 'Aktywna' : 'Nieaktywna' }}
          </span>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center text-xs border-t border-gray-100 pt-3">
          <div><div class="font-bold text-gray-900">{{ formatMoney(r.amount) }}</div><div class="text-gray-400">Kwota netto</div></div>
          <div><div class="font-bold text-gray-900">{{ intervalLabels[r.interval] }}</div><div class="text-gray-400">Interwał</div></div>
          <div><div class="font-bold text-cyan-600">{{ r.next_date }}</div><div class="text-gray-400">Następna</div></div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowa faktura cykliczna</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <select v-model="form.client_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
            <option value="">Wybierz klienta *</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <input v-model="form.name" placeholder="Nazwa faktury *" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <div class="grid grid-cols-2 gap-3">
            <input v-model.number="form.amount" type="number" min="0" step="0.01" placeholder="Kwota netto *" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <select v-model.number="form.vat_rate" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
              <option :value="0">VAT 0%</option><option :value="5">VAT 5%</option>
              <option :value="8">VAT 8%</option><option :value="23">VAT 23%</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <select v-model="form.interval" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="monthly">Co miesiąc</option>
              <option value="quarterly">Co kwartał</option>
              <option value="yearly">Co rok</option>
            </select>
            <input v-model.number="form.day_of_month" type="number" min="1" max="28" placeholder="Dzień miesiąca" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
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

defineProps({ recurring: Array, clients: Array })

const tabs = [
  { href: '/invoices', label: 'Faktury' },
  { href: '/invoices/clients', label: 'Klienci' },
  { href: '/invoices/recurring', label: 'Cykliczne' },
  { href: '/invoices/integrations', label: 'Integracje' },
]

const intervalLabels = { monthly: 'Miesięcznie', quarterly: 'Kwartalnie', yearly: 'Rocznie' }
const showForm = ref(false)
const form = ref({ client_id: '', name: '', amount: 0, vat_rate: 23, interval: 'monthly', day_of_month: 1 })

function formatMoney(v) { return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(v || 0) }
function submit() { router.post('/invoices/recurring', form.value, { onSuccess: () => { showForm.value = false } }) }
</script>
