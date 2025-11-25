<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invitation $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build(): self
    {
        $url = url('/invitation/accept/'.$this->invitation->token);
        return $this->subject('Has sido invitado como administrador')
            ->view('emails.admin_invitation')
            ->with([
                'name' => $this->invitation->name,
                'role' => $this->invitation->role,
                'url' => $url,
                'expires' => $this->invitation->expires_at,
            ]);
    }
}
