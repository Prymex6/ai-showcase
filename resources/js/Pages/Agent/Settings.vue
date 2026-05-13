<template>
  <AppLayout>
    <template #header>
      <PageHeader title="AI Agent — Ustawienia" subtitle="Konfiguracja modelu i integracji AI" />
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Model status -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Status integracji</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <span class="text-sm text-gray-600">Model AI</span>
            <span class="text-sm font-semibold text-gray-900">{{ settings.model }}</span>
          </div>
          <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <span class="text-sm text-gray-600">Tryb demo</span>
            <span class="text-sm font-semibold px-2 py-0.5 rounded-full"
              :class="settings.demo_mode ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'">
              {{ settings.demo_mode ? '⚠️ Demo' : '✅ Produkcja' }}
            </span>
          </div>
          <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <span class="text-sm text-gray-600">API skonfigurowane</span>
            <span class="text-sm font-semibold"
              :class="settings.api_configured ? 'text-green-600' : 'text-red-500'">
              {{ settings.api_configured ? 'Tak' : 'Nie' }}
            </span>
          </div>
          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-gray-600">Wpisy w bazie wiedzy</span>
            <span class="text-sm font-semibold text-cyan-600">{{ settings.kb_count }}</span>
          </div>
        </div>
      </div>

      <!-- Config instructions -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Konfiguracja .env</h3>
        <div class="bg-gray-50 rounded-lg p-4 font-mono text-xs text-gray-700 space-y-1">
          <div><span class="text-purple-600">OPENAI_API_KEY</span>=sk-your-key-here</div>
          <div><span class="text-purple-600">OPENAI_MODEL</span>=gpt-4o-mini</div>
          <div><span class="text-purple-600">DEMO_MODE</span>=false</div>
        </div>
        <p class="text-xs text-gray-500">Po zmianie pliku .env uruchom: <code class="bg-gray-100 px-1 rounded">php artisan config:cache</code></p>
      </div>

      <!-- Persona settings (display only) -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 md:col-span-2">
        <h3 class="font-semibold text-gray-900 mb-4">Persona agenta</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div class="bg-cyan-50 rounded-lg p-4">
            <div class="font-medium text-cyan-900 mb-1">Nazwa</div>
            <div class="text-cyan-700">AI Assistant</div>
          </div>
          <div class="bg-cyan-50 rounded-lg p-4">
            <div class="font-medium text-cyan-900 mb-1">Język</div>
            <div class="text-cyan-700">Polski</div>
          </div>
          <div class="bg-cyan-50 rounded-lg p-4">
            <div class="font-medium text-cyan-900 mb-1">Tryb fallback</div>
            <div class="text-cyan-700">Baza wiedzy → AI</div>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-4">Agent najpierw szuka odpowiedzi w bazie wiedzy. Jeśli nie znajdzie, odpowiada z użyciem OpenAI GPT.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'

defineProps({ settings: Object })

const tabs = [
  { href: '/agent',           label: 'Chat' },
  { href: '/agent/knowledge', label: 'Baza wiedzy' },
  { href: '/agent/history',   label: 'Historia' },
  { href: '/agent/settings',  label: 'Ustawienia' },
]
</script>
