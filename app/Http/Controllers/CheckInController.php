<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Services\CheckInService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function index()
    {
        return Inertia::render('CheckIn/Index', $this->pageData());
    }

    public function store(Request $request, CheckInService $service)
    {
        $validated = $request->validate([
            'member_code' => 'required|string|max:30',
            'method' => 'nullable|in:manual,barcode',
            'allow_grace' => 'nullable|boolean',
        ]);

        $result = null;
        $errorBag = null;

        try {
            $result = $service->handle(
                $validated['member_code'],
                $validated['method'] ?? 'manual',
                (bool) ($validated['allow_grace'] ?? false)
            );
        } catch (ValidationException $e) {
            $errorBag = $e->errors();
        }

        return Inertia::render('CheckIn/Index', array_merge($this->pageData(), [
            'result' => $result,
            'errors' => $errorBag,
        ]));
    }

    public function cancel(Request $request, Attendance $attendance, CheckInService $service)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $result = $service->cancel($attendance, $validated['reason'] ?? null);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function storeFromMember(Request $request, Member $member, CheckInService $service)
    {
        $allowGrace = (bool) $request->boolean('allow_grace');

        try {
            $result = $service->handle($member->member_code, 'manual', $allowGrace);
        } catch (ValidationException $e) {
            return redirect()
                ->route('members.show', $member)
                ->withErrors($e->errors());
        }

        // Expired → minta konfirmasi di halaman show (opsional nanti)
        if (!$result['success'] && ($result['needs_grace_confirmation'] ?? false)) {
            return redirect()
                ->route('members.show', $member)
                ->with('error', $result['message'])
                ->with('checkin_result', $result);
        }

        $flashKey = $result['success'] ? 'success' : 'error';
        $message = $result['message'] ?? ($result['success'] ? 'Check-in berhasil.' : 'Check-in ditolak.');

        return redirect()
            ->route('members.show', $member)
            ->with($flashKey, $message)
            ->with('checkin_result', $result);
    }

    private function pageData(): array
    {
        $todayAttendances = Attendance::with(['member', 'operator'])
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->limit(50)
            ->get();

        $todayCount = Attendance::whereDate('check_in_at', today())
            ->whereIn('status', ['verified', 'grace'])
            ->count();

        return [
            'todayAttendances' => $todayAttendances,
            'todayCount' => $todayCount,
        ];
    }
}
