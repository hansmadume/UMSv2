<x-mail::message>
# Support Ticket Updated

Your support ticket has been updated.

**Ticket ID:** #{{ $ticket->ticket_id }}  
**Subject:** {{ $ticket->subject }}  
**Status:** {{ ucfirst($ticket->status) }}  
**Priority:** {{ ucfirst($ticket->priority) }}

@if($updateMessage)
## Update

{{ $updateMessage }}
@endif

## Description

{{ $ticket->comments }}

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
