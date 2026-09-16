<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::latest()->get();

        return Inertia::render('MembershipPlans/Index', [
            'plans' => $plans,
        ]);
    }

    public function create()
    {
        return Inertia::render('MembershipPlans/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:days,months',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        MembershipPlan::create($validated);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Paket membership berhasil ditambahkan.');
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        return Inertia::render('MembershipPlans/Edit', [
            'plan' => $membershipPlan,
        ]);
    }

    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:days,months',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $membershipPlan->update($validated);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        // Soft nonaktifkan saja (sesuai PRD BR-10)
        $membershipPlan->update(['is_active' => false]);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Paket membership dinonaktifkan.');
    }
}
