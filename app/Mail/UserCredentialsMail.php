<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     * Kita terima data User dan Password mentah dari Controller
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     * Judul Email (Subject)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akses Login Aplikasi GeoAset Patrol',
        );
    }

    /**
     * Get the message content definition.
     * Mengarahkan ke file tampilan (View)
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.credentials',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
