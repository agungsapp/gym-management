<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, nextTick, onMounted } from 'vue';
import ActiveIcon from '@/Components/Icons/ActiveIcon.vue';
import InactiveIcon from '@/Components/Icons/InactiveIcon.vue';

const props = defineProps({
  todayAttendances: {
    type: Array,
    default: () => [],
  },
  todayCount: {
    type: Number,
    default: 0,
  },
  result: {
    type: Object,
    default: null,
  },
  errors: {
    type: Object,
    default: null,
  },
});

const form = useForm({
  member_code: '',
  method: 'manual',
});

const inputRef = ref(null);
const localResult = ref(props.result);
const localErrors = ref(props.errors);

watch(
  () => props.result,
  (val) => {
    localResult.value = val;
  }
);

watch(
  () => props.errors,
  (val) => {
    localErrors.value = val;
  }
);

const focusInput = async () => {
  await nextTick();
  inputRef.value?.focus();
  inputRef.value?.select();
};

const submit = () => {
  if (!form.member_code.trim()) return;

  form.post(route('check-in.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.member_code = '';
      focusInput();
    },
    onError: () => {
      focusInput();
    },
    onFinish: () => {
      focusInput();
    },
  });
};

const formatTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

onMounted(() => {
  focusInput();
});
</script>

<template>
  <Head title="Check-in" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-xl font-semibold text-gray-800">Check-in</h2>
        <span class="text-sm text-gray-500">
          Hari ini:
          <strong class="text-blue-600">{{ todayCount }}</strong>
          check-in
        </span>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Form Check-in -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6">
          <form @submit.prevent="submit" class="max-w-xl mx-auto space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                Kode Member
              </label>
              <input ref="inputRef" v-model="form.member_code" type="text" placeholder="EP-0001 / EM-0001"
                autocomplete="off"
                class="w-full text-center text-2xl font-bold tracking-wider rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase"
                :disabled="form.processing" />
              <p class="mt-2 text-center text-xs text-gray-500">
                Ketik kode member lalu Enter, atau tembak barcode scanner ke kolom ini
              </p>
            </div>

            <button type="submit" :disabled="form.processing || !form.member_code.trim()"
              class="w-full inline-flex justify-center items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50 transition">
              {{ form.processing ? 'Memproses...' : 'Check-in' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Hasil Check-in -->
      <div v-if="localErrors?.member_code" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <div class="text-red-600 text-4xl mb-2">✕</div>
        <h3 class="text-lg font-bold text-red-700">CHECK-IN DITOLAK</h3>
        <p class="mt-2 text-red-600">{{ localErrors.member_code[0] }}</p>
      </div>

      <div v-else-if="localResult" class="rounded-xl p-6 text-center border" :class="localResult.success
        ? 'bg-green-50 border-green-200'
        : 'bg-red-50 border-red-200'">
        <div class="text-4xl mb-2" :class="localResult.success ? 'text-green-600' : 'text-red-600'">
          {{ localResult.success ? '✓' : '✕' }}
        </div>

        <h3 class="text-lg font-bold" :class="localResult.success ? 'text-green-700' : 'text-red-700'">
          {{ localResult.success ? 'CHECK-IN BERHASIL' : 'CHECK-IN DITOLAK' }}
        </h3>

        <div class="mt-4 space-y-1">
          <p class="text-xl font-bold text-gray-900">
            {{ localResult.member?.name }}
          </p>
          <p class="text-blue-600 font-medium text-lg">
            {{ localResult.member?.member_code }}
          </p>
        </div>

        <div v-if="localResult.success && localResult.membership" class="mt-4 text-sm text-gray-700 space-y-1">
          <p>
            Membership:
            <strong>{{ localResult.membership.plan?.name }}</strong>
          </p>
          <p>
            Berlaku sampai:
            <strong>{{ formatDate(localResult.membership.end_date) }}</strong>
          </p>
        </div>

        <p v-if="!localResult.success" class="mt-3 text-sm"
          :class="localResult.success ? 'text-green-600' : 'text-red-600'">
          {{ localResult.message }}
        </p>
      </div>

      <!-- List Check-in Hari Ini -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
            Check-in Hari Ini
          </h3>

          <div v-if="todayAttendances.length === 0" class="text-center py-10 text-gray-500 text-sm">
            Belum ada check-in hari ini.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Metode</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Operator</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in todayAttendances" :key="item.id" class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ formatTime(item.check_in_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-blue-600 font-medium">
                    {{ item.member?.member_code }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900">
                    {{ item.member?.name }}
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