<template>
  <AppLayout>
    <template #header>
      <PageHeader title="Dashboard" subtitle="Centrum narzędzi AI dla Twojej firmy" />
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <a v-for="m in modules" :key="m.href" :href="m.href"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
        <div class="h-2 bg-gradient-to-r" :class="m.color"></div>
        <div class="p-6">
          <div class="text-3xl mb-3">{{ m.icon }}</div>
          <h3 class="font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition">{{ m.title }}</h3>
          <p class="text-sm text-gray-500 leading-relaxed">{{ m.desc }}</p>
        </div>
      </a>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'

const modules = [
  { href: '/leads',   icon: '🎯', title: 'Lead Manager Pro',  desc: 'Zarządzanie leadami, Kanban, szablony maili, statystyki konwersji.',       color: 'from-indigo-500 to-indigo-700'  },
  { href: '/invoices',icon: '📄', title: 'FakturaAI',         desc: 'Faktury, klienci, faktury cykliczne, integracje Stripe i PayU.',             color: 'from-cyan-500 to-cyan-700'      },
  { href: '/agent',   icon: '🤖', title: 'AI Agent',          desc: 'Chatbot GPT-4 z bazą wiedzy, automatyczna eskalacja, historia rozmów.',      color: 'from-amber-500 to-amber-700'    },
  { href: '/social',  icon: '📱', title: 'Social Planner',    desc: 'Planowanie postów, generowanie treści przez AI, kalendarz publikacji.',      color: 'from-purple-500 to-purple-700'  },
  { href: '/reports', icon: '📈', title: 'Raporty AI',        desc: 'KPI dashboard, insighty AI, harmonogram automatycznej wysyłki raportów.',   color: 'from-emerald-500 to-emerald-700'},
  { href: '/reviews', icon: '⭐', title: 'Opinie Manager',    desc: 'Monitoring opinii Google, automatyczne odpowiedzi AI, analiza trendów.',    color: 'from-red-500 to-red-700'        },
]
</script>
