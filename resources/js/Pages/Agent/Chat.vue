<template>
  <AppLayout>
    <template #header>
      <PageHeader title="AI Agent — Chat" subtitle="Asystent AI oparty na Twojej bazie wiedzy" />
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="flex gap-6 h-[calc(100vh-260px)] min-h-[500px]">
      <!-- Chat window -->
      <div class="flex-1 bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col overflow-hidden">
        <div ref="messagesEl" class="flex-1 overflow-y-auto p-5 space-y-4">
          <div v-if="messages.length === 0" class="text-center text-gray-400 mt-16">
            <div class="text-5xl mb-3">🤖</div>
            <p class="font-medium">Napisz wiadomość, aby rozpocząć rozmowę</p>
            <p class="text-sm mt-1">Agent korzysta z Twojej bazy wiedzy</p>
          </div>
          <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
            <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed"
              :class="msg.role === 'user' ? 'bg-cyan-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm'">
              {{ msg.content }}
            </div>
          </div>
          <div v-if="loading" class="flex justify-start">
            <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-2.5">
              <span class="inline-flex gap-1">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
              </span>
            </div>
          </div>
        </div>

        <form @submit.prevent="send" class="border-t border-gray-100 p-4 flex gap-3">
          <input v-model="input" :disabled="loading"
            placeholder="Napisz wiadomość…"
            class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-cyan-400 disabled:opacity-50">
          <button type="submit" :disabled="loading || !input.trim()"
            class="px-5 py-2.5 bg-cyan-600 text-white text-sm font-medium rounded-xl hover:bg-cyan-700 disabled:opacity-50 transition">
            Wyślij
          </button>
        </form>
      </div>

      <!-- Sidebar info -->
      <div class="w-64 space-y-4 flex-shrink-0">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
          <h3 class="font-semibold text-gray-900 mb-3 text-sm">Status agenta</h3>
          <div class="space-y-2 text-xs text-gray-600">
            <div class="flex justify-between"><span>Model</span><span class="font-medium text-gray-900">{{ settings.model }}</span></div>
            <div class="flex justify-between"><span>Tryb demo</span>
              <span :class="settings.demo_mode ? 'text-amber-600 font-medium' : 'text-green-600 font-medium'">
                {{ settings.demo_mode ? 'Tak' : 'Nie' }}
              </span>
            </div>
            <div class="flex justify-between"><span>Baza wiedzy</span><span class="font-medium text-gray-900">{{ settings.kb_count }} wpisów</span></div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
          <h3 class="font-semibold text-gray-900 mb-3 text-sm">Sugestie</h3>
          <div class="space-y-2">
            <button v-for="s in suggestions" :key="s" @click="input = s"
              class="w-full text-left text-xs text-cyan-700 bg-cyan-50 rounded-lg px-3 py-2 hover:bg-cyan-100 transition">
              {{ s }}
            </button>
          </div>
        </div>

        <button @click="clearChat"
          class="w-full text-xs text-gray-500 bg-white border border-gray-200 rounded-xl py-2 hover:bg-gray-50">
          Wyczyść chat
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'
import axios from 'axios'

defineProps({ settings: Object })

const tabs = [
  { href: '/agent',            label: 'Chat' },
  { href: '/agent/knowledge',  label: 'Baza wiedzy' },
  { href: '/agent/history',    label: 'Historia' },
  { href: '/agent/settings',   label: 'Ustawienia' },
]

const suggestions = [
  'Jak mogę ci pomóc?',
  'Jakie są godziny pracy?',
  'Jakie usługi oferujecie?',
  'Jak skontaktować się z obsługą?',
]

const messages = ref([])
const input = ref('')
const loading = ref(false)
const messagesEl = ref(null)

async function send() {
  const text = input.value.trim()
  if (!text || loading.value) return
  messages.value.push({ id: Date.now(), role: 'user', content: text })
  input.value = ''
  loading.value = true
  await scrollBottom()
  try {
    const res = await axios.post('/agent/message', { message: text })
    messages.value.push({ id: Date.now() + 1, role: 'assistant', content: res.data.reply })
  } catch {
    messages.value.push({ id: Date.now() + 1, role: 'assistant', content: 'Przepraszam, wystąpił błąd. Spróbuj ponownie.' })
  } finally {
    loading.value = false
    await scrollBottom()
  }
}

async function scrollBottom() {
  await nextTick()
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
}

function clearChat() {
  messages.value = []
}
</script>
