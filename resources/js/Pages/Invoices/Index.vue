<template>
  <AppLayout>
    <template #header>
      <PageHeader title="FakturaAI" subtitle="Zarządzaj fakturami i dokumentami sprzedaży">
        <button @click="showForm = true"
          class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">
          + Nowa faktura
        </button>
      </PageHeader>
    </template>

    <ModuleNav :tabs="tabs" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Numer</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Klient</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Termin</th>
            <th class="text-right px-5 py-3 font-semibold text-gray-600">Kwota</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50 transition">
            <td class="px-5 py-3 font-mono text-xs text-gray-700 font-semibold">{{ inv.number }}</td>
            <td class="px-5 py-3 text-gray-900">{{ inv.client?.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ inv.due_date }}</td>
            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ formatMoney(inv.gross_total) }}</td>
            <td class="px-5 py-3">
              <select @change="changeStatus(inv.id, $event.target.value)"
                class="text-xs border border-gray-200 rounded px-2 py-1"
                :class="statusColors[inv.status]">
                <option v-for="(label, key) in statuses" :key="key" :value="key" :selected="inv.status === key">
                  {{ label }}
                </option>
              </select>
            </td>
            <td class="px-5 py-3 text-right">
              <button @click="deleteInvoice(inv.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </td>
          </tr>
          <tr v-if="invoices.length === 0">
            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Brak faktur</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mt-6">
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-cyan-600">{{ invoices.length }}</div>
        <div class="text-xs text-gray-500 mt-1">Wszystkich faktur</div>
      </div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-green-600">{{ formatMoney(paidTotal) }}</div>
        <div class="text-xs text-gray-500 mt-1">Opłacone</div>
      </div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-red-500">{{ invoices.filter(i => i.status === 'overdue').length }}</div>
        <div class="text-xs text-gray-500 mt-1">Zaległe</div>
      </div>
    </div>

    <!-- Modal nowej faktury (uproszczony) -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">Nowa faktura</h3>
        <form @submit.prevent="submitInvoice" class="space-y-3">
          <select v-model="form.client_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
            <option value="">Wybierz klienta *</option>
            <option v-for="c in clientList" :key="c.id" :value="c.id">{{ c.name }} — {{ c.company }}</option>
          </select>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500 mb-1 block">Data wystawienia</label>
              <input v-model="form.issue_date" type="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
            <div>
              <label class="text-xs text-gray-500 mb-1 block">Termin płatności</label>
              <input v-model="form.due_date" type="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
          </div>

          <div class="border border-gray-200 rounded-lg p-3">
            <div class="text-xs font-semibold text-gray-600 mb-2">Pozycje faktury</div>
            <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-1 mb-2 text-xs">
              <input v-model="item.description" placeholder="Opis" class="col-span-5 border border-gray-200 rounded px-2 py-1" required>
              <input v-model.number="item.quantity" type="number" min="0.01" step="0.01" placeholder="Ilość" class="col-span-2 border border-gray-200 rounded px-2 py-1" required>
              <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Cena" class="col-span-2 border border-gray-200 rounded px-2 py-1" required>
              <select v-model.number="item.vat_rate" class="col-span-2 border border-gray-200 rounded px-1 py-1">
                <option value="0">0%</option>
                <option value="5">5%</option>
                <option value="8">8%</option>
                <option value="23">23%</option>
              </select>
              <button type="button" @click="removeItem(i)" class="col-span-1 text-red-400 hover:text-red-600 text-center">✕</button>
            </div>
            <button type="button" @click="addItem"
              class="text-xs text-cyan-600 hover:text-cyan-700 font-medium">+ Dodaj pozycję</button>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-cyan-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-cyan-700">Utwórz fakturę</button>
            <button type="button" @click="showForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

const props = defineProps({ invoices: Array, statuses: Object })
const page = usePage()

const tabs = [
  { href: '/invoices',             label: 'Faktury' },
  { href: '/invoices/clients',     label: 'Klienci' },
  { href: '/invoices/recurring',   label: 'Cykliczne' },
  { href: '/invoices/integrations',label: 'Integracje' },
]

const statusColors = {
  draft: 'bg-gray-50', sent: 'bg-blue-50 text-blue-700',
  paid: 'bg-green-50 text-green-700', overdue: 'bg-red-50 text-red-700', cancelled: ''
}

const showForm = ref(false)
const clientList = ref([])
const form = ref({ client_id: '', issue_date: new Date().toISOString().slice(0,10), due_date: '', items: [{ description: '', quantity: 1, unit_price: 0, vat_rate: 23 }] })

const paidTotal = computed(() => props.invoices.filter(i => i.status === 'paid').reduce((s, i) => s + parseFloat(i.gross_total), 0))

function formatMoney(v) { return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(v || 0) }

function addItem() { form.value.items.push({ description: '', quantity: 1, unit_price: 0, vat_rate: 23 }) }
function removeItem(i) { if (form.value.items.length > 1) form.value.items.splice(i, 1) }

function openForm() {
  router.get('/invoices/clients', {}, {
    only: ['clients'],
    onSuccess: (page) => { clientList.value = page.props.clients || [] }
  })
  showForm.value = true
}

function submitInvoice() {
  router.post('/invoices', form.value, {
    onSuccess: () => { showForm.value = false }
  })
}

function changeStatus(id, status) {
  router.patch(`/invoices/${id}/status`, { status })
}

function deleteInvoice(id) {
  if (confirm('Usunąć fakturę?')) router.delete(`/invoices/${id}`)
}
</script>
