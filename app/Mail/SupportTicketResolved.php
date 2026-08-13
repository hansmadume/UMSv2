<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketResolved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public ?string $userName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Ticket #' . $this->ticket->ticket_id . ' - Resolved',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.ticket-resolved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
