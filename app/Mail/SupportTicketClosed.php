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

class SupportTicketClosed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public ?string $userName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Ticket #' . $this->ticket->ticket_id . ' - Closed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.ticket-closed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
