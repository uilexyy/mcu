import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import NProgress from 'nprogress'
import AuthLayout from '../layouts/AuthLayout.vue'
import AppLayout from '../layouts/AppLayout.vue'
import LoginPage from '../pages/auth/LoginPage.vue'
import RegisterPage from '../pages/auth/RegisterPage.vue'

// Karyawan pages
import KaryawanDashboard from '../pages/karyawan/DashboardPage.vue'
import KaryawanRegisterMcu from '../pages/karyawan/RegisterMcuPage.vue'
import KaryawanHistory from '../pages/karyawan/HistoryPage.vue'

// Admin pages
import AdminDashboard from '../pages/admin/DashboardPage.vue'
import AdminRegistrations from '../pages/admin/RegistrationsPage.vue'
import AdminRegistrationDetail from '../pages/admin/RegistrationDetailPage.vue'
import AdminPackages from '../pages/admin/PackagesPage.vue'
import AdminUsers from '../pages/admin/UsersPage.vue'

// Dokter pages
import DokterQueue from '../pages/dokter/QueuePage.vue'
import DokterPhysicalExam from '../pages/dokter/PhysicalExamPage.vue'
import DokterRiwayat from '../pages/dokter/RiwayatPage.vue'

// Lab pages
import LabQueue from '../pages/lab/QueuePage.vue'
import LabResults from '../pages/lab/LabResultsPage.vue'
import LabRiwayat from '../pages/lab/RiwayatPage.vue'

// Radiologi pages
import RadiologiQueue from '../pages/radiologi/QueuePage.vue'
import RadiologiResults from '../pages/radiologi/RadiologiPage.vue'
import RadiologiRiwayat from '../pages/radiologi/RiwayatPage.vue'

// Profile
import ProfilePage from '../pages/ProfilePage.vue'
import AdminActivityLogs from '../pages/admin/ActivityLogsPage.vue'

const routes = [
  {
    path: '/login',
    component: AuthLayout,
    children: [{ path: '', name: 'Login', component: LoginPage }],
    meta: { guest: true },
  },
  {
    path: '/register',
    component: AuthLayout,
    children: [{ path: '', name: 'Register', component: RegisterPage }],
    meta: { guest: true },
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      // Karyawan
      { path: '', name: 'KaryawanDashboard', component: KaryawanDashboard, meta: { role: 'karyawan' } },
      { path: 'daftar-mcu', name: 'DaftarMCU', component: KaryawanRegisterMcu, meta: { role: 'karyawan' } },
      { path: 'riwayat-mcu', name: 'RiwayatMCU', component: KaryawanHistory, meta: { role: 'karyawan' } },

      // Admin
      { path: 'admin', name: 'AdminDashboard', component: AdminDashboard, meta: { role: 'admin' } },
      { path: 'admin/registrations', name: 'AdminRegistrations', component: AdminRegistrations, meta: { role: 'admin' } },
      { path: 'admin/registrations/:id', name: 'AdminRegistrationDetail', component: AdminRegistrationDetail, meta: { role: 'admin' } },
      { path: 'admin/packages', name: 'AdminPackages', component: AdminPackages, meta: { role: 'admin' } },
      { path: 'admin/users', name: 'AdminUsers', component: AdminUsers, meta: { role: 'admin' } },
      { path: 'admin/activity-logs', name: 'AdminActivityLogs', component: AdminActivityLogs, meta: { role: 'admin' } },

      // Dokter
      { path: 'dokter', name: 'DokterDashboard', component: DokterQueue, meta: { role: 'dokter_umum' } },
      { path: 'dokter/exam/:id', name: 'DokterPhysicalExam', component: DokterPhysicalExam, meta: { role: 'dokter_umum' } },
      { path: 'dokter/riwayat', name: 'DokterRiwayat', component: DokterRiwayat, meta: { role: 'dokter_umum' } },

      // Lab
      { path: 'lab', name: 'LabDashboard', component: LabQueue, meta: { role: 'laboratorium' } },
      { path: 'lab/results/:id', name: 'LabResults', component: LabResults, meta: { role: 'laboratorium' } },
      { path: 'lab/riwayat', name: 'LabRiwayat', component: LabRiwayat, meta: { role: 'laboratorium' } },

      // Radiologi
      { path: 'radiologi', name: 'RadiologiDashboard', component: RadiologiQueue, meta: { role: 'radiologi' } },
      { path: 'radiologi/results/:id', name: 'RadiologiResults', component: RadiologiResults, meta: { role: 'radiologi' } },
      { path: 'radiologi/riwayat', name: 'RadiologiRiwayat', component: RadiologiRiwayat, meta: { role: 'radiologi' } },

      // Profile (all roles)
      { path: 'profil', name: 'Profile', component: ProfilePage },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  NProgress.start()
  const auth = useAuthStore()

  if (to.meta.guest && auth.isAuthenticated) {
    return next('/')
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.role && auth.userRole !== to.meta.role) {
    const roleRoutes = {
      karyawan: '/',
      admin: '/admin',
      dokter_umum: '/dokter',
      laboratorium: '/lab',
      radiologi: '/radiologi',
    }
    return next(roleRoutes[auth.userRole] || '/login')
  }

  next()
})

router.afterEach(() => {
  NProgress.done()
})

export default router
