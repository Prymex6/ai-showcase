<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Lead Manager — Szablony maili" subtitle="Zarządzaj szablonami automatycznych wiadomości">
        <button @click="showForm = true"
          class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
          + Nowy szablon
        </button>
      </PageHeader>
    </template>

    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="t in templates" :key="t.id"
        class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-start justify-between mb-2">
          <div>
            <h3 class="font-semibold text-gray-900">{{ t.name }}</h3>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ t.trigger }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs" :class="t.is_active ? 'text-green-600' : 'text-gray-400'">
              {{ t.is_active ? '✅ Aktywny' : '⏸ Nieaktywny' }}
            </span>
            <button @click="deleteTemplate(t.id)" class="text-red-400 hover:text-red-600 text-sm">✕</button>
          </div>
        </div>
        <p class="text-xs font-medium text-gray-500 mb-1">Temat: {{ t.subject }}</p>
        <p class="text-xs text-gray-500 line-clamp-3" v-html="t.body"></p>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy szablon maila</h3>
        <form @submit.prevent="submitTemplate" class="space-y-3">
          <input v-model="form.name" placeholder="Nazwa szablonu *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
          <input v-model="form.subject" placeholder="Temat maila *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
          <textarea v-model="form.body" placeholder="Treść maila (HTML) *" required rows="5"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
          <select v-model="form.trigger" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="manual">Ręczny</option>
            <option value="new_lead">Nowy lead</option>
            <option value="follow_up">Follow-up</option>
          </select>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Zapisz</button>
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

defineProps({ templates: Array })

const tabs = [
  { href: '/leads',           label: 'Lista' },
  { href: '/leads/kanban',    label: 'Kanban' },
  { href: '/leads/templates', label: 'Szablony maili' },
  { href: '/leads/stats',     label: 'Statystyki' },
]

const showForm = ref(false)
const form = ref({ name: '', subject: '', body: '', trigger: 'manual' })

function submitTemplate() {
  router.post('/leads/templates', form.value, {
    onSuccess: () => { showForm.value = false; form.value = { name: '', subject: '', body: '', trigger: 'manual' } }
  })
}

function deleteTemplate(id) {
  if (confirm('Usunąć szablon?')) router.delete(`/leads/templates/${id}`)
}
</script>
