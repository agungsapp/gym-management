<script setup>
import ActiveIcon from '@/Components/Icons/ActiveIcon.vue';
import InactiveIcon from '@/Components/Icons/InactiveIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  member: Object,
  activeMembership: Object,
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
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
        <div class="flex gap-2">
          <Link :href="route('members.edit', member.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold uppercase rounded-lg hover:bg-blue-700">
            Edit
          </Link>
          <Link :href="route('members.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold uppercase rounded-lg hover:bg-gray-200">
            Kembali
          </Link>
          <Link :href="route('members.memberships.create', member.id)"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold uppercase rounded-lg hover:bg-green-700">
            Beli Membership
          </Link>
        </div>
      </div>
    </template>

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
            <div v-if="activeMembership" class="mt-6 p-4 bg-green-50 rounded-lg border border-green-100">
              <p class="text-xs text-green-600 uppercase font-semibold">Membership Aktif</p>
              <p class="font-medium text-gray-900">{{ activeMembership.plan?.name }}</p>
              <p class="text-sm text-gray-600">
                {{ formatDate(activeMembership.start_date) }}
                →
                {{ formatDate(activeMembership.end_date) }}
              </p>
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
                <p class="font-medium">{{ formatDate(member.birth_date) || '-' }}</p>
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
  </AuthenticatedLayout>
</template>