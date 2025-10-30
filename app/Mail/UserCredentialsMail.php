<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $plainPassword;
    /** @var array<int,string> */
    public array $roles;
    public string $event; // 'created' | 'updated'
    public bool $passwordChanged;

    /**
     * @param array<int,string> $roles
     */
    public function __construct(User $user, ?string $plainPassword, array $roles, string $event = 'created', bool $passwordChanged = true)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->roles = $roles;
        $this->event = $event;
        $this->passwordChanged = $passwordChanged;
    }

    public function build()
    {
        $subject = $this->event === 'created'
            ? 'Your account has been created'
            : 'Your account has been updated';

        return $this->subject($subject)
            ->view('emails.user_credentials')
            ->with([
                'user'            => $this->user,
                'plainPassword'   => $this->plainPassword,
                'roles'           => $this->roles,
                'event'           => $this->event,
                'passwordChanged' => $this->passwordChanged,
                // Optional: add a login URL if you have one:
                'loginUrl'        => url('/login'),
            ]);
    }
}
