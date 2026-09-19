<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
  todayAttendances: { type: Array, default: () => [] },
  todayCount: { type: Number, default: 0 },
  result: { type: Object, default: null },
  errors: { type: Object, default: null },
});

const form = useForm({
  member_code: '',
  method: 'manual',
  allow_grace: false,
});

const inputRef = ref(null);
const localResult = ref(props.result);
const localErrors = ref(props.errors);

const scanning = ref(false);
const scannerError = ref('');
let html5QrCode = null;

watch(() => props.result, (val) => { localResult.value = val; });
watch(() => props.errors, (val) => { localErrors.value = val; });

const pageResultNeedsGrace = () => {
  return !!(
    localResult.value &&
    !localResult.value.success &&
    localResult.value.needs_grace_confirmation
  );
};

const allowGraceCheckIn = () => {
  if (!form.member_code.trim()) return;

  form.allow_grace = true;

  form.post(route('check-in.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.member_code = '';
      form.allow_grace = false;
      focusInput();
    },
    onFinish: () => {
      form.allow_grace = false;
    },
  });
};

const dismissGrace = () => {
  form.member_code = '';
  form.allow_grace = false;
  localResult.value = null;
  focusInput();
};

const cancelCheckIn = (attendanceId) => {
  Swal.fire({
    title: 'Batalkan check-in?',
    input: 'text',
    inputPlaceholder: 'Alasan (opsional)',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    confirmButtonText: 'Ya, batalkan',
    cancelButtonText: 'Tidak',
    reverseButtons: true,
  }).then((res) => {
    if (res.isConfirmed) {
      router.post(
        route('check-in.cancel', attendanceId),
        { reason: res.value || null },
        {
          preserveScroll: true,
          onSuccess: () => {
            localResult.value = null;
            Swal.fire({
              title: 'Dibatalkan',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false,
            });
          },
        }
      );
    }
  });
};

const focusInput = async () => {
  await nextTick();
  inputRef.value?.focus();
  inputRef.value?.select();
};

const submit = (method = 'manual') => {
  if (!form.member_code.trim()) return;
  form.method = method;
  form.allow_grace = false;

  form.post(route('check-in.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // props.result baru masuk setelah response; cek di nextTick
      nextTick(() => {
        if (!pageResultNeedsGrace()) {
          form.member_code = '';
        }
        focusInput();
      });
    },
  });
};

const startScanner = async () => {
  scannerError.value = '';
  scanning.value = true;

  await nextTick();

  try {
    html5QrCode = new Html5Qrcode('qr-reader');

    await html5QrCode.start(
      { facingMode: 'environment' }, // kamera belakang HP
      {
        fps: 10,
        qrbox: { width: 250, height: 250 },
      },
      async (decodedText) => {
        // QR terbaca
        const code = decodedText.trim().toUpperCase();
        form.member_code = code;

        await stopScanner();
        submit('barcode'); // method barcode di attendance
      },
      () => {
        // ignore scan miss
      }
    );
  } catch (e) {
    scannerError.value = e?.message || 'Tidak bisa mengakses kamera.';
    scanning.value = false;
  }
};

const stopScanner = async () => {
  try {
    if (html5QrCode?.isScanning) {
      await html5QrCode.stop();
    }
    html5QrCode?.clear();
  } catch (_) {
    // ignore
  } finally {
    html5QrCode = null;
    scanning.value = false;
  }
};

onMounted(() => focusInput());

onBeforeUnmount(() => {
  stopScanner();
});

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
      <!-- Form -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
        <div class="p-6">
          <form @submit.prevent="submit('manual')" class="max-w-xl mx-auto space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                Kode Member
              </label>
              <input ref="inputRef" v-model="form.member_code" type="text" placeholder="EP-0001 / EM-0001"
                autocomplete="off"
                class="w-full text-center text-2xl font-bold tracking-wider rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase"
                :disabled="form.processing" />
              <p class="mt-2 text-center text-xs text-gray-500">
                Ketik kode, tembak scanner USB, atau scan QR lewat kamera
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <button type="submit" :disabled="form.processing || !form.member_code.trim()"
                class="inline-flex justify-center items-center px-6 py-3 bg-blue-600 rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50 transition">
                {{ form.processing ? 'Memproses...' : 'Check-in' }}
              </button>

              <button type="button" v-if="!scanning" @click="startScanner"
                class="inline-flex justify-center items-center px-6 py-3 bg-indigo-600 rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                Scan QR
              </button>

              <button type="button" v-else @click="stopScanner"
                class="inline-flex justify-center items-center px-6 py-3 bg-red-600 rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 transition">
                Stop Kamera
              </button>
            </div>
          </form>

          <!-- Area kamera -->
          <div v-show="scanning" class="mt-6 max-w-md mx-auto">
            <div id="qr-reader" class="overflow-hidden rounded-xl border border-gray-200"></div>
            <p class="mt-2 text-center text-xs text-gray-500">
              Arahkan kamera ke QR code di member card
            </p>
          </div>

          <p v-if="scannerError" class="mt-3 text-center text-sm text-red-600">
            {{ scannerError }}
          </p>
        </div>
      </div>

      <!-- Hasil (tetap pakai blok result/errors yang sudah ada) -->
      <!-- Error validasi (member tidak ketemu / nonaktif) -->
      <div v-if="localErrors?.member_code" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <div class="text-red-600 text-4xl mb-2">✕</div>
        <h3 class="text-lg font-bold text-red-700">CHECK-IN DITOLAK</h3>
        <p class="mt-2 text-red-600">{{ localErrors.member_code[0] }}</p>
      </div>

      <!-- Membership expired → konfirmasi toleransi -->
      <div v-else-if="localResult && !localResult.success && localResult.needs_grace_confirmation"
        class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
        <div class="text-amber-600 text-4xl mb-2">!</div>
        <h3 class="text-lg font-bold text-amber-800">MEMBERSHIP EXPIRED</h3>

        <div class="mt-4 flex flex-col items-center gap-3">
          <img v-if="localResult.member?.photo" :src="`/storage/${localResult.member.photo}`"
            class="h-28 w-28 object-cover rounded-xl border" />
          <div v-else
            class="h-28 w-28 rounded-xl bg-gray-200 flex items-center justify-center text-3xl font-bold text-gray-500">
            {{ localResult.member?.name?.charAt(0) }}
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900">{{ localResult.member?.name }}</p>
            <p class="text-blue-600 font-medium">{{ localResult.member?.member_code }}</p>
          </div>
          <p class="text-sm text-amber-700">{{ localResult.message }}</p>
        </div>

        <div class="mt-6 flex justify-center gap-3">
          <button type="button" @click="dismissGrace"
            class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold uppercase">
            Tolak
          </button>
          <button type="button" @click="allowGraceCheckIn"
            class="px-5 py-2.5 rounded-lg bg-amber-600 text-white text-sm font-semibold uppercase hover:bg-amber-700">
            Izinkan
          </button>
        </div>
      </div>

      <!-- Sukses (verified / grace) -->
      <div v-else-if="localResult && localResult.success" class="rounded-xl p-6 text-center border" :class="localResult.reason === 'grace'
        ? 'bg-amber-50 border-amber-200'
        : 'bg-green-50 border-green-200'">
        <div class="text-4xl mb-2" :class="localResult.reason === 'grace' ? 'text-amber-600' : 'text-green-600'">
          ✓
        </div>
        <h3 class="text-lg font-bold" :class="localResult.reason === 'grace' ? 'text-amber-800' : 'text-green-700'">
          {{ localResult.reason === 'grace' ? 'CHECK-IN TOLERANSI' : 'CHECK-IN BERHASIL' }}
        </h3>

        <div class="mt-4 flex flex-col items-center gap-3">
          <img v-if="localResult.member?.photo" :src="`/storage/${localResult.member.photo}`"
            class="h-32 w-32 object-cover rounded-xl border shadow" />
          <div v-else
            class="h-32 w-32 rounded-xl bg-gray-200 flex items-center justify-center text-3xl font-bold text-gray-500">
            {{ localResult.member?.name?.charAt(0) }}
          </div>

          <p class="text-xl font-bold text-gray-900">{{ localResult.member?.name }}</p>
          <p class="text-blue-600 font-medium text-lg">{{ localResult.member?.member_code }}</p>

          <div v-if="localResult.membership" class="text-sm text-gray-700 space-y-1">
            <p>Membership: <strong>{{ localResult.membership.plan?.name }}</strong></p>
            <p>Berlaku sampai: <strong>{{ formatDate(localResult.membership.end_date) }}</strong></p>
          </div>
        </div>

        <button v-if="localResult.attendance?.id" type="button" @click="cancelCheckIn(localResult.attendance.id)"
          class="mt-6 text-sm text-red-600 hover:text-red-800 font-medium underline">
          Batalkan check-in
        </button>
      </div>

      <!-- Gagal lain (duplikat, dll) -->
      <div v-else-if="localResult && !localResult.success"
        class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <div class="text-red-600 text-4xl mb-2">✕</div>
        <h3 class="text-lg font-bold text-red-700">CHECK-IN DITOLAK</h3>
        <div class="mt-4 space-y-1">
          <p class="text-xl font-bold text-gray-900">{{ localResult.member?.name }}</p>
          <p class="text-blue-600 font-medium text-lg">{{ localResult.member?.member_code }}</p>
        </div>
        <p class="mt-3 text-sm text-red-600">{{ localResult.message }}</p>
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
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
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
                  <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize" :class="{
                      'bg-green-100 text-green-800': item.status === 'verified',
                      'bg-amber-100 text-amber-800': item.status === 'grace',
                      'bg-gray-100 text-gray-500 line-through': item.status === 'cancelled',
                    }">
                      {{ item.status || 'verified' }}
                    </span>
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