<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subAccount;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subAccount, $password)
    {
        $this->subAccount = $subAccount;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $accountType = "sub account";
        if($this->subAccount->sub_account_type == "department") {
            $accountType = "department";
        }
        return $this->subject("Your New {$accountType} Has Been Created")
                    ->view('emails.new-sub-account')
                    ->with([
                        'name' => $this->subAccount->name,
                        'email' => $this->subAccount->email,
                        'role' => $this->subAccount->role,
                        'password' => $this->password,
                    ]);
    }
}
