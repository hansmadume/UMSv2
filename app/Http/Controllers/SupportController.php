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
    /**
     * Display the support dashboard with stats and recent tickets.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Base query for tickets the user can access
        $baseQuery = SupportTicket::query()->with(['user', 'assignedTo']);

        if ($user->hasRole('Administrator')) {
            // Admins see all tickets
        } elseif ($user->hasAnyRole(['Support Staff', 'Manager'])) {
            // Support staff and managers see all tickets
        } elseif ($user->can('tickets.view_own')) {
            $baseQuery->where('user_id', $user->id);
        } else {
            $baseQuery->where('id', 0); // No tickets
        }

        // Stats
        $stats = [
            'open_tickets' => (clone $baseQuery)->where('status', 'open')->count(),
            'my_assigned_tickets' => (clone $baseQuery)->where('assigned_to', $user->id)->whereIn('status', ['open', 'in_progress'])->count(),
            'high_urgent_tickets' => (clone $baseQuery)->whereIn('priority', ['high', 'urgent'])->whereIn('status', ['open', 'in_progress'])->count(),
            'in_progress_tickets' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved_today' => (clone $baseQuery)->where('status', 'resolved')->whereDate('updated_at', today())->count(),
        ];

        // Recently updated tickets (limit 10)
        $recentlyUpdated = (clone $baseQuery)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'username' => $ticket->user?->getDisplayName() ?? 'Anonymous',
                    'email' => $ticket->user?->email ?? 'N/A',
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                ];
            })
            ->values()
            ->all();

        return inertia('Support/Dashboard', [
            'stats' => $stats,
            'recentlyUpdated' => $recentlyUpdated,
        ]);
    }

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
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt'],
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
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt'],
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
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt'],
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

    /**
     * Download a support ticket attachment securely.
     */
    public function downloadAttachment(Request $request, SupportTicket $ticket, string $attachment)
    {
        $user = $request->user();

        // Check if user has access to this ticket
        if (! $this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have permission to access this ticket.');
        }

        // Determine the attachment path based on the ticket
        $attachmentPath = null;
        $attachmentName = null;

        // Check main ticket attachment
        if ($ticket->attachment_path && basename($ticket->attachment_path) === $attachment) {
            $attachmentPath = $ticket->attachment_path;
            $attachmentName = $attachment;
        }

        // Check replies for attachment
        if (! $attachmentPath) {
            $message = $ticket->messages()->where('attachment_path', 'like', "%/$attachment")->first();
            if ($message) {
                $attachmentPath = $message->attachment_path;
                $attachmentName = $attachment;
            }
        }

        if (! $attachmentPath) {
            abort(404, 'Attachment not found.');
        }

        $fullPath = storage_path('app/public/' . $attachmentPath);

        if (! file_exists($fullPath)) {
            abort(404, 'Attachment file not found.');
        }

        // Get MIME type
        $mimeType = mime_content_type($fullPath);

        // Set headers for secure file serving
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $attachmentName . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->file($fullPath, $headers);
    }
}
