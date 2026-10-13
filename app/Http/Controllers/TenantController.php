<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        // Eager-load latest-lease chain: the view reads
        // $tenant->leases->...->apartment per row (was N+1).
        $tenants = Tenant::with(['user:id,name,email', 'leases.apartment:id,unit_number,unit_type'])
            ->latest()
            ->paginate(10);

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        // Only accounts not yet linked to any tenant (new/pending first).
        $users = $this->unlinkedAccountQuery()
            ->orderBy('name')
            ->get();

        return view('tenants.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:150',
            'emergency_phone' => 'nullable|string|max:30',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if (! empty($validated['user_id'])) {
            // Create/edit forms may attach an approved-but-unlinked account;
            // the double-link guard still applies.
            $this->assertLinkable((int) $validated['user_id'], null, false);
        }

        $tenant = Tenant::create($validated);
        $this->syncApproval(null, $tenant->user_id);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant added successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('user', 'leases.apartment');
        $pendingUsers = $this->pendingUsers();

        return view('tenants.show', compact('tenant', 'pendingUsers'));
    }

    /**
     * Pending Accounts: registered users waiting for an admin to link
     * them to an existing Tenant record.
     */
    public function pending()
    {
        $pendingUsers = $this->pendingUsers();
        // Unlinked tenants available for linking (narrow columns).
        $unlinkedTenants = Tenant::select('id', 'first_name', 'last_name')
            ->whereNull('user_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('tenants.pending', compact('pendingUsers', 'unlinkedTenants'));
    }

    /**
     * Link endpoint for the Pending Accounts table (tenant chosen in the form).
     */
    public function link(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        return $this->linkUser($request, Tenant::findOrFail($validated['tenant_id']));
    }

    /**
     * Link a pending user account to an existing Tenant record.
     * Sets users.status = approved. Never creates a Tenant record.
     */
    public function linkUser(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        if ($tenant->user_id !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => 'This tenant is already linked to a user account. Unlink it first.',
            ]);
        }

        $this->assertLinkable((int) $validated['user_id']);
        $user = User::findOrFail($validated['user_id']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $user) {
            $tenant->update(['user_id' => $user->id]);
            $user->update(['status' => 'approved']);
        });
        \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$user->id);

        \App\Support\TenantNotifications::recordForUser(
            $user,
            'account-approved-'.$tenant->id,
            'account',
            'Account approved',
            'Your account was linked to '.$tenant->full_name.'. You can now access your Tenant Dashboard.',
            route('dashboard')
        );

        return back()->with('success', $user->name.' is now linked to '.$tenant->full_name.' and approved.');
    }

    /**
     * Unlink a user account from a Tenant record (returns user to pending).
     */
    public function unlinkUser(Tenant $tenant)
    {
        if ($tenant->user_id === null) {
            return back()->withErrors(['user_id' => 'This tenant is not linked to any user account.']);
        }

        $user = User::find($tenant->user_id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $user) {
            $tenant->update(['user_id' => null]);
            if ($user && $user->role === 'tenant') {
                $user->update(['status' => 'pending']);
            }
        });
        if ($user) {
            \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$user->id);
        }

        return back()->with('success', 'Account unlinked. The user is pending approval again.');
    }

    private function pendingUsers()
    {
        return User::select('id', 'name', 'email', 'created_at')
            ->where('role', 'tenant')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Tenant-role accounts not linked to any tenant record.
     */
    private function unlinkedAccountQuery()
    {
        return User::select('id', 'name', 'email')
            ->where('role', 'tenant')
            ->whereNotIn('id', fn ($q) => $q->select('user_id')->from('tenants')->whereNotNull('user_id'));
    }

    /**
     * Guardrails: one user ↔ one tenant, always. When $pendingOnly is true
     * (approval endpoints), the account must also be a pending tenant.
     * Failures return to the form with a message instead of a 422 page.
     */
    private function assertLinkable(int $userId, ?int $exceptTenantId = null, bool $pendingOnly = true): void
    {
        $user = User::findOrFail($userId);
        if ($user->role !== 'tenant') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => 'Only tenant accounts can be linked to a tenant record.',
            ]);
        }
        if ($pendingOnly && $user->status !== 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => $user->name.' is already '.$user->status.' and does not need approval linking. Pick a pending account, or leave the field empty.',
            ]);
        }
        $takenBy = Tenant::where('user_id', $userId)
            ->when($exceptTenantId, fn ($q) => $q->where('id', '!=', $exceptTenantId))
            ->value('id');
        if ($takenBy) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => $user->name.' is already linked to another tenant record and cannot be linked twice.',
            ]);
        }
    }

    /**
     * Keep users.status in sync with the tenant link.
     */
    private function syncApproval(?int $oldUserId, ?int $newUserId): void
    {
        if ($oldUserId && $oldUserId !== $newUserId) {
            $old = User::find($oldUserId);
            if ($old && $old->role === 'tenant') {
                $old->update(['status' => 'pending']);
                \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$old->id);
            }
        }
        if ($newUserId && $newUserId !== $oldUserId) {
            $new = User::find($newUserId);
            if ($new && $new->role === 'tenant' && $new->status === 'pending') {
                $new->update(['status' => 'approved']);
                \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$new->id);
            }
        }
    }

    public function edit(Tenant $tenant)
    {
        // Unlinked accounts plus the one already linked here (keeps selection).
        $users = $this->unlinkedAccountQuery()
            ->orWhere('id', $tenant->user_id)
            ->orderBy('name')
            ->get();

        return view('tenants.edit', compact('tenant', 'users'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:150',
            'emergency_phone' => 'nullable|string|max:30',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $oldUserId = $tenant->user_id;
        $newUserId = $validated['user_id'] ?? null;

        if ($newUserId && (int) $newUserId !== (int) $oldUserId) {
            $this->assertLinkable((int) $newUserId, $tenant->id, false);
        }

        $tenant->update($validated);
        $this->syncApproval($oldUserId ? (int) $oldUserId : null, $newUserId ? (int) $newUserId : null);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
