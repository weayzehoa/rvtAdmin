<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminSendEmailForQueuing extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->details['subject']); //主旨
        !empty($this->details['from']) ? $mail = $mail->from($this->details['from'], $this->details['name']) : '';
        !empty($this->details['replyTo']) ? $mail = $mail->replyTo($this->details['replyTo'], $this->details['replyName']) : '';
        if($this->details['model'] == 'mitakeAccountPointNotice'){
            $mail = $mail->view('admin.mails.templates.mitakeAccountPointNoticeMailBody');
        }else{
            $mail = $mail->view('admin.mails.templates.adminsendmailqueuesbody');
        }
        return $mail;
    }
}
