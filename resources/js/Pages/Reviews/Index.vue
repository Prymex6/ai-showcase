<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Opinie Manager — Opinie" subtitle="Monitoruj i odpowiadaj na opinie klientów" />
    </template>
    <ModuleNav :tabs="tabs" />

    <!-- Filters -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <button v-for="opt in ratingFilters" :key="opt.value"
        @click="filter = opt.value"
        class="px-3 py-1.5 rounded-full text-xs font-medium transition"
        :class="filter === opt.value ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'">
        {{ opt.label }}
      </button>
    </div>

    <div class="space-y-4">
      <div v-for="review in filteredReviews" :key="review.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-4 mb-3">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-semibold text-gray-900">{{ review.author_name }}</span>
              <span class="text-xs text-gray-400">{{ review.platform }}</span>
              <span class="text-xs text-gray-300">·</span>
              <span class="text-xs text-gray-400">{{ formatDate(review.review_date) }}</span>
            </div>
            <div class="flex gap-0.5">
              <span v-for="i in 5" :key="i" class="text-sm" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'">★</span>
            </div>
          </div>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0"
            :class="sentimentColors[review.sentiment] || 'bg-gray-100 text-gray-500'">
            {{ sentimentLabels[review.sentiment] || review.sentiment }}
          </span>
        </div>

        <p class="text-sm text-gray-700 mb-3">{{ review.content }}</p>

        <div v-if="review.reply" class="bg-gray-50 rounded-lg px-4 py-3 border-l-2 border-cyan-400 text-sm text-gray-700 mb-3">
          <span class="text-xs text-cyan-600 font-semibold block mb-1">Twoja odpowiedź:</span>
          {{ review.reply }}
        </div>

        <div class="flex items-center gap-2">
          <button v-if="!review.reply" @click="generateReply(review)"
            class="text-xs bg-cyan-50 text-cyan-700 border border-cyan-200 px-3 py-1.5 rounded-lg hover:bg-cyan-100 font-medium">
            ✨ Generuj odpowiedź AI
          </button>
          <button @click="deleteReview(review.id)" class="text-xs text-red-400 hover:text-red-600 ml-auto">✕ Usuń</button>
        </div>

        <!-- Reply editor -->
        <div v-if="replyingTo === review.id" class="mt-3 space-y-2">
          <textarea v-model="replyText" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-400 resize-none"></textarea>
          <div class="flex gap-2">
            <button @click="saveReply(review.id)" class="text-xs bg-cyan-600 text-white px-4 py-1.5 rounded-lg hover:bg-cyan-700 font-medium">Zapisz odpowiedź</button>
            <button @click="replyingTo = null" class="text-xs text-gray-500 hover:text-gray-700">Anuluj</button>
          </div>
        </div>
      </div>
      <div v-if="filteredReviews.length === 0" class="text-center py-12 text-gray-400">Brak opinii</div>
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

const props = defineProps({ reviews: Array })

const tabs = [
  { href: '/reviews',           label: 'Opinie' },
  { href: '/reviews/templates', label: 'Szablony' },
  { href: '/reviews/trends',    label: 'Trendy' },
]

const ratingFilters = [
  { value: 'all', label: 'Wszystkie' },
  { value: 5, label: '★★★★★' },
  { value: 4, label: '★★★★' },
  { value: 3, label: '★★★' },
  { value: 2, label: '★★' },
  { value: 1, label: '★' },
]
const sentimentColors = { positive: 'bg-green-100 text-green-700', neutral: 'bg-gray-100 text-gray-600', negative: 'bg-red-100 text-red-600' }
const sentimentLabels = { positive: '😊 Pozytywna', neutral: '😐 Neutralna', negative: '😞 Negatywna' }

const filter = ref('all')
const replyingTo = ref(null)
const replyText = ref('')

const filteredReviews = computed(() => {
  if (filter.value === 'all') return props.reviews
  return props.reviews.filter(r => r.rating === filter.value)
})

function formatDate(d) {
  return new Date(d).toLocaleDateString('pl-PL')
}

async function generateReply(review) {
  const res = await axios.post(`/reviews/${review.id}/generate-reply`)
  replyText.value = res.data.reply
  replyingTo.value = review.id
}

function saveReply(id) {
  router.post(`/reviews/${id}/reply`, { reply: replyText.value }, {
    onSuccess: () => { replyingTo.value = null; replyText.value = '' }
  })
}

function deleteReview(id) {
  if (confirm('Usunąć opinię?')) router.delete(`/reviews/${id}`)
}
</script>
