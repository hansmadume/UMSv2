<x-mail::message>
# Support Ticket #{{ $ticket->ticket_number }} Received

Hi {{ $userName ?? 'there' }},

We've received your support request.

Ticket #{{ $ticket->ticket_number }}
Subject: {{ $ticket->subject }}
Priority: {{ ucfirst($ticket->priority) }}
Status: {{ ucfirst($ticket->status) }}

Our Support Team will review your request and get back to you.

You can reply directly to this email if you have additional
information about your issue.

Thank you,<br>
Support Team<br>
User Management System
</x-mail::message>
