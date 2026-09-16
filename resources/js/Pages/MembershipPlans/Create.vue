<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  duration: 30,
  duration_unit: 'days',
  price: 150000,
  description: '',
  is_active: true,
});

const submit = () => {
  form.post(route('membership-plans.store'));
};
</script>

<template>
  <Head title="Tambah Paket Membership" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Tambah Paket Membership</h2>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <form @submit.prevent="submit" class="p-6 space-y-6 max-w-2xl">
        <!-- Nama -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Nama Paket</label>
          <input v-model="form.name" type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Contoh: 1 Bulan" required />
          <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- Durasi -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Durasi</label>
            <input v-model="form.duration" type="number" min="1"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Satuan</label>
            <select v-model="form.duration_unit"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="days">Hari</option>
              <option value="months">Bulan</option>
            </select>
          </div>
        </div>

        <!-- Harga -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
          <input v-model="form.price" type="number" min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required />
          <div v-if="form.errors.price" class="text-red-600 text-sm mt-1">{{ form.errors.price }}</div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Deskripsi (opsional)</label>
          <textarea v-model="form.description" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <!-- Status -->
        <div class="flex items-center">
          <input id="is_active" v-model="form.is_active" type="checkbox"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
          <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
        </div>

        <!-- Buttons -->
        <div class="flex items-center gap-4">
          <button type="submit" :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50">
            Simpan
          </button>
          <Link :href="route('membership-plans.index')" class="text-gray-600 hover:text-gray-900">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>