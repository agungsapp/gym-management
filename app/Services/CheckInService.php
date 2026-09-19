<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Membership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CheckInService
{
  /** Interval anti-duplikat (menit) */
  public const DUPLICATE_INTERVAL_MINUTES = 5;

  public function handle(string $memberCode, string $method = 'manual'): array
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

    if (!$membership) {
      return [
        'success' => false,
        'reason' => 'expired',
        'member' => $member,
        'message' => 'Membership telah expired. Silakan melakukan renewal.',
      ];
    }

    // Cek duplikat
    $recent = Attendance::where('member_id', $member->id)
      ->where('check_in_at', '>=', now()->subMinutes(self::DUPLICATE_INTERVAL_MINUTES))
      ->exists();

    if ($recent) {
      return [
        'success' => false,
        'reason' => 'duplicate',
        'member' => $member,
        'membership' => $membership->load('plan'),
        'message' => 'Baru saja check-in dalam ' . self::DUPLICATE_INTERVAL_MINUTES . ' menit terakhir.',
      ];
    }

    $attendance = Attendance::create([
      'member_id' => $member->id,
      'membership_id' => $membership->id,
      'check_in_at' => now(),
      'method' => $method,
      'operator_id' => Auth::id(),
    ]);

    return [
      'success' => true,
      'member' => $member,
      'membership' => $membership->load('plan'),
      'attendance' => $attendance,
      'message' => 'Check-in berhasil.',
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
