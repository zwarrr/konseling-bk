<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SetupCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $role,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('bksmknciamis@gmail.com', 'BK SMKN 1 Ciamis'),
            subject: 'Notifikasi Setup Akun Berhasil',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.setup-completed',
            with: [
                'name' => $this->name,
                'roleLabel' => $this->roleLabel(),
            ]
        );
    }

    protected function roleLabel(): string
    {
        return match (strtolower($this->role)) {
            'bk', 'guru' => 'Guru BK',
            'siswa', 'user' => 'Siswa',
            default => 'Pengguna',
        };
    }
}
