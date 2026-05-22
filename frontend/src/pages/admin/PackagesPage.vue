<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Paket MCU</h1>
      <button @click="openForm()"
        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">Tambah Paket</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="pkg in packages" :key="pkg.id" class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-2">
          <div>
            <h3 class="font-semibold text-gray-800">{{ pkg.nama_paket }} <span v-if="!pkg.is_active" class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Nonaktif</span></h3>
            <p class="text-sm text-gray-500">{{ pkg.deskripsi }}</p>
          </div>
          <div class="text-right">
            <span class="text-lg font-bold text-emerald-600">Rp {{ formatPrice(pkg.harga) }}</span>
          </div>
        </div>
        <div class="text-sm text-gray-600 mb-3 flex items-center gap-3">
          <span>{{ pkg.items?.length || 0 }} pemeriksaan</span>
          <span v-if="pkg.has_radiologi" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Radiologi</span>
          <span v-else class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Tanpa Radiologi</span>
        </div>
        <div class="flex space-x-2">
          <button @click="editPackage(pkg)" class="text-emerald-600 hover:underline text-sm">Edit</button>
          <button @click="confirmDelete(pkg)" class="text-red-600 hover:underline text-sm">Hapus</button>
        </div>
      </div>
    </div>

    <Modal :show="showForm" :title="(editing ? 'Edit' : 'Tambah') + ' Paket MCU'" size="md" @close="showForm = false">
      <form @submit.prevent="savePackage" class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Paket</label>
          <BaseInput v-model="form.nama_paket" required :error="fieldError(errors, 'nama_paket')" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Deskripsi</label>
          <textarea v-model="form.deskripsi" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 outline-none dark:bg-slate-700 dark:border-slate-600 dark:text-slate-200" :class="fieldError(errors, 'deskripsi') ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-emerald-500'"></textarea>
          <p v-if="fieldError(errors, 'deskripsi')" class="mt-1 text-xs text-red-500">{{ fieldError(errors, 'deskripsi') }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Harga (Rp)</label>
          <BaseInput v-model.number="form.harga" type="number" min="0" required :error="fieldError(errors, 'harga')" />
        </div>
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-gray-300 dark:border-slate-600" />
            <label for="is_active" class="text-sm text-gray-700 dark:text-slate-300">Aktif</label>
          </div>
          <div class="flex items-center space-x-2">
            <input v-model="form.has_radiologi" type="checkbox" id="has_radiologi" class="rounded border-gray-300 dark:border-slate-600" />
            <label for="has_radiologi" class="text-sm text-gray-700 dark:text-slate-300">Butuh Radiologi</label>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Daftar Pemeriksaan</label>
          <div v-for="(item, i) in form.items" :key="i" class="flex space-x-2 mb-2">
            <input v-model="item.nama_pemeriksaan" placeholder="Nama" class="flex-1 px-2 py-1 border rounded text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-slate-200" />
            <input v-model="item.satuan" placeholder="Satuan" class="w-20 px-2 py-1 border rounded text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-slate-200" />
            <input v-model="item.nilai_normal" placeholder="Nilai normal" class="w-24 px-2 py-1 border rounded text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-slate-200" />
            <button type="button" @click="form.items.splice(i, 1)" class="text-red-500 text-sm">X</button>
          </div>
          <button type="button" @click="form.items.push({ nama_pemeriksaan: '', satuan: '', nilai_normal: '' })" class="text-emerald-600 hover:underline text-sm">+ Tambah Pemeriksaan</button>
        </div>
        <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
      </form>
      <template #footer>
        <button type="button" @click="showForm = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Batal</button>
        <button type="submit" @click="savePackage" :disabled="saving" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 text-sm">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
      </template>
    </Modal>

    <Modal :show="showDelete" title="Hapus Paket MCU" size="sm" @close="showDelete = false">
      <p class="text-gray-600 dark:text-slate-300">Yakin ingin menghapus paket <strong>{{ deletingPkg?.nama_paket }}</strong>?</p>
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
import { parseErrors, fieldError } from '../../utils/errors'
import api from '../../utils/axios'
import Modal from '../../components/Modal.vue'
import BaseInput from '../../components/BaseInput.vue'

const packages = ref([])
const showForm = ref(false)
const showDelete = ref(false)
const deletingPkg = ref(null)
const deleteError = ref('')
const editing = ref(null)
const saving = ref(false)
const formError = ref('')
const errors = ref({})
const form = reactive({ nama_paket: '', deskripsi: '', harga: 0, is_active: true, has_radiologi: false, items: [] })

function formatPrice(p) { return new Intl.NumberFormat('id-ID').format(p) }

function openForm() {
  editing.value = null
  form.nama_paket = ''; form.deskripsi = ''; form.harga = 0; form.is_active = true; form.has_radiologi = false; form.items = []
  showForm.value = true
}

function editPackage(pkg) {
  editing.value = pkg.id
  form.nama_paket = pkg.nama_paket
  form.deskripsi = pkg.deskripsi
  form.harga = pkg.harga
  form.is_active = pkg.is_active
  form.has_radiologi = pkg.has_radiologi ?? false
  form.items = pkg.items?.map(i => ({ nama_pemeriksaan: i.nama_pemeriksaan, satuan: i.satuan || '', nilai_normal: i.nilai_normal || '' })) || []
  showForm.value = true
}

async function savePackage() {
  saving.value = true; formError.value = ''; errors.value = {}
  try {
    if (editing.value) {
      await api.put(`/admin/packages/${editing.value}`, form)
    } else {
      await api.post('/admin/packages', form)
    }
    showForm.value = false
    await fetchPackages()
  } catch (e) {
    const parsed = parseErrors(e)
    if (Object.keys(parsed).length > 0) {
      errors.value = parsed
    } else {
      formError.value = e.response?.data?.message || 'Gagal menyimpan'
    }
  }
  finally { saving.value = false }
}

function confirmDelete(pkg) {
  deletingPkg.value = pkg
  deleteError.value = ''
  showDelete.value = true
}

async function doDelete() {
  saving.value = true
  deleteError.value = ''
  try {
    await api.delete(`/admin/packages/${deletingPkg.value.id}`)
    showDelete.value = false
    deletingPkg.value = null
    await fetchPackages()
  } catch (e) {
    deleteError.value = e.response?.data?.message || 'Gagal menghapus'
  } finally { saving.value = false }
}

async function fetchPackages() {
  try { const res = await api.get('/admin/packages'); packages.value = res.data.data }
  catch (e) { console.error(e) }
}

onMounted(fetchPackages)
</script>
