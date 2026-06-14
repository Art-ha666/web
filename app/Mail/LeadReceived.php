<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New discovery-call lead: '.$this->lead->name,
            replyTo: [new Address($this->lead->business_email, $this->lead->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.lead-received',
            with: ['lead' => $this->lead],
        );
    }
}
