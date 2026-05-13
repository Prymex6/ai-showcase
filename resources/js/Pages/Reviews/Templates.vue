<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Opinie Manager — Szablony" subtitle="Gotowe szablony odpowiedzi na opinie">
        <button @click="showForm = true" class="px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600 font-medium">+ Nowy szablon</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="t in templates" :key="t.id" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-2">
          <div>
            <h3 class="font-semibold text-gray-900">{{ t.name }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                :class="sentimentColors[t.sentiment_type] || 'bg-gray-100 text-gray-500'">
                {{ sentimentLabels[t.sentiment_type] || t.sentiment_type }}
              </span>
              <span class="text-xs text-gray-400">⭐ {{ t.min_rating }}–{{ t.max_rating }}</span>
            </div>
          </div>
          <button @click="deleteTemplate(t.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
        </div>
        <p class="text-sm text-gray-600 line-clamp-3">{{ t.content }}</p>
      </div>
      <div v-if="templates.length === 0" class="col-span-2 text-center py-12 text-gray-400">Brak szablonów</div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy szablon odpowiedzi</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <input v-model="form.name" placeholder="Nazwa szablonu *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-amber-400">
          <select v-model="form.sentiment_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="positive">Pozytywna</option>
            <option value="neutral">Neutralna</option>
            <option value="negative">Negatywna</option>
          </select>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500 block mb-1">Min. ocena</label>
              <input v-model.number="form.min_rating" type="number" min="1" max="5"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
            <div>
              <label class="text-xs text-gray-500 block mb-1">Max. ocena</label>
              <input v-model.number="form.max_rating" type="number" min="1" max="5"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
            </div>
          </div>
          <textarea v-model="form.content" placeholder="Treść szablonu *" required rows="5"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-amber-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-amber-600">Zapisz</button>
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
  { href: '/reviews',           label: 'Opinie' },
  { href: '/reviews/templates', label: 'Szablony' },
  { href: '/reviews/trends',    label: 'Trendy' },
]

const sentimentColors = { positive: 'bg-green-100 text-green-700', neutral: 'bg-gray-100 text-gray-600', negative: 'bg-red-100 text-red-600' }
const sentimentLabels = { positive: '😊 Pozytywna', neutral: '😐 Neutralna', negative: '😞 Negatywna' }

const showForm = ref(false)
const form = ref({ name: '', sentiment_type: 'positive', min_rating: 4, max_rating: 5, content: '' })

function submit() {
  router.post('/reviews/templates', form.value, { onSuccess: () => { showForm.value = false } })
}

function deleteTemplate(id) {
  if (confirm('Usunąć szablon?')) router.delete(`/reviews/templates/${id}`)
}
</script>
