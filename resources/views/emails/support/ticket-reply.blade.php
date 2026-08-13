<x-mail::message>
# Support Ticket #{{ $ticket->ticket_id }} - New Reply

A new reply has been added to your support ticket.

**Ticket ID:** #{{ $ticket->ticket_id }}
**Subject:** {{ $ticket->subject }}
**Status:** {{ ucfirst($ticket->status) }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Replied By:** {{ $replierName ?? 'Support Staff' }}

@if($replyMessage)
## Reply

{{ $replyMessage }}
@endif

## Latest Description

{{ $ticket->comments }}

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

