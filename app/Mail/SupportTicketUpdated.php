<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public ?string $userName = null,
        public ?string $updateMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Ticket #'.$this->ticket->ticket_number.' Updated',
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.ticket-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
