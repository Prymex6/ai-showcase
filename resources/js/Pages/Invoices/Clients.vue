<template>
  <AppLayout>
    <template #header>
      <PageHeader title="FakturaAI — Klienci" subtitle="Baza danych klientów">
        <button @click="showForm = true" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">+ Nowy klient</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Klient</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Firma / NIP</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Kontakt</th>
            <th class="text-right px-5 py-3 font-semibold text-gray-600">Faktur</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="c in clients" :key="c.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-900">{{ c.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ c.company }}<br><span class="text-xs">NIP: {{ c.nip }}</span></td>
            <td class="px-5 py-3 text-gray-500 text-xs">{{ c.email }}<br>{{ c.phone }}</td>
            <td class="px-5 py-3 text-right font-bold text-cyan-600">{{ c.invoices_count }}</td>
            <td class="px-5 py-3 text-right">
              <button @click="deleteClient(c.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy klient</h3>
        <form @submit.prevent="submitClient" class="space-y-3">
          <input v-model="form.name" placeholder="Imię i nazwisko *" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <input v-model="form.email" type="email" placeholder="E-mail *" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <input v-model="form.phone" placeholder="Telefon" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
          <input v-model="form.company" placeholder="Firma" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
          <input v-model="form.nip" placeholder="NIP" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
          <input v-model="form.address" placeholder="Adres" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="form.city" placeholder="Miasto" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <input v-model="form.postal_code" placeholder="Kod pocztowy" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
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

defineProps({ clients: Array })

const tabs = [
  { href: '/invoices', label: 'Faktury' },
  { href: '/invoices/clients', label: 'Klienci' },
  { href: '/invoices/recurring', label: 'Cykliczne' },
  { href: '/invoices/integrations', label: 'Integracje' },
]

const showForm = ref(false)
const form = ref({ name: '', email: '', phone: '', company: '', nip: '', address: '', city: '', postal_code: '' })

function submitClient() {
  router.post('/invoices/clients', form.value, { onSuccess: () => { showForm.value = false } })
}

function deleteClient(id) {
  if (confirm('Usunąć klienta?')) router.delete(`/invoices/clients/${id}`)
}
</script>
