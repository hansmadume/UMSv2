<x-mail::message>
# Support Ticket #{{ $ticket->ticket_id }} - Closed

Your support ticket has been closed.

**Ticket ID:** #{{ $ticket->ticket_id }}
**Subject:** {{ $ticket->subject }}
**Status:** Closed
**Priority:** {{ ucfirst($ticket->priority) }}

## Description

{{ $ticket->comments }}

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
