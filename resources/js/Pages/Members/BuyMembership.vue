<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  member: Object,
  plans: Array,
  referralMembers: Array,
  activeMembership: Object,
});

const form = useForm({
  membership_plan_id: '',
  start_date: new Date().toISOString().slice(0, 10),
  discount_type: null,
  discount_value: 0,
  extra_days: 0,
  payment_method: 'cash',
  payment_date: new Date().toISOString().slice(0, 10),
  notes: '',
  referral_member_id: null,
});

const referralSearch = ref('');

const selectedPlan = computed(() =>
  props.plans.find((p) => p.id === Number(form.membership_plan_id))
);

const originalPrice = computed(() => selectedPlan.value?.price ?? 0);

const discountAmount = computed(() => {
  if (!form.discount_type || !form.discount_value) return 0;
  if (form.discount_type === 'percent') {
    return Math.round(originalPrice.value * (form.discount_value / 100));
  }
  return form.discount_value;
});

const totalAmount = computed(() =>
  Math.max(0, originalPrice.value - discountAmount.value)
);

const estimatedEndDate = computed(() => {
  if (!selectedPlan.value || !form.start_date) return '-';

  let days = selectedPlan.value.duration_unit === 'months'
    ? selectedPlan.value.duration * 30
    : selectedPlan.value.duration;

  days += Number(form.extra_days || 0);

  const start = new Date(form.start_date);
  start.setDate(start.getDate() + days - 1);
  return start.toISOString().slice(0, 10);
});

const filteredReferrals = computed(() => {
  if (!referralSearch.value) return props.referralMembers.slice(0, 20);
  const q = referralSearch.value.toLowerCase();
  return props.referralMembers.filter(
    (m) =>
      m.name.toLowerCase().includes(q) ||
      m.member_code.toLowerCase().includes(q)
  ).slice(0, 20);
});

const submit = () => {
  form.post(route('members.memberships.store', props.member.id), {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Membership berhasil dibeli.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Gagal!',
        text: 'Periksa kembali data transaksi.',
        icon: 'error',
      });
    },
  });
};
</script>

<template>
  <Head title="Beli Membership" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">
        Beli Membership — {{ member.name }} ({{ member.member_code }})
      </h2>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
      <form @submit.prevent="submit" class="p-6 space-y-6 max-w-3xl">

        <!-- Info membership aktif -->
        <div v-if="activeMembership" class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm">
          Member masih memiliki membership aktif sampai
          <strong>{{ activeMembership.end_date }}</strong>.
          Jika dilanjutkan, periode baru akan dimulai setelah tanggal tersebut.
        </div>

        <!-- Pilih Paket -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Paket Membership</label>
          <select v-model="form.membership_plan_id"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required>
            <option value="" disabled>Pilih paket</option>
            <option v-for="plan in plans" :key="plan.id" :value="plan.id">
              {{ plan.name }} —
              {{ plan.duration }} {{ plan.duration_unit === 'days' ? 'hari' : 'bulan' }} —
              Rp {{ Number(plan.price).toLocaleString('id-ID') }}
            </option>
          </select>
        </div>

        <!-- Start Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
          <input v-model="form.start_date" type="date"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required />
          <p class="mt-1 text-xs text-gray-500">
            Estimasi berakhir: <strong>{{ estimatedEndDate }}</strong>
          </p>
        </div>

        <!-- Extra Days -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Extra Durasi (hari)</label>
          <input v-model="form.extra_days" type="number" min="0"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <!-- Diskon -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tipe Diskon</label>
            <select v-model="form.discount_type"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option :value="null">Tanpa Diskon</option>
              <option value="amount">Nominal (Rp)</option>
              <option value="percent">Persen (%)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nilai Diskon</label>
            <input v-model="form.discount_value" type="number" min="0" :disabled="!form.discount_type"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100" />
          </div>
        </div>

        <!-- Ringkasan harga -->
        <div class="p-4 bg-gray-50 rounded-lg text-sm space-y-1">
          <div class="flex justify-between">
            <span>Harga Paket</span>
            <span>Rp {{ Number(originalPrice).toLocaleString('id-ID') }}</span>
          </div>
          <div class="flex justify-between text-red-600">
            <span>Diskon</span>
            <span>- Rp {{ Number(discountAmount).toLocaleString('id-ID') }}</span>
          </div>
          <div class="flex justify-between font-bold text-lg border-t pt-2">
            <span>Total Bayar</span>
            <span>Rp {{ Number(totalAmount).toLocaleString('id-ID') }}</span>
          </div>
        </div>

        <!-- Metode Pembayaran -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
          <div class="flex flex-wrap gap-4">
            <label class="inline-flex items-center gap-2">
              <input type="radio" v-model="form.payment_method" value="cash" class="text-blue-600" />
              <span>Tunai</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" v-model="form.payment_method" value="transfer" class="text-blue-600" />
              <span>Transfer</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" v-model="form.payment_method" value="qris" class="text-blue-600" />
              <span>QRIS</span>
            </label>
          </div>
        </div>

        <!-- Tanggal Bayar -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
          <input v-model="form.payment_date" type="date"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required />
        </div>

        <!-- Referral -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Referral (opsional)</label>
          <input v-model="referralSearch" type="text" placeholder="Cari nama / kode member..."
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
          <select v-model="form.referral_member_id"
            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option :value="null">— Tidak ada —</option>
            <option v-for="m in filteredReferrals" :key="m.id" :value="m.id">
              {{ m.member_code }} — {{ m.name }}
            </option>
          </select>
        </div>

        <!-- Catatan -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Catatan</label>
          <textarea v-model="form.notes" rows="3"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4 pt-2">
          <button type="submit" :disabled="form.processing || !form.membership_plan_id"
            class="inline-flex items-center px-6 py-2.5 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 disabled:opacity-50 transition">
            Checkout
          </button>
          <Link :href="route('members.show', member.id)" class="text-sm text-gray-600 hover:text-gray-900">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>