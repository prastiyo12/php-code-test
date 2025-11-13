<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Models\User;

class WelcomeUser extends Mailable
{
    public function __construct(public User $user) {}

    public function build(): self
    {
        return $this->subject('Welcome')
                    ->view('emails.welcome')
                    ->with(['user' => $this->user]);
    }
}
