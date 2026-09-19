<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import debounce from 'lodash/debounce';
import EditIcon from '@/Components/Icons/EditIcon.vue';
import InfoIcon from '@/Components/Icons/InfoIcon.vue';
import BanIcon from '@/Components/Icons/BanIcon.vue';
import ActiveIcon from '@/Components/Icons/ActiveIcon.vue';
import InactiveIcon from '@/Components/Icons/InactiveIcon.vue';

const props = defineProps({
  members: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');

const destroy = (id) => {
  Swal.fire({
    title: 'Nonaktifkan Member?',
    text: 'Member yang dinonaktifkan tidak bisa digunakan untuk check-in.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Nonaktifkan',
    cancelButtonText: 'Batal',
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('members.destroy', id), {
        onSuccess: () => {
          Swal.fire({
            title: 'Berhasil!',
            text: 'Member telah dinonaktifkan.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
          });
        },
      });
    }
  });
};

// Search dengan debounce
watch(search, debounce((value) => {
  router.get(route('members.index'), {
    search: value,
    type: type.value,
  }, {
    preserveState: true,
    replace: true,
  });
}, 300));

const filterType = () => {
  router.get(route('members.index'), {
    search: search.value,
    type: type.value,
  }, {
    preserveState: true,
    replace: true,
  });
};
</script>

<template>
  <Head title="Members" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Members</h2>
    </template>

    <template #headnav>
      <div class="flex items-center justify-between w-full">
        <Link :href="route('members.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Member
        </Link>
      </div>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
      <div class="p-6">
        <!-- Filter -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <div class="flex-1">
            <input v-model="search" type="text" placeholder="Cari nama, kode member, atau WhatsApp..."
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
          </div>
          <div>
            <select v-model="type" @change="filterType"
              class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="">Semua Tipe</option>
              <option value="pelajar">Pelajar</option>
              <option value="non_pelajar">Non Pelajar</option>
            </select>
          </div>
        </div>

        <div v-if="members.data.length === 0" class="text-center py-16 text-gray-500">
          <p class="text-sm">Belum ada member.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">WhatsApp</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="member in members.data" :key="member.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-4 text-sm font-medium text-blue-600">
                  {{ member.member_code }}
                </td>
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  {{ member.name }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <span :class="member.type === 'pelajar'
                    ? 'bg-purple-100 text-purple-800'
                    : 'bg-blue-100 text-blue-800'"
                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full">
                    {{ member.type === 'pelajar' ? 'Pelajar' : 'Non Pelajar' }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">
                  {{ member.whatsapp || '-' }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <span :class="member.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-600'"
                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full">
                    <ActiveIcon class="me-1" v-if="member.is_active" />
                    <InactiveIcon class="me-1" v-else />
                    {{ member.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('members.show', member.id)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                      <InfoIcon />
                      Detail
                    </Link>
                    <Link :href="route('members.edit', member.id)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                      <EditIcon />
                      Edit
                    </Link>
                    <button v-if="member.is_active" @click="destroy(member.id)"
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

        <!-- Pagination sederhana -->
        <div v-if="members.links && members.links.length > 3" class="mt-6 flex justify-center gap-1">
          <template v-for="(link, index) in members.links" :key="index">
            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1.5 text-sm rounded-md" :class="link.active
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" />
            <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>