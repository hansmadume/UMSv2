<x-mail::message>
# Support Ticket #{{ $ticket->ticket_number }} Closed

Hi {{ $userName ?? 'there' }},

Your support ticket has been closed.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Ticket #{{ $ticket->ticket_number }}**
**Subject:** {{ $ticket->subject }}
**Status:** Closed
**Priority:** {{ ucfirst($ticket->priority) }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

If you're still having trouble, simply reply to this email
and our Support Team will assist you further.

Thank you,<br>
Support Team<br>
User Management System
</x-mail::message>
