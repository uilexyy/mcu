<template>
  <div>
    <PageHeader title="Manajemen User">
      <template #actions>
        <div class="flex gap-2">
          <SearchInput v-model="search" placeholder="Cari nama/email..." />
          <BaseSelect v-model="filterRole" @change="fetchUsers" :options="roleOptions" placeholder="Semua Role" />
          <button @click="openForm()" type="button" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">Tambah User</button>
        </div>
      </template>
    </PageHeader>

    <SkeletonTable v-if="loading" />

    <BaseCard v-else-if="users.length === 0" no-padding>
      <EmptyState title="Tidak ada user" description="Belum ada user yang terdaftar." />
    </BaseCard>

    <BaseCard v-else no-padding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm table-striped table-sticky">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50 dark:bg-slate-700/50">
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Nama</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Email</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Role</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">NIP</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-4 font-medium text-gray-800 dark:text-slate-200">{{ u.nama_lengkap || u.name }}</td>
              <td class="px-6 py-4 text-gray-600 dark:text-slate-300">{{ u.email }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 capitalize">{{ u.role.replace('_', ' ') }}</span>
              </td>
              <td class="px-6 py-4 text-gray-500 dark:text-slate-400">{{ u.nip || '-' }}</td>
              <td class="px-6 py-4 flex gap-3">
                <button @click="openForm(u)" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">Edit</button>
                <button @click="confirmDelete(u)" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :meta="pagination" @page-change="page = $event; fetchUsers()" />
    </BaseCard>

    <Modal :show="showForm" :title="editing ? 'Edit User' : 'Tambah User'" size="lg" @close="showForm = false">
      <form @submit.prevent="saveUser" class="space-y-3">
        <div class="grid grid-cols-3 gap-2">
          <BaseInput v-model="form.gelar_depan" label="Gelar Depan" placeholder="dr." :error="fieldError(fieldErrors, 'gelar_depan')" />
          <BaseInput v-model="form.name" label="Nama" required :error="fieldError(fieldErrors, 'name')" />
          <BaseInput v-model="form.gelar_belakang" label="Gelar Belakang" placeholder="Sp.PD" :error="fieldError(fieldErrors, 'gelar_belakang')" />
        </div>
        <BaseInput v-model="form.email" label="Email" type="email" required :error="fieldError(fieldErrors, 'email')" />
        <BaseInput v-model="form.password" label="Password" type="password" :required="!editing" minlength="8" :error="fieldError(fieldErrors, 'password')" :placeholder="editing ? 'Kosongkan jika tidak diubah' : ''" />
        <BaseSelect v-model="form.role" label="Role" required :options="formRoleOptions" :error="fieldError(fieldErrors, 'role')" />
        <BaseInput v-model="form.nip" label="NIP" :error="fieldError(fieldErrors, 'nip')" />
        <BaseInput v-model="form.departemen" label="Perusahaan" :error="fieldError(fieldErrors, 'departemen')" />
        <div v-if="editing && editing.role === 'dokter_umum'" class="border-t border-gray-100 pt-3 mt-3">
          <p class="text-sm font-medium text-gray-700 mb-2">Tanda Tangan Dokter</p>
          <div v-if="signaturePreview" class="mb-3 p-3 border border-gray-200 rounded-lg bg-white inline-block">
            <img :src="signaturePreview" class="max-h-16" alt="Tanda tangan" />
          </div>
          <div class="flex items-center gap-3">
            <label class="cursor-pointer px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-xs font-medium">
              {{ signaturePreview ? 'Ganti' : 'Unggah' }} Tanda Tangan
              <input type="file" accept="image/png,image/jpg,image/jpeg" @change="uploadSignature" class="hidden" />
            </label>
            <button v-if="signaturePreview" @click="removeSignature" type="button" class="text-xs text-red-600 hover:underline">Hapus</button>
            <p v-if="signatureUploading" class="text-xs text-emerald-600">Mengunggah...</p>
            <p v-if="signatureError" class="text-xs text-red-500">{{ signatureError }}</p>
          </div>
        </div>
        <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
      </form>
      <template #footer>
        <button type="button" @click="showForm = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Batal</button>
        <button type="submit" @click="saveUser" :disabled="saving" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 text-sm">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
      </template>
    </Modal>

    <Modal :show="showDelete" title="Hapus User" size="sm" @close="showDelete = false">
      <p class="text-gray-600">Yakin ingin menghapus user <strong>{{ deletingUser?.nama_lengkap || deletingUser?.name }}</strong>?</p>
      <div v-if="deleteError" class="text-red-500 text-sm mt-2">{{ deleteError }}</div>
      <template #footer>
        <button type="button" @click="showDelete = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Batal</button>
        <button type="button" @click="doDelete" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 text-sm">{{ saving ? 'Menghapus...' : 'Hapus' }}</button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { parseErrors as parseFieldErrors, fieldError } from '../../utils/errors'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import SearchInput from '../../components/SearchInput.vue'
import SkeletonTable from '../../components/SkeletonTable.vue'
import EmptyState from '../../components/EmptyState.vue'
import Pagination from '../../components/Pagination.vue'
import Modal from '../../components/Modal.vue'

const users = ref([])
const showForm = ref(false)
const showDelete = ref(false)
const deletingUser = ref(null)
const deleteError = ref('')
const editing = ref(null)
const saving = ref(false)
const formError = ref('')
const fieldErrors = ref({})
const search = ref('')
const filterRole = ref('')
const page = ref(1)
const loading = ref(true)
const pagination = ref(null)
const form = reactive({ name: '', gelar_depan: '', gelar_belakang: '', email: '', password: '', role: 'dokter_umum', nip: '', departemen: '' })
const signaturePreview = ref('')
const signatureUploading = ref(false)
const signatureError = ref('')

const roleOptions = [
  { value: '', label: 'Semua Role' },
  { value: 'admin', label: 'Admin' },
  { value: 'dokter_umum', label: 'Dokter Umum' },
  { value: 'laboratorium', label: 'Laboratorium' },
  { value: 'radiologi', label: 'Radiologi' },
  { value: 'karyawan', label: 'Karyawan' },
]

const formRoleOptions = [
  { value: 'dokter_umum', label: 'Dokter Umum' },
  { value: 'laboratorium', label: 'Laboratorium' },
  { value: 'radiologi', label: 'Radiologi' },
  { value: 'admin', label: 'Admin' },
  { value: 'karyawan', label: 'Karyawan' },
]

function resetForm() {
  form.name = ''; form.gelar_depan = ''; form.gelar_belakang = ''
  form.email = ''; form.password = ''
  form.role = 'dokter_umum'; form.nip = ''; form.departemen = ''
}

function fillForm(user) {
  form.name = user.name
  form.gelar_depan = user.gelar_depan || ''
  form.gelar_belakang = user.gelar_belakang || ''
  form.email = user.email
  form.password = ''
  form.role = user.role
  form.nip = user.nip || ''
  form.departemen = user.departemen || ''
  signaturePreview.value = user.signature_url || ''
}

function openForm(user) {
  resetForm()
  signaturePreview.value = ''
  signatureError.value = ''
  if (user) {
    editing.value = user
    fillForm(user)
  } else {
    editing.value = null
  }
  showForm.value = true
}

async function saveUser() {
  saving.value = true; formError.value = ''; fieldErrors.value = {}
  try {
    if (editing.value) {
      await api.put(`/admin/users/${editing.value.id}`, form)
    } else {
      await api.post('/admin/users', form)
    }
    showForm.value = false
    await fetchUsers()
    resetForm()
  } catch (e) {
    const parsed = parseFieldErrors(e)
    if (Object.keys(parsed).length > 0) {
      fieldErrors.value = parsed
    } else {
      formError.value = e.response?.data?.message || 'Gagal menyimpan'
    }
  }
  finally { saving.value = false }
}

function confirmDelete(user) {
  deletingUser.value = user
  deleteError.value = ''
  showDelete.value = true
}

async function doDelete() {
  saving.value = true
  deleteError.value = ''
  try {
    await api.delete(`/admin/users/${deletingUser.value.id}`)
    showDelete.value = false
    deletingUser.value = null
    await fetchUsers()
  } catch (e) {
    deleteError.value = e.response?.data?.message || 'Gagal menghapus'
  } finally { saving.value = false }
}

async function uploadSignature(e) {
  const file = e.target.files?.[0]
  if (!file) return
  signatureUploading.value = true
  signatureError.value = ''
  const fd = new FormData()
  fd.append('signature', file)
  try {
    const res = await api.post(`/admin/users/${editing.value.id}/signature`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    signaturePreview.value = res.data.data.signature_url
  } catch (e) {
    signatureError.value = e.response?.data?.message || 'Gagal mengunggah'
  } finally {
    signatureUploading.value = false
  }
}

function removeSignature() {
  signaturePreview.value = ''
}

async function fetchUsers() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15 }
    if (filterRole.value) params.role = filterRole.value
    if (search.value) params.search = search.value
    const res = await api.get('/admin/users', { params })
    users.value = res.data.data
    pagination.value = res.data.meta
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

onMounted(fetchUsers)
</script>
