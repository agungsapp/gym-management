<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateMembershipService
{
  public function handle(array $data): Membership
  {
    return DB::transaction(function () use ($data) {
      $member = Member::findOrFail($data['member_id']);
      $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

      $startDate = Carbon::parse($data['start_date'])->startOfDay();

      // Renewal logic
      $activeMembership = $member->memberships()
        ->where('status', 'active')
        ->whereDate('end_date', '>=', now())
        ->orderByDesc('end_date')
        ->first();

      if ($activeMembership && $startDate->lte($activeMembership->end_date)) {
        // Lanjutkan setelah membership aktif berakhir
        $startDate = $activeMembership->end_date->copy()->addDay();
      }

      $extraDays = (int) ($data['extra_days'] ?? 0);
      $durationDays = $this->resolveDurationInDays($plan) + $extraDays;
      $endDate = $startDate->copy()->addDays($durationDays)->subDay();

      $membership = Membership::create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'extra_days' => $extraDays,
        'status' => 'active',
        'referral_member_id' => $data['referral_member_id'] ?? null,
        'notes' => $data['notes'] ?? null,
        'created_by' => Auth::id(),
      ]);

      $originalPrice = $plan->price;
      $discountType = $data['discount_type'] ?? null;
      $discountValue = (int) ($data['discount_value'] ?? 0);
      $amount = $this->calculateAmount($originalPrice, $discountType, $discountValue);

      Payment::create([
        'payment_number' => Payment::generatePaymentNumber(),
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'original_price' => $originalPrice,
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'amount' => $amount,
        'payment_method' => $data['payment_method'],
        'payment_date' => $data['payment_date'] ?? now()->toDateString(),
        'notes' => $data['notes'] ?? null,
        'recorded_by' => Auth::id(),
      ]);

      // Deteksi membership pertama
      $isFirstMembership = $member->memberships()->count() === 1;

      if ($isFirstMembership && !empty($member->whatsapp)) {
        try {
          $relativePath = app(MemberCardService::class)->generate($member);
          $fullPath = storage_path('app/public/' . $relativePath);

          $pesan = "Halo *{$member->name}*!\n\n"
            . "Selamat bergabung sebagai member.\n"
            . "Kode member: *{$member->member_code}*\n\n"
            . "Tunjukkan barcode pada kartu ini saat check-in di gym.";

          app(WhatsAppGatewayService::class)->kirimPesanDenganGambar(
            $pesan,
            $fullPath,
            $member->whatsapp
          );
        } catch (\Throwable $e) {
          Log::error('Gagal kirim member card via WA Gateway: ' . $e->getMessage(), [
            'member_id' => $member->id,
          ]);
        }
      }

      return $membership->load(['plan', 'payment', 'referral']);
    });
  }

  private function resolveDurationInDays(MembershipPlan $plan): int
  {
    if ($plan->duration_unit === 'months') {
      return $plan->duration * 30; // sederhana untuk MVP
    }

    return $plan->duration;
  }

  private function calculateAmount(int $originalPrice, ?string $discountType, int $discountValue): int
  {
    if (!$discountType || $discountValue <= 0) {
      return $originalPrice;
    }

    if ($discountType === 'percent') {
      $discount = (int) round($originalPrice * ($discountValue / 100));
      return max(0, $originalPrice - $discount);
    }

    // amount
    return max(0, $originalPrice - $discountValue);
  }
}
