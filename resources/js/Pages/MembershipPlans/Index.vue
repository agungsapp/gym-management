<script setup>
import ActiveIcon from '@/Components/Icons/ActiveIcon.vue';
import BanIcon from '@/Components/Icons/BanIcon.vue';
import EditIcon from '@/Components/Icons/EditIcon.vue';
import InactiveIcon from '@/Components/Icons/InactiveIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineProps({
  plans: Array,
});

const destroy = (id) => {
  Swal.fire({
    title: 'Nonaktifkan Paket?',
    text: 'Paket yang sudah dinonaktifkan tidak akan muncul di pilihan membership baru.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Nonaktifkan',
    cancelButtonText: 'Batal',
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('membership-plans.destroy', id), {
        onSuccess: () => {
          Swal.fire({
            title: 'Berhasil!',
            text: 'Paket membership telah dinonaktifkan.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
          });
        },
      });
    }
  });
};
</script>

<template>
  <Head title="Paket Membership" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Paket Membership</h2>
    </template>

    <template #headnav>
      <div class="flex items-center justify-between">
        <Link :href="route('membership-plans.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
          + Tambah Paket
        </Link>
      </div>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
      <div class="p-6">
        <div v-if="plans.length === 0" class="text-center py-16 text-gray-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          <p class="mt-4 text-sm">Belum ada paket membership.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="plan in plans" :key="plan.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  {{ plan.name }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">
                  {{ plan.duration }} {{ plan.duration_unit === 'days' ? 'hari' : 'bulan' }}
                </td>
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  Rp {{ Number(plan.price).toLocaleString('id-ID') }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <span :class="plan.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-600'"
                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full">
                    <ActiveIcon class="me-1" v-if="plan.is_active" />
                    <InactiveIcon class="me-1" v-else />
                    {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('membership-plans.edit', plan.id)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                      <EditIcon />
                      Edit
                    </Link>

                    <button v-if="plan.is_active" @click="destroy(plan.id)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition">
                      <BanIcon />
                      Nonaktifkan
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>