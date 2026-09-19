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
        $todayAttendances = Attendance::with(['member', 'operator'])
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->limit(50)
            ->get();

        return Inertia::render('CheckIn/Index', [
            'todayAttendances' => $todayAttendances,
            'todayCount' => Attendance::whereDate('check_in_at', today())->count(),
        ]);
    }

    public function store(Request $request, CheckInService $service)
    {
        $validated = $request->validate([
            'member_code' => 'required|string|max:30',
            'method' => 'nullable|in:manual,barcode',
        ]);

        $result = null;
        $errors = null;

        try {
            $result = $service->handle(
                $validated['member_code'],
                $validated['method'] ?? 'manual'
            );
        } catch (ValidationException $e) {
            $errors = $e->errors();
        }

        $todayAttendances = Attendance::with(['member', 'operator'])
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->limit(50)
            ->get();

        return Inertia::render('CheckIn/Index', [
            'todayAttendances' => $todayAttendances,
            'todayCount' => Attendance::whereDate('check_in_at', today())->count(),
            'result' => $result,
            'errors' => $errors,
        ]);
    }

    public function storeFromMember(Member $member, CheckInService $service)
    {
        try {
            $result = $service->handle($member->member_code, 'manual');
        } catch (ValidationException $e) {
            return redirect()
                ->route('members.show', $member)
                ->withErrors($e->errors());
        }

        $flashKey = $result['success'] ? 'success' : 'error';
        $message = $result['message'] ?? ($result['success'] ? 'Check-in berhasil.' : 'Check-in ditolak.');

        return redirect()
            ->route('members.show', $member)
            ->with($flashKey, $message)
            ->with('checkin_result', $result);
    }
}
