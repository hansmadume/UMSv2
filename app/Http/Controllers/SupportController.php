<?php

namespace App\Http\Controllers;

use App\Mail\SupportTicketClosed;
use App\Mail\SupportTicketCreated;
use App\Mail\SupportTicketNewReply;
use App\Mail\SupportTicketResolved;
use App\Mail\SupportTicketUpdated;
use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function myTickets(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->with(['user', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Support/Index', [
            'tickets' => $tickets,
            'filters' => ['my_tickets' => true],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = SupportTicket::query()->with(['user', 'assignedTo']);

        if ($user->hasRole('Administrator')) {
            // Admin sees all tickets
        } elseif ($user->hasAnyRole(['Support Staff'])) {
            if ($request->boolean('my_tickets')) {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($user->can('tickets.view')) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('id', 0); // No tickets
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return inertia('Support/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'priority', 'my_tickets']),
        ]);
    }

    public function create()
    {
        return inertia('Support/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'comments' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = $request->user();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support-tickets', 'public');
        }

        $ticket = SupportTicket::create([
            'ticket_id' => 'TKT-' . str_pad(SupportTicket::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
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
            'action' => 'Support Ticket Created: ' . $ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            $supportEmails = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff']);
            })->pluck('email')->filter()->toArray();

            foreach ($supportEmails as $email) {
                Mail::to($email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));
            }
        } catch (\Throwable $e) {
            // Log but don't fail the request
        }

        return redirect()->route('support.index')->with('success', 'Support ticket created successfully.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        if (! $this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have permission to access this ticket.');
        }

        $ticket->load('user', 'assignedTo');

        $assignableUsers = [];
        if ($user->hasRole('Administrator') || $user->hasRole('Support Staff')) {
            $assignableUsers = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff']);
            })->get(['id', 'name', 'username']);
        }

        return inertia('Support/Show', [
            'ticket' => $ticket,
            'users' => $assignableUsers,
        ]);
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        if (! $this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have permission to update this ticket.');
        }

        $validated = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
            'comments' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('support-tickets', 'public');
        }

        $oldStatus = $ticket->status;
        $updateMessage = match (true) {
            isset($validated['comments']) => 'New reply added',
            isset($validated['status']) => 'Status changed to ' . $validated['status'],
            isset($validated['priority']) => 'Priority changed to ' . $validated['priority'],
            isset($validated['assigned_to']) => 'Ticket assignment updated',
            isset($validated['internal_notes']) => 'Internal notes updated',
            default => 'Ticket updated',
        };

        $ticket->update($validated);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'Support Ticket Updated: ' . $ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            if (isset($validated['comments'])) {
                Mail::to($ticket->email)->send(new SupportTicketNewReply($ticket, $request->user()?->getDisplayName(), $validated['comments']));
            } elseif (isset($validated['status']) && $validated['status'] === 'resolved') {
                Mail::to($ticket->email)->send(new SupportTicketResolved($ticket, $request->user()?->getDisplayName()));
            } elseif (isset($validated['status']) && $validated['status'] === 'closed') {
                Mail::to($ticket->email)->send(new SupportTicketClosed($ticket, $request->user()?->getDisplayName()));
            } else {
                Mail::to($ticket->email)->send(new SupportTicketUpdated($ticket, $request->user()?->getDisplayName(), $updateMessage));
            }
        } catch (\Throwable $e) {
            // Log but don't fail the request
        }

        return back()->with('success', 'Ticket updated successfully.');
    }

    private function canAccessTicket($user, SupportTicket $ticket): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($user->hasAnyRole(['Support Staff'])) {
            return true;
        }

        if ($user->hasAnyPermission(['tickets.view', 'tickets.update', 'tickets.reply'])) {
            return true;
        }

        if ($ticket->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function dashboard()
    {
        $user = request()->user();

        $openTickets = SupportTicket::where('status', 'open')->count();
        $myAssignedTickets = SupportTicket::where('assigned_to', $user->id)->count();
        $highUrgentTickets = SupportTicket::whereIn('priority', ['high', 'urgent'])
            ->where('status', '!=', 'closed')
            ->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $resolvedToday = SupportTicket::where('status', 'resolved')
            ->whereDate('updated_at', now()->toDateString())
            ->count();
        $recentlyUpdated = SupportTicket::orderBy('updated_at', 'desc')->limit(10)->get();

        return inertia('Support/Dashboard', [
            'stats' => [
                'open_tickets' => $openTickets,
                'my_assigned_tickets' => $myAssignedTickets,
                'high_urgent_tickets' => $highUrgentTickets,
                'in_progress_tickets' => $inProgressTickets,
                'resolved_today' => $resolvedToday,
            ],
            'recentlyUpdated' => $recentlyUpdated,
        ]);
    }

    public function createContact()
    {
        return Inertia::render('ContactSupport', [
            'user' => request()->user(),
        ]);
    }

    public function storeContact(Request $request)
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
            'ticket_id' => 'TKT-' . str_pad(SupportTicket::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'subject' => 'Support Request',
            'category' => 'General',
            'priority' => 'medium',
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
            'action' => 'Support Ticket Created: ' . $ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            $supportEmails = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff']);
            })->pluck('email')->filter()->toArray();

            foreach ($supportEmails as $email) {
                Mail::to($email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));
            }
        } catch (\Throwable $e) {
            // Log but don't fail the request
        }

        return back()->with('success', 'Support ticket submitted successfully.');
    }
}
