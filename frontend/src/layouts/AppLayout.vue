<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-900 flex">
    <!-- Mobile overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-20 lg:hidden" @click="sidebarOpen = false" />

    <!-- Sidebar -->
    <aside :class="[
      'fixed lg:static inset-y-0 left-0 z-30 flex flex-col shrink-0 transition-all duration-300 lg:translate-x-0',
      'bg-gradient-to-b from-emerald-700 via-emerald-800 to-emerald-900',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
      sidebarCollapsed ? 'w-20' : 'w-64',
    ]">
      <!-- Logo -->
      <div class="px-4 py-5 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
          <img src="/logo.png" alt="RS Juwita" class="w-9 h-9 object-contain" />
          <div :class="sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto'" class="transition-all duration-200 overflow-hidden whitespace-nowrap">
            <h2 class="text-white font-bold text-sm">RS Juwita</h2>
            <p class="text-emerald-200/60 text-xs">MCU System</p>
          </div>
        </div>
        <button @click="toggleSidebar" class="hidden lg:flex p-1.5 rounded-lg text-emerald-200/60 hover:bg-white/10 hover:text-white transition-colors">
          <svg class="w-4 h-4" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="px-3 text-xs font-semibold text-emerald-200/50 uppercase tracking-wider mb-2" :class="sidebarCollapsed ? 'text-center' : ''">{{ sidebarCollapsed ? '•••' : 'Menu' }}</p>
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="[isActive(item.path)
            ? 'bg-white/20 text-white shadow-sm backdrop-blur-sm'
            : 'text-emerald-100/80 hover:bg-white/10 hover:text-white',
            sidebarCollapsed ? 'justify-center' : '',
          ]"
          :title="sidebarCollapsed ? item.label : ''"
        >
          <span v-html="item.icon" class="w-5 h-5 flex items-center justify-center shrink-0" />
          <span :class="sidebarCollapsed ? 'hidden' : ''">{{ item.label }}</span>
        </router-link>

        <p class="px-3 text-xs font-semibold text-emerald-200/50 uppercase tracking-wider mt-6 mb-2" :class="sidebarCollapsed ? 'text-center' : ''">{{ sidebarCollapsed ? '•••' : 'Akun' }}</p>
        <router-link
          to="/profil"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="[isActive('/profil') ? 'bg-white/20 text-white shadow-sm backdrop-blur-sm' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white', sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Profil' : ''"
        >
          <span class="w-5 h-5 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </span>
          <span :class="sidebarCollapsed ? 'hidden' : ''">Profil</span>
        </router-link>
      </nav>

      <!-- User footer -->
      <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3 mb-3" :class="sidebarCollapsed ? 'justify-center' : ''">
          <div class="w-9 h-9 min-w-[36px] rounded-full flex items-center justify-center text-white text-sm font-semibold shrink-0" :style="{ background: avatarColor }">
            {{ initials }}
          </div>
          <div :class="sidebarCollapsed ? 'hidden' : 'flex-1 min-w-0'">
            <p class="text-sm font-medium text-white truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-emerald-200/60 capitalize truncate">{{ roleLabel }}</p>
          </div>
        </div>
        <div :class="sidebarCollapsed ? 'flex flex-col gap-2' : 'flex gap-2'">
          <button @click="toggleDark" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs text-emerald-200/70 hover:bg-white/10 rounded-xl transition-colors" :title="sidebarCollapsed ? (isDark ? 'Terang' : 'Gelap') : ''">
            <svg v-if="isDark" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <span :class="sidebarCollapsed ? 'hidden' : ''">{{ isDark ? 'Terang' : 'Gelap' }}</span>
          </button>
          <button @click="handleLogout" class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs text-red-300 hover:bg-red-900/30 rounded-xl transition-colors" :title="sidebarCollapsed ? 'Logout' : ''">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span :class="sidebarCollapsed ? 'hidden' : ''">Logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Top bar -->
      <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-4 lg:px-6 py-3 flex items-center gap-4 sticky top-0 z-10">
        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700">
          <svg class="w-5 h-5 text-gray-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex-1" />
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-500 dark:text-slate-400 hidden sm:block">{{ auth.user?.name }}</span>
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold" :style="{ background: avatarColor }">
            {{ initials }}
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-4 lg:p-8 overflow-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true')
const isDark = ref(false)

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? 'true' : 'false')
}

const avatarColors = [
  'linear-gradient(135deg, #6366f1, #8b5cf6)',
  'linear-gradient(135deg, #10b981, #06b6d4)',
  'linear-gradient(135deg, #ec4899, #f43f5e)',
  'linear-gradient(135deg, #f59e0b, #ef4444)',
  'linear-gradient(135deg, #10b981, #14b8a6)',
  'linear-gradient(135deg, #8b5cf6, #d946ef)',
  'linear-gradient(135deg, #0ea5e9, #6366f1)',
  'linear-gradient(135deg, #f97316, #ec4899)',
]

const initials = computed(() => {
  const name = auth.user?.name || ''
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return name.charAt(0).toUpperCase() || 'U'
})

const avatarColor = computed(() => {
  const name = auth.user?.name || ''
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return avatarColors[Math.abs(hash) % avatarColors.length]
})

const roleLabel = computed(() => {
  const labels = {
    admin: 'Admin',
    karyawan: 'Karyawan',
    dokter_umum: 'Dokter Umum',
    laboratorium: 'Laboratorium',
    radiologi: 'Radiologi',
  }
  return labels[auth.userRole] || auth.userRole?.replace(/_/g, ' ')
})

const roleMenus = {
  karyawan: [
    { path: '/', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>', label: 'Dashboard' },
    { path: '/daftar-mcu', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>', label: 'Daftar MCU' },
    { path: '/riwayat-mcu', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>', label: 'Riwayat MCU' },
  ],
  admin: [
    { path: '/admin', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>', label: 'Dashboard' },
    { path: '/admin/registrations', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>', label: 'Pendaftaran' },
    { path: '/admin/packages', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>', label: 'Paket MCU' },
    { path: '/admin/users', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg>', label: 'Users' },
    { path: '/admin/activity-logs', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>', label: 'Log Aktivitas' },
  ],
  dokter_umum: [
    { path: '/dokter', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>', label: 'Antrian Pasien' },
    { path: '/dokter/riwayat', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>', label: 'Riwayat' },
  ],
  laboratorium: [
    { path: '/lab', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>', label: 'Antrian Lab' },
    { path: '/lab/riwayat', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>', label: 'Riwayat' },
  ],
  radiologi: [
    { path: '/radiologi', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>', label: 'Antrian Radiologi' },
    { path: '/radiologi/riwayat', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>', label: 'Riwayat' },
  ],
}

const menuItems = computed(() => roleMenus[auth.userRole] || [])

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

function toggleDark() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('darkMode', isDark.value ? 'true' : 'false')
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  const saved = localStorage.getItem('darkMode')
  if (saved === 'true') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})
</script>
