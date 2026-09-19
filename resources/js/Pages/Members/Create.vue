<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const form = useForm({
  type: 'non_pelajar',
  name: '',
  whatsapp: '',
  photo: null,
  gender: '',
  birth_date: '',
  address: '',
  is_active: true,
});

const photoPreview = ref(null);

import { ref } from 'vue';

const onPhotoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const submit = () => {
  form.post(route('members.store'), {
    forceFormData: true,
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Member berhasil ditambahkan.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
    },
  });
};
</script>

<template>
  <Head title="Tambah Member" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Tambah Member</h2>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
      <form @submit.prevent="submit" class="p-6 space-y-6 max-w-3xl">

        <!-- Tipe Member -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Member</label>
          <div class="flex gap-4">
            <label class="inline-flex items-center">
              <input type="radio" v-model="form.type" value="pelajar" class="text-blue-600 focus:ring-blue-500" />
              <span class="ml-2 text-sm">Pelajar (EP)</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" v-model="form.type" value="non_pelajar" class="text-blue-600 focus:ring-blue-500" />
              <span class="ml-2 text-sm">Non Pelajar (EM)</span>
            </label>
          </div>
          <p class="mt-1 text-xs text-gray-500">Kode member akan digenerate otomatis sesuai tipe.</p>
        </div>

        <!-- Nama -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input v-model="form.name" type="text"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required />
          <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- WhatsApp -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
          <input v-model="form.whatsapp" type="text" placeholder="08xxxxxxxxxx"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <!-- Foto -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Foto Member</label>
          <input type="file" accept="image/*" @change="onPhotoChange"
            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
          <div v-if="photoPreview" class="mt-3">
            <img :src="photoPreview" class="h-32 w-32 object-cover rounded-lg border" />
          </div>
        </div>

        <!-- Gender & Birth Date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
            <select v-model="form.gender"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="">Pilih</option>
              <option value="laki-laki">Laki-laki</option>
              <option value="perempuan">Perempuan</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
            <input v-model="form.birth_date" type="date"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
          </div>
        </div>

        <!-- Alamat -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Alamat (opsional)</label>
          <textarea v-model="form.address" rows="3"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <!-- Status -->
        <div class="flex items-center">
          <input id="is_active" v-model="form.is_active" type="checkbox"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
          <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
        </div>

        <!-- Buttons -->
        <div class="flex items-center gap-4 pt-4">
          <button type="submit" :disabled="form.processing"
            class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50 transition">
            Simpan Member
          </button>
          <Link :href="route('members.index')" class="text-gray-600 hover:text-gray-900 text-sm">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>