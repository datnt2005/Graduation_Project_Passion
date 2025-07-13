<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductApprovedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $seller;
    public $product;
    public function __construct($product, $seller )
    {
        $this->product = $product;
        $this->seller = $seller;
        $this->product->seller = $seller;
    }

    public function build()
    {
        return $this->subject('Chúc mừng! Sản phẩm của bạn đã được duyệt')
            ->view('emails.product_approved')
            ->with([
            'product' => $this->product,
            'seller' => $this->seller,
        ]);
    }
}
