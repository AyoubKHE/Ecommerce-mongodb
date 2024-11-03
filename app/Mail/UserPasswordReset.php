<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserPasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public string $firstName;

    public string $passwordResetToken;

    /**
     * Create a new message instance.
     */
    public function __construct(string $firstName, string $passwordResetToken)
    {
        $this->firstName = $firstName;
        $this->passwordResetToken = $passwordResetToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Réinitialisation de votre mot de passe",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-password-reset',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
