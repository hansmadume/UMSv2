<x-mail::message>
# Support Ticket #{{ $ticket->ticket_number }} Updated

Hi {{ $userName ?? 'there' }},

Your support ticket has been updated.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Ticket #{{ $ticket->ticket_number }}**
**Subject:** {{ $ticket->subject }}
**Status:** {{ ucfirst($ticket->status) }}
**Priority:** {{ ucfirst($ticket->priority) }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

@if($updateMessage)
**Update:** {{ $updateMessage }}
@endif

If you're still having trouble, simply reply to this email
and our Support Team will assist you further.

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Support Ticket
</x-mail::button>

Thank you,<br>
Support Team<br>
{{ config('app.name') }}
</x-mail::message>
