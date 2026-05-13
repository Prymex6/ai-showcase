<template>
  <AppLayout>
    <template #header>
      <PageHeader title="FakturaAI — Integracje" subtitle="Połącz system z zewnętrznymi serwisami" />
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="integration in integrations" :key="integration.name"
        class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="text-3xl">{{ integration.icon }}</div>
        <div class="flex-1">
          <h3 class="font-semibold text-gray-900">{{ integration.name }}</h3>
          <p class="text-sm text-gray-500">{{ integration.desc }}</p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full font-medium"
          :class="integration.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
          {{ integration.status === 'active' ? '✅ Połączono' : '⚙️ Skonfiguruj' }}
        </span>
      </div>
    </div>

    <div class="mt-6 bg-blue-50 rounded-xl p-5 border border-blue-100">
      <h3 class="font-semibold text-blue-900 mb-2">Konfiguracja przez .env</h3>
      <p class="text-sm text-blue-700">Dodaj klucze API do pliku <code class="bg-blue-100 px-1 rounded">.env</code>
        aby aktywować integracje: <code>STRIPE_KEY</code>, <code>PAYU_MERCHANT_ID</code>, <code>SMTP_HOST</code>.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

const tabs = [
  { href: '/invoices', label: 'Faktury' },
  { href: '/invoices/clients', label: 'Klienci' },
  { href: '/invoices/recurring', label: 'Cykliczne' },
  { href: '/invoices/integrations', label: 'Integracje' },
]

const integrations = [
  { icon: '💳', name: 'Stripe', desc: 'Płatności online, webhook po zaksięgowaniu', status: 'inactive' },
  { icon: '🏦', name: 'PayU', desc: 'Polskie płatności online, BLIK, przelew', status: 'inactive' },
  { icon: '📧', name: 'SMTP / Gmail', desc: 'Automatyczna wysyłka faktur PDF na e-mail', status: 'inactive' },
  { icon: '📊', name: 'iFirma', desc: 'Eksport faktur do programu księgowego', status: 'inactive' },
  { icon: '📱', name: 'Telegram Bot', desc: 'Powiadomienia o nowych płatnościach', status: 'inactive' },
  { icon: '🔗', name: 'Webhook', desc: 'Własne integracje przez REST webhook', status: 'inactive' },
]
</script>
