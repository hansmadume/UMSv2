<x-mail::message>
# Re: Support Ticket #{{ $ticket->ticket_number }} — {{ $ticket->subject }}

Hi {{ $userName ?? 'there' }},

Our Support Team has replied to your support ticket.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Ticket #{{ $ticket->ticket_number }}**
**Subject:** {{ $ticket->subject }}
**Status:** {{ ucfirst($ticket->status) }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

@if($replyMessage)
**Support Staff:**

{{ $replyMessage }}
@endif

If you're still having trouble, simply reply to this email
and our Support Team will assist you further.

Thank you,<br>
Support Team<br>
User Management System
</x-mail::message>
