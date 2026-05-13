<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col fixed inset-y-0 left-0 z-50">
      <div class="p-5 border-b border-gray-700">
        <Link href="/dashboard" class="flex items-center gap-3">
          <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">AI</div>
          <div>
            <div class="font-bold text-sm leading-tight">AI Showcase</div>
            <div class="text-xs text-gray-400">Narzędzia dla firm</div>
          </div>
        </Link>
      </div>

      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <SidebarItem href="/dashboard" icon="📊" label="Dashboard" :active="isActive('/dashboard')" />

        <div class="pt-4 pb-1 px-2 text-xs text-gray-500 uppercase tracking-wider">Moduły</div>

        <SidebarItem href="/leads" icon="🎯" label="Lead Manager" :active="isActive('/leads')" />
        <SidebarItem href="/invoices" icon="📄" label="FakturaAI" :active="isActive('/invoices')" />
        <SidebarItem href="/agent" icon="🤖" label="AI Agent" :active="isActive('/agent')" />
        <SidebarItem href="/social" icon="📱" label="Social Planner" :active="isActive('/social')" />
        <SidebarItem href="/reports" icon="📈" label="Raporty AI" :active="isActive('/reports')" />
        <SidebarItem href="/reviews" icon="⭐" label="Opinie Manager" :active="isActive('/reviews')" />
      </nav>

      <div class="p-4 border-t border-gray-700">
        <div class="flex items-center justify-between">
          <div class="text-sm">
            <div class="font-medium">{{ $page.props.auth.user.name }}</div>
            <div class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</div>
          </div>
          <Link href="/logout" method="post" as="button" class="text-gray-400 hover:text-white text-xs px-2 py-1 rounded hover:bg-gray-700 transition">
            Wyloguj
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
      <header v-if="$slots.header" class="bg-white border-b px-8 py-4 sticky top-0 z-40">
        <slot name="header" />
      </header>
      <main class="flex-1 p-8">
        <!-- Flash messages -->
        <div v-if="$page.props.flash?.success"
          class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
          <span>✅</span> {{ $page.props.flash.success }}
        </div>
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import SidebarItem from '@/Components/SidebarItem.vue'

const page = usePage()

function isActive(path) {
  return window.location.pathname.startsWith(path)
}
</script>
