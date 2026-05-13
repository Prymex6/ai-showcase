<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Social Planner — Kalendarz" subtitle="Planuj i zarządzaj postami w mediach społecznościowych">
        <button @click="showForm = true" class="px-4 py-2 bg-violet-600 text-white text-sm rounded-lg hover:bg-violet-700 font-medium">+ Nowy post</button>
      </PageHeader>
    </template>
    <ModuleNav :tabs="tabs" />

    <!-- Platform filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <button v-for="p in ['all', ...platforms]" :key="p"
        @click="filter = p"
        class="px-3 py-1.5 rounded-full text-xs font-medium transition"
        :class="filter === p ? 'bg-violet-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'">
        {{ p === 'all' ? 'Wszystkie' : p }}
      </button>
    </div>

    <div class="space-y-3">
      <div v-for="post in filteredPosts" :key="post.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex gap-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl"
          :class="platformColors[post.platforms?.[0]] || 'bg-gray-100'">
          {{ platformIcons[post.platforms?.[0]] || '📢' }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span v-for="plat in (post.platforms || [])" :key="plat"
              class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">{{ plat }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium ml-auto"
              :class="statusColors[post.status] || 'bg-gray-100 text-gray-600'">{{ post.status }}</span>
          </div>
          <p class="text-sm text-gray-800 line-clamp-2">{{ post.content }}</p>
          <div class="mt-2 text-xs text-gray-400">
            {{ post.scheduled_at ? 'Zaplanowano: ' + formatDate(post.scheduled_at) : 'Brak daty' }}
          </div>
        </div>
        <button @click="deletePost(post.id)" class="text-red-400 hover:text-red-600 text-xs self-start flex-shrink-0">✕</button>
      </div>
      <div v-if="filteredPosts.length === 0" class="text-center py-12 text-gray-400">Brak postów</div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Nowy post</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <div class="flex gap-2 flex-wrap">
            <label v-for="p in platforms" :key="p" class="flex items-center gap-1.5 text-sm cursor-pointer">
              <input type="checkbox" :value="p" v-model="form.platforms" class="accent-violet-600">{{ p }}
            </label>
          </div>
          <textarea v-model="form.content" placeholder="Treść posta *" required rows="5"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-400 resize-none"></textarea>
          <input v-model="form.scheduled_at" type="datetime-local"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none">
          <div class="flex gap-2">
            <button type="button" @click="generateContent" :disabled="generating"
              class="flex-1 bg-violet-50 text-violet-700 border border-violet-200 py-2 rounded-lg text-sm font-medium hover:bg-violet-100 disabled:opacity-50">
              {{ generating ? 'Generuję…' : '✨ Generuj AI' }}
            </button>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-violet-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Zapisz post</button>
            <button type="button" @click="showForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200">Anuluj</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ModuleNav from '@/Components/ModuleNav.vue'
import axios from 'axios'

const props = defineProps({ posts: Array })

const tabs = [
  { href: '/social',           label: 'Kalendarz' },
  { href: '/social/templates', label: 'Szablony' },
  { href: '/social/analytics', label: 'Analityka' },
]

const platforms = ['Facebook', 'Instagram', 'LinkedIn', 'Twitter']
const platformIcons = { Facebook: '📘', Instagram: '📸', LinkedIn: '💼', Twitter: '🐦' }
const platformColors = { Facebook: 'bg-blue-100', Instagram: 'bg-pink-100', LinkedIn: 'bg-sky-100', Twitter: 'bg-cyan-100' }
const statusColors = {
  draft: 'bg-gray-100 text-gray-600',
  scheduled: 'bg-blue-100 text-blue-700',
  published: 'bg-green-100 text-green-700',
  failed: 'bg-red-100 text-red-600',
}

const filter = ref('all')
const showForm = ref(false)
const generating = ref(false)
const form = ref({ content: '', platforms: [], scheduled_at: '' })

const filteredPosts = computed(() => {
  if (filter.value === 'all') return props.posts
  return props.posts.filter(p => p.platforms?.includes(filter.value))
})

function formatDate(d) {
  return new Date(d).toLocaleString('pl-PL', { dateStyle: 'short', timeStyle: 'short' })
}

async function generateContent() {
  generating.value = true
  try {
    const res = await axios.post('/social/generate', { platforms: form.value.platforms })
    form.value.content = res.data.content
  } finally {
    generating.value = false
  }
}

function submit() {
  router.post('/social', { content: form.value.content, platform: form.value.platforms, scheduled_at: form.value.scheduled_at },
    { onSuccess: () => { showForm.value = false } })
}

function deletePost(id) {
  if (confirm('Usunąć post?')) router.delete(`/social/${id}`)
}
</script>
