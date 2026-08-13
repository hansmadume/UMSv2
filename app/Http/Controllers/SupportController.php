<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function create()
    {
        return Inertia::render('ContactSupport', [
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comments' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = $request->user();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support-tickets', 'public');
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'username' => $user->username ?? $user->full_name ?? $user->email,
            'email' => $user->email,
            'comments' => $validated['comments'],
            'attachment_path' => $attachmentPath,
            'status' => 'open',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->getDisplayName(),
            'action' => 'Support Ticket Created',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Support ticket submitted successfully.');
    }
}
