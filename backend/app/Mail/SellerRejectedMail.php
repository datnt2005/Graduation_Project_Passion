<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $reason;

    public function __construct($seller, $reason)
    {
        $this->seller = $seller;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Rất tiếc! Cửa hàng của bạn chưa được duyệt')
            ->view('emails.seller_rejected');
    }
}
