<?php

namespace App\Http\Controllers;

use App\Mail\SupportTicketClosed;
use App\Mail\SupportTicketCreated;
use App\Mail\SupportTicketNewReply;
use App\Mail\SupportTicketResolved;
use App\Mail\SupportTicketUpdated;
use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function myTickets(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->with(['user', 'assignedTo'])
            ->where('user_id', $user->id)
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
            if ($request->boolean('my_tickets')) {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($user->hasAnyRole(['Support Staff', 'Manager'])) {
            if ($request->boolean('my_tickets')) {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($user->can('tickets.view_own')) {
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
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $isMyTickets = $user->can('tickets.view_own') && ! $user->hasAnyRole(['Administrator', 'Support Staff', 'Manager']);

        return inertia('Support/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'priority', 'my_tickets']),
            'isMyTickets' => $isMyTickets,
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
            'ticket_number' => 'TKT-'.str_pad(SupportTicket::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'user_id' => $user->id,
            'comments' => $validated['comments'],
            'attachment_path' => $attachmentPath,
            'status' => 'open',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->getDisplayName(),
            'action' => 'Support Ticket Created: '.$ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            Mail::to($ticket->user->email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));

            $supportEmails = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff']);
            })->pluck('email')->filter()->toArray();

            foreach ($supportEmails as $email) {
                Mail::to($email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));
            }
        } catch (\Throwable $e) {
            Log::error('Support ticket created email failed', [
                'ticket_id' => $ticket->ticket_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return redirect()->route('support.index')->with('success', 'Support ticket created successfully.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        if (! $this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have permission to access this ticket.');
        }

        $ticket->load('user', 'assignedTo', 'messages.user');

        $assignableUsers = [];
        if ($user->hasRole('Administrator') || $user->hasRole('Support Staff') || $user->hasRole('Manager')) {
            $assignableUsers = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff', 'Manager']);
            })->get(['id', 'name', 'username']);
        }

        return inertia('Support/Show', [
            'ticket' => $ticket,
            'users' => $assignableUsers,
            'isMyTicket' => $ticket->user_id === $user->id,
        ]);
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        if (! $this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have permission to access this ticket.');
        }

        $validated = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        $replyAttachmentPath = null;
        if ($request->hasFile('attachment')) {
            $replyAttachmentPath = $request->file('attachment')->store('support-tickets', 'public');
        }

        if (isset($validated['status']) && ! $user->can('tickets.change_status')) {
            abort(403, 'You do not have permission to change ticket status.');
        }

        if (isset($validated['priority']) && ! $user->can('tickets.change_priority')) {
            abort(403, 'You do not have permission to change ticket priority.');
        }

        if (isset($validated['assigned_to']) && ! $user->can('tickets.assign')) {
            abort(403, 'You do not have permission to assign tickets.');
        }

        if (isset($validated['comments'])) {
            $canReply = $user->can('tickets.reply') || ($ticket->user_id === $user->id && $user->can('tickets.reply_own'));
            if (! $canReply) {
                abort(403, 'You do not have permission to reply to this ticket.');
            }

            $replyAttachmentPath = null;
            if ($request->hasFile('attachment')) {
                $replyAttachmentPath = $request->file('attachment')->store('support-tickets', 'public');
            }

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $validated['comments'],
                'attachment_path' => $replyAttachmentPath,
            ]);
        }

        $updateMessage = match (true) {
            isset($validated['comments']) => 'New reply added',
            isset($validated['status']) => 'Status changed to '.$validated['status'],
            isset($validated['priority']) => 'Priority changed to '.$validated['priority'],
            isset($validated['assigned_to']) => 'Ticket assignment updated',
            default => 'Ticket updated',
        };

        $shouldNotifyCustomer = match (true) {
            isset($validated['comments']) => true,
            isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed']) => true,
            default => false,
        };

        $ticket->update($validated);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'Support Ticket Updated: '.$ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            if (! $shouldNotifyCustomer) {
                return back()->with('success', 'Ticket updated successfully.');
            }

            if (isset($validated['comments'])) {
                Mail::to($ticket->user->email)->send(new SupportTicketNewReply($ticket, $request->user()?->getDisplayName(), $validated['comments']));
            } elseif (isset($validated['status']) && $validated['status'] === 'resolved') {
                Mail::to($ticket->user->email)->send(new SupportTicketResolved($ticket, $request->user()?->getDisplayName()));
            } elseif (isset($validated['status']) && $validated['status'] === 'closed') {
                Mail::to($ticket->user->email)->send(new SupportTicketClosed($ticket, $request->user()?->getDisplayName()));
            } else {
                Mail::to($ticket->user->email)->send(new SupportTicketUpdated($ticket, $request->user()?->getDisplayName(), $updateMessage));
            }
        } catch (\Throwable $e) {
            Log::error('Support ticket update email failed', [
                'ticket_id' => $ticket->ticket_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return back()->with('success', 'Ticket updated successfully.');
    }

    private function canAccessTicket($user, SupportTicket $ticket): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($user->hasAnyRole(['Support Staff', 'Manager'])) {
            return true;
        }

        if ($user->hasAnyPermission(['tickets.view_own', 'tickets.reply_own', 'tickets.close_own'])) {
            return $ticket->user_id === $user->id;
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
        $recentlyUpdated = SupportTicket::with('user')->orderBy('updated_at', 'desc')->limit(10)->get();

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
            'ticket_number' => 'TKT-'.str_pad(SupportTicket::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'subject' => 'Support Request',
            'priority' => 'medium',
            'user_id' => $user->id,
            'comments' => $validated['comments'],
            'attachment_path' => $attachmentPath,
            'status' => 'open',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->getDisplayName(),
            'action' => 'Support Ticket Created: '.$ticket->ticket_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        try {
            Mail::to($ticket->user->email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));

            $supportEmails = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Administrator', 'Support Staff']);
            })->pluck('email')->filter()->toArray();

            foreach ($supportEmails as $email) {
                Mail::to($email)->send(new SupportTicketCreated($ticket, $user->getDisplayName()));
            }
        } catch (\Throwable $e) {
            Log::error('Support contact email failed', [
                'ticket_id' => $ticket->ticket_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return back()->with('success', 'Support ticket submitted successfully.');
    }
}
