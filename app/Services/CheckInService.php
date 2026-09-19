<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Membership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CheckInService
{
  public const DUPLICATE_INTERVAL_MINUTES = 5;

  public function handle(string $memberCode, string $method = 'manual', bool $allowGrace = false): array
  {
    $member = Member::where('member_code', strtoupper(trim($memberCode)))->first();

    if (!$member) {
      throw ValidationException::withMessages([
        'member_code' => 'Member tidak ditemukan.',
      ]);
    }

    if (!$member->is_active) {
      throw ValidationException::withMessages([
        'member_code' => 'Member nonaktif. Tidak bisa check-in.',
      ]);
    }

    $membership = $this->findActiveMembership($member);

    // Membership tidak aktif
    if (!$membership) {
      if (!$allowGrace) {
        return [
          'success' => false,
          'reason' => 'expired',
          'needs_grace_confirmation' => true,
          'member' => $member->only(['id', 'name', 'member_code', 'photo', 'type']),
          'message' => 'Membership telah expired. Izinkan masuk (toleransi)?',
        ];
      }

      // Operator izinkan toleransi
      return $this->createAttendance($member, null, $method, 'grace');
    }

    // Cek duplikat (abaikan yang cancelled)
    $recent = Attendance::where('member_id', $member->id)
      ->whereIn('status', ['verified', 'grace'])
      ->where('check_in_at', '>=', now()->subMinutes(self::DUPLICATE_INTERVAL_MINUTES))
      ->exists();

    if ($recent) {
      return [
        'success' => false,
        'reason' => 'duplicate',
        'needs_grace_confirmation' => false,
        'member' => $member->only(['id', 'name', 'member_code', 'photo', 'type']),
        'membership' => $membership->load('plan'),
        'message' => 'Baru saja check-in dalam ' . self::DUPLICATE_INTERVAL_MINUTES . ' menit terakhir.',
      ];
    }

    return $this->createAttendance($member, $membership, $method, 'verified');
  }

  public function cancel(Attendance $attendance, ?string $reason = null): array
  {
    if ($attendance->status === 'cancelled') {
      return [
        'success' => false,
        'message' => 'Check-in ini sudah dibatalkan.',
      ];
    }

    if (!$attendance->isCancellable()) {
      return [
        'success' => false,
        'message' => 'Batas waktu pembatalan (1 hari) sudah lewat.',
      ];
    }

    $attendance->update([
      'status' => 'cancelled',
      'cancelled_at' => now(),
      'cancelled_by' => Auth::id(),
      'cancel_reason' => $reason,
    ]);

    return [
      'success' => true,
      'message' => 'Check-in berhasil dibatalkan.',
      'attendance' => $attendance->fresh(),
    ];
  }

  private function createAttendance(
    Member $member,
    ?Membership $membership,
    string $method,
    string $status
  ): array {
    $attendance = Attendance::create([
      'member_id' => $member->id,
      'membership_id' => $membership?->id,
      'check_in_at' => now(),
      'method' => $method,
      'status' => $status,
      'operator_id' => Auth::id(),
    ]);

    return [
      'success' => true,
      'reason' => $status === 'grace' ? 'grace' : 'ok',
      'needs_grace_confirmation' => false,
      'member' => $member->only(['id', 'name', 'member_code', 'photo', 'type']),
      'membership' => $membership?->load('plan'),
      'attendance' => $attendance,
      'message' => $status === 'grace'
        ? 'Check-in toleransi (membership expired) berhasil.'
        : 'Check-in berhasil.',
    ];
  }

  private function findActiveMembership(Member $member): ?Membership
  {
    return $member->memberships()
      ->where('status', 'active')
      ->whereDate('start_date', '<=', now())
      ->whereDate('end_date', '>=', now())
      ->orderByDesc('end_date')
      ->first();
  }
}
