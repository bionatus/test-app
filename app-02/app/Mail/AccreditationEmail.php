<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use \Illuminate\Support\Facades\URL;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Contracts\Mail\Mailer as MailerContract;

class AccreditationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The data instance.
     *
     * @array Data
     */
    public $data;

    /**
     * The filename instance.
     *
     * @string Filename
     */
    public $filename;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->filename = public_path("storage/" . md5($this->data['name']) . '.pdf');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $scriptPath = base_path('etc/generate-pdf.js');

        $url = Url::Route('pdf', $this->data);

        $process = Process::fromShellCommandline('node ' . $scriptPath  . ' --filename ' . $this->filename . ' --url "' . $url . '"');

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        } else {
            return $this->view('emails.accreditation.content')
                ->attach($this->filename, [
                    'as' => 'accreditation.pdf',
                    'mime' => 'application/pdf',
                ]);
        }
    }

    /**
     * Send email and delete attachment
     *
     * @param MailerContract $mailer
     */
    public function send($mailer)
    {
        parent::send($mailer);

        unlink($this->filename);
    }
}
