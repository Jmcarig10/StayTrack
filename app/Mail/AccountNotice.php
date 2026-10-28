<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $title,
        public array $lines,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[StayTrack] '.$this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notice', with: [
            'title' => $this->title,
            'lines' => $this->lines,
            'actionUrl' => $this->actionUrl,
            'actionLabel' => $this->actionLabel,
        ]);
    }
}
