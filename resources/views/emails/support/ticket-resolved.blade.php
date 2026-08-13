<x-mail::message>
# Support Ticket #{{ $ticket->ticket_id }} - Resolved

Your support ticket has been marked as resolved.

**Ticket ID:** #{{ $ticket->ticket_id }}
**Subject:** {{ $ticket->subject }}
**Status:** Resolved
**Priority:** {{ ucfirst($ticket->priority) }}

## Description

{{ $ticket->comments }}

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
