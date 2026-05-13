<template>
  <AppLayout>
    <template #header>
      <PageHeader title="AI Agent — Baza wiedzy" subtitle="Zarządzaj wiedzą swojego agenta AI">
        <button @click="openAdd" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700 font-medium">+ Dodaj wpis</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="space-y-3">
      <div v-for="entry in entries" :key="entry.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full font-medium">{{ entry.category }}</span>
            </div>
            <h3 class="font-semibold text-gray-900">{{ entry.question }}</h3>
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ entry.answer }}</p>
            <div v-if="entry.keywords" class="mt-2 flex flex-wrap gap-1">
              <span v-for="kw in entry.keywords.split(',')" :key="kw"
                class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ kw.trim() }}</span>
            </div>
          </div>
          <div class="flex gap-2 flex-shrink-0">
            <button @click="openEdit(entry)" class="text-xs text-cyan-600 hover:text-cyan-800 border border-cyan-200 px-2 py-1 rounded-lg">Edytuj</button>
            <button @click="deleteEntry(entry.id)" class="text-xs text-red-400 hover:text-red-600 border border-red-100 px-2 py-1 rounded-lg">✕</button>
          </div>
        </div>
      </div>
      <div v-if="entries.length === 0" class="text-center py-12 text-gray-400">
        Brak wpisów w bazie wiedzy
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <h3 class="text-lg font-bold mb-4">{{ editEntry ? 'Edytuj wpis' : 'Nowy wpis' }}</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <input v-model="form.category" placeholder="Kategoria (np. Godziny pracy, Ceny)" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <input v-model="form.question" placeholder="Pytanie / słowo kluczowe *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400">
          <textarea v-model="form.answer" placeholder="Odpowiedź *" required rows="4"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400 resize-none"></textarea>
          <input v-model="form.keywords" placeholder="Słowa kluczowe (oddziel przecinkami)"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
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

defineProps({ entries: Array })

const tabs = [
  { href: '/agent',           label: 'Chat' },
  { href: '/agent/knowledge', label: 'Baza wiedzy' },
  { href: '/agent/history',   label: 'Historia' },
  { href: '/agent/settings',  label: 'Ustawienia' },
]

const showForm = ref(false)
const editEntry = ref(null)
const form = ref({ category: '', question: '', answer: '', keywords: '' })

function openAdd() {
  editEntry.value = null
  form.value = { category: '', question: '', answer: '', keywords: '' }
  showForm.value = true
}

function openEdit(entry) {
  editEntry.value = entry
  form.value = { category: entry.category, question: entry.question, answer: entry.answer, keywords: entry.keywords || '' }
  showForm.value = true
}

function submit() {
  if (editEntry.value) {
    router.patch(`/agent/knowledge/${editEntry.value.id}`, form.value, { onSuccess: () => { showForm.value = false } })
  } else {
    router.post('/agent/knowledge', form.value, { onSuccess: () => { showForm.value = false } })
  }
}

function deleteEntry(id) {
  if (confirm('Usunąć wpis?')) router.delete(`/agent/knowledge/${id}`)
}
</script>
