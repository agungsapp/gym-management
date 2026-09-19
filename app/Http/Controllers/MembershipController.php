<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Services\CreateMembershipService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipController extends Controller
{
    public function create(Member $member)
    {
        $plans = MembershipPlan::where('is_active', true)
            ->orderBy('price')
            ->get(['id', 'name', 'duration', 'duration_unit', 'price']);

        // untuk referral select
        $members = Member::where('is_active', true)
            ->where('id', '!=', $member->id)
            ->orderBy('name')
            ->get(['id', 'member_code', 'name']);

        $activeMembership = $member->memberships()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->with('plan')
            ->orderByDesc('end_date')
            ->first();

        return Inertia::render('Members/BuyMembership', [
            'member' => $member,
            'plans' => $plans,
            'referralMembers' => $members,
            'activeMembership' => $activeMembership,
        ]);
    }

    public function store(Request $request, Member $member, CreateMembershipService $service)
    {
        $validated = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'required|date',
            'discount_type' => 'nullable|in:percent,amount',
            'discount_value' => 'nullable|integer|min:0',
            'extra_days' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,transfer,qris',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'referral_member_id' => 'nullable|exists:members,id',
        ]);

        $validated['member_id'] = $member->id;

        $service->handle($validated);

        return redirect()
            ->route('members.show', $member->id)
            ->with('success', 'Membership berhasil dibeli.');
    }
}
