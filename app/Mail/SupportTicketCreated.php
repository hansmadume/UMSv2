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

class SupportTicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public ?string $userName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Support Ticket #' . $this->ticket->ticket_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.ticket-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
