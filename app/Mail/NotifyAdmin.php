<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Models\User;

class NotifyAdmin extends Mailable
{
    public function __construct(public User $user) {}

    public function build(): self
    {
        return $this->subject('New user registered')
                    ->view('emails.notify-admin')
                    ->with(['user' => $this->user]);
    }
}
