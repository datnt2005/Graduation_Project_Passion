<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $product;
    public $reason;

    public function __construct($product, $seller, $reason)
    {
        $this->product = $product;
        $this->seller = $seller;
        $this->product->seller = $seller;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Rất tiếc! Sản phẩm của bạn chưa được duyệt')
            ->view('emails.product_rejected');
    }
}
