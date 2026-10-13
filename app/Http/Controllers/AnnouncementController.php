<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Tenant;
use App\Support\TenantNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest('published_at')->paginate(15);
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['title' => ['required', 'string', 'max:255'], 'message' => ['required', 'string', 'max:5000'], 'published_at' => ['required', 'date'], 'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'], 'is_active' => ['nullable', 'boolean']]);
        $announcement = Announcement::create([...$validated, 'is_active' => $request->boolean('is_active', true), 'created_by' => Auth::id()]);
        if ($announcement->is_active && $announcement->published_at->isPast()) {
            // Chunked bulk fan-out: was 1 SELECT + up to 1 INSERT per tenant.
            Tenant::whereNotNull('user_id')->select('id', 'user_id')->chunk(200, fn ($tenants) => TenantNotifications::recordForTenants($tenants, 'announcement-'.$announcement->id, 'announcement', $announcement->title, $announcement->message, route('dashboard')));
        }
        return redirect()->route('announcements.index')->with('success', 'Announcement published.');
    }
}
