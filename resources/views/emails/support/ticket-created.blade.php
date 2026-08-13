<x-mail::message>
# New Support Ticket #{{ $ticket->ticket_id }}

A new support ticket has been created and requires attention.

**Ticket ID:** #{{ $ticket->ticket_id }}
**Subject:** {{ $ticket->subject }}
**Category:** {{ $ticket->category }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Status:** {{ ucfirst($ticket->status) }}
**Created By:** {{ $userName ?? $ticket->username ?? 'Anonymous' }}
**Email:** {{ $ticket->email }}

## Description

{{ $ticket->comments }}

<x-mail::button :url="url('/support/tickets/' . $ticket->id)">
    View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
