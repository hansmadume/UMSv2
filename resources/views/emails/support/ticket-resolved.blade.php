<x-mail::message>
# Support Ticket #{{ $ticket->ticket_number }} Resolved

Hi {{ $userName ?? 'there' }},

Your support ticket has been marked as resolved.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Ticket #{{ $ticket->ticket_number }}**
**Subject:** {{ $ticket->subject }}
**Status:** Resolved
**Priority:** {{ ucfirst($ticket->priority) }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

If you're still having trouble, simply reply to this email
and our Support Team will assist you further.

Thank you,<br>
Support Team<br>
User Management System
</x-mail::message>
