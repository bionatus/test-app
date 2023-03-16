<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsersExportEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users.export')->attach(storage_path('app/users.csv'), [
                'as'   => 'users.csv',
                'mime' => 'text/csv',
            ]);
    }

    /**
     * Send email and delete attachment
     *
     * @param MailerContract $mailer
     */
    public function send($mailer)
    {
        parent::send($mailer);

        unlink(storage_path('app/users.csv'));
    }
}
