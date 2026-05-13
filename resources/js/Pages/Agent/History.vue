<template>
  <AppLayout>
    <template #header>
      <PageHeader title="AI Agent — Historia" subtitle="Zapisane rozmowy z agentem" />
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Session ID</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Wiadomości</th>
            <th class="text-left px-5 py-3 font-semibold text-gray-600">Ostatnia aktywność</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="session in sessions" :key="session.session_id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ session.session_id.slice(0, 12) }}…</td>
            <td class="px-5 py-3 text-gray-700 font-medium">{{ session.count }}</td>
            <td class="px-5 py-3 text-gray-500 text-xs">{{ formatDate(session.last_at) }}</td>
            <td class="px-5 py-3 text-right">
              <button @click="viewSession(session.session_id)" class="text-xs text-cyan-600 hover:text-cyan-800 border border-cyan-200 px-2 py-1 rounded-lg">Podgląd</button>
            </td>
          </tr>
          <tr v-if="sessions.length === 0">
            <td colspan="4" class="px-5 py-8 text-center text-gray-400">Brak zapisanych rozmów</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Session detail modal -->
    <div v-if="viewedMessages.length > 0" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[80vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-bold text-gray-900">Podgląd rozmowy</h3>
          <button @click="viewedMessages = []" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-3">
          <div v-for="msg in viewedMessages" :key="msg.id" class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
            <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm"
              :class="msg.role === 'user' ? 'bg-cyan-600 text-white' : 'bg-gray-100 text-gray-900'">
              {{ msg.content }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'
import axios from 'axios'

defineProps({ sessions: Array })

const tabs = [
  { href: '/agent',           label: 'Chat' },
  { href: '/agent/knowledge', label: 'Baza wiedzy' },
  { href: '/agent/history',   label: 'Historia' },
  { href: '/agent/settings',  label: 'Ustawienia' },
]

const viewedMessages = ref([])

function formatDate(d) {
  return new Date(d).toLocaleString('pl-PL', { dateStyle: 'short', timeStyle: 'short' })
}

async function viewSession(sessionId) {
  const res = await axios.get(`/agent/history/${sessionId}`)
  viewedMessages.value = res.data.messages || []
}
</script>
