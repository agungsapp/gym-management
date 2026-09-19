<script setup>
import ActiveIcon from '@/Components/Icons/ActiveIcon.vue';
import InactiveIcon from '@/Components/Icons/InactiveIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  member: Object,
  activeMembership: Object,
  attendances: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const doCheckIn = () => {
  Swal.fire({
    title: 'Check-in Member?',
    text: `${props.member.name} (${props.member.member_code})`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Check-in',
    cancelButtonText: 'Batal',
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('members.check-in', props.member.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          const flash = page.props.flash || {};
          if (flash.success) {
            Swal.fire({
              title: 'Berhasil!',
              text: flash.success,
              icon: 'success',
              timer: 2000,
              showConfirmButton: false,
            });
          } else if (flash.error) {
            Swal.fire({
              title: 'Ditolak!',
              text: flash.error,
              icon: 'error',
            });
          }
        },
        onError: (errors) => {
          const msg = errors.member_code?.[0] || 'Check-in gagal.';
          Swal.fire({
            title: 'Ditolak!',
            text: msg,
            icon: 'error',
          });
        },
      });
    }
  });
};
</script>

<template>
  <Head :title="`Member - ${member.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Detail Member</h2>
    </template>

    <template #headnav>
      <div class="flex items-center justify-between w-full">
        <div class="flex flex-wrap gap-2">
          <button type="button" @click="doCheckIn"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-lg hover:bg-indigo-700">
            Check-in
          </button>

          <Link :href="route('members.memberships.create', member.id)"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold uppercase rounded-lg hover:bg-green-700">
            Beli Membership
          </Link>

          <Link :href="route('members.edit', member.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold uppercase rounded-lg hover:bg-blue-700">
            Edit
          </Link>

          <Link :href="route('members.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold uppercase rounded-lg hover:bg-gray-200">
            Kembali
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Info Member -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6">
          <div class="flex flex-col sm:flex-row gap-6">
            <!-- Foto -->
            <div class="flex-shrink-0">
              <img v-if="member.photo" :src="`/storage/${member.photo}`"
                class="h-40 w-40 object-cover rounded-xl border" />
              <div v-else
                class="h-40 w-40 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-4xl font-bold">
                {{ member.name.charAt(0).toUpperCase() }}
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 space-y-3">
              <div v-if="activeMembership" class="p-4 bg-green-50 rounded-lg border border-green-100">
                <p class="text-xs text-green-600 uppercase font-semibold">Membership Aktif</p>
                <p class="font-medium text-gray-900">{{ activeMembership.plan?.name }}</p>
                <p class="text-sm text-gray-600">
                  {{ formatDate(activeMembership.start_date) }}
                  →
                  {{ formatDate(activeMembership.end_date) }}
                </p>
              </div>

              <div v-else class="p-4 bg-red-50 rounded-lg border border-red-100">
                <p class="text-xs text-red-600 uppercase font-semibold">Tidak Ada Membership Aktif</p>
                <p class="text-sm text-red-600">Member perlu membeli / renewal membership.</p>
              </div>

              <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ member.name }}</h3>
                <p class="text-blue-600 font-medium text-lg">{{ member.member_code }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                  <p class="text-xs text-gray-500 uppercase">Tipe</p>
                  <p class="font-medium">
                    {{ member.type === 'pelajar' ? 'Pelajar' : 'Non Pelajar' }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase">WhatsApp</p>
                  <p class="font-medium">{{ member.whatsapp || '-' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase">Jenis Kelamin</p>
                  <p class="font-medium capitalize">{{ member.gender || '-' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase">Tanggal Lahir</p>
                  <p class="font-medium">{{ formatDate(member.birth_date) }}</p>
                </div>
                <div class="sm:col-span-2">
                  <p class="text-xs text-gray-500 uppercase">Alamat</p>
                  <p class="font-medium">{{ member.address || '-' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase">Status</p>
                  <span :class="member.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-600'"
                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full">
                    <ActiveIcon class="me-1" v-if="member.is_active" />
                    <InactiveIcon class="me-1" v-else />
                    {{ member.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Attendance -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
            Riwayat Check-in
          </h3>

          <div v-if="attendances.length === 0" class="text-center py-10 text-gray-500 text-sm">
            Belum ada riwayat check-in.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Metode</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Operator</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in attendances" :key="item.id" class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ formatDateTime(item.check_in_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600 capitalize">
                    {{ item.method }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ item.operator?.name || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>