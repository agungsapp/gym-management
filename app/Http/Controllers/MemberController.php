<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query()->latest();

        // Search sederhana
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        // Filter tipe
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $members = $query->paginate(15)->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Members/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:pelajar,non_pelajar',
            'name' => 'required|string|max:150',
            'whatsapp' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Generate member code
        $validated['member_code'] = Member::generateMemberCode($validated['type']);
        $validated['created_by'] = Auth::id();

        // Upload foto
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        Member::create($validated);

        return redirect()->route('members.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        $member->load([
            'memberships' => fn($q) => $q->with('plan', 'payment')->latest(),
        ]);

        $activeMembership = $member->memberships
            ->first(fn($m) => $m->status === 'active' && $m->end_date->gte(now()->startOfDay()));

        $attendances = $member->attendances()
            ->with('operator')
            ->latest('check_in_at')
            ->limit(30)
            ->get();

        return Inertia::render('Members/Show', [
            'member' => $member,
            'activeMembership' => $activeMembership,
            'attendances' => $attendances,
        ]);
    }

    public function edit(Member $member)
    {
        return Inertia::render('Members/Edit', [
            'member' => $member,
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'type' => 'required|in:pelajar,non_pelajar',
            'name' => 'required|string|max:150',
            'whatsapp' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Upload foto baru (hapus yang lama)
        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        // Catatan: member_code tidak diubah (sesuai PRD)
        $member->update($validated);

        return redirect()->route('members.index')
            ->with('success', 'Data member berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        // Soft nonaktifkan saja
        $member->update(['is_active' => false]);

        return redirect()->route('members.index')
            ->with('success', 'Member berhasil dinonaktifkan.');
    }
}
