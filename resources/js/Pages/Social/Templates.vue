<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Social Planner — Szablony" subtitle="Gotowe szablony treści do mediów społecznościowych">
        <button @click="showForm = true" class="px-4 py-2 bg-violet-600 text-white text-sm rounded-lg hover:bg-violet-700 font-medium">+ Nowy szablon</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="t in templates" :key="t.id" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ t.name }}</h3>
            <div class="flex gap-1 mt-1">
              <span v-for="p in (t.platforms || [])" :key="p"
                class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">{{ p }}</span>
            </div>
          </div>
          <button @click="deleteTemplate(t.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
        </div>
        <p class="text-sm text-gray-600 line-clamp-3">{{ t.content }}</p>
        <button @click="useTemplate(t)" class="mt-3 text-xs text-violet-600 font-medium hover:text-violet-800">Użyj szablonu →</button>
      </div>
      <div v-if="templates.length === 0" class="col-span-2 text-center py-12 text-gray-400">Brak szablonów</div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy szablon</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <input v-model="form.name" placeholder="Nazwa szablonu *" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-400">
          <div class="flex gap-3 flex-wrap">
            <label v-for="p in platformList" :key="p" class="flex items-center gap-1.5 text-sm cursor-pointer">
              <input type="checkbox" :value="p" v-model="form.platforms" class="accent-violet-600">{{ p }}
            </label>
          </div>
          <textarea v-model="form.content" placeholder="Treść szablonu *" required rows="5"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-400 resize-none"></textarea>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-violet-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Zapisz</button>
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
  { href: '/social',           label: 'Kalendarz' },
  { href: '/social/templates', label: 'Szablony' },
  { href: '/social/analytics', label: 'Analityka' },
]

const platformList = ['Facebook', 'Instagram', 'LinkedIn', 'Twitter']
const showForm = ref(false)
const form = ref({ name: '', content: '', platforms: [] })

function submit() {
  router.post('/social/templates', { ...form.value, platforms: form.value.platforms },
    { onSuccess: () => { showForm.value = false } })
}

function deleteTemplate(id) {
  if (confirm('Usunąć szablon?')) router.delete(`/social/templates/${id}`)
}

function useTemplate(t) {
  router.visit('/social', { data: { content: t.content, platforms: t.platforms } })
}
</script>
