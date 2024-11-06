<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminSendMail extends Mailable
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
        return $this->subject($this->details['subject'])                        //主旨
                    ->view('admin.mails.templates.adminsendmailbody');           //使用blade樣板
                    // ->from($this->details['from'], $this->details['name'])   //From
                    // ->text('emails.orders.shipped_plain')                    //純文字
                    // ->with([                                                 //附帶變數
                    //     'Name' => $this->details->name,
                    //     'Text' => $this->details->text,
                    // ])
                    // ->attach('/path/to/file')                                //附帶檔案
                    // ->attach('/path/to/file', [                              //附帶指定格式檔案
                    //     'as' => 'name.pdf',
                    //     'mime' => 'application/pdf',
                    // ])
                    // ->attachFromStorage('/path/to/file')                     //從Storage附帶檔案
                    // ->attachFromStorage('/path/to/file', 'name.pdf', [
                    //     'mime' => 'application/pdf'
                    // ])
                    // ->attachFromStorageDisk('s3', '/path/to/file')           //從AWS S3, Storage附帶檔案
                    // ->attachData($this->pdf, 'name.pdf', [
                    //     'mime' => 'application/pdf',
                    // ])
    }
}
