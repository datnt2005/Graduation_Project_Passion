<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $oldStatus;

    /**
     * Tạo instance mới cho mail.
     */
    public function __construct(Order $order, $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Tiêu đề mail.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cập nhật trạng thái đơn hàng',
        );
    }

    /**
     * Nội dung mail (dùng markdown giống OrderSuccessMail).
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.status_updated',
            with: [
                'order' => $this->order,
                'oldStatus' => $this->oldStatus
            ]
        );
    }

    /**
     * Đính kèm file (nếu có).
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Cập nhật trạng thái đơn hàng #' . $this->order->id)
            ->view('emails.orders.status_updated');
    }
}
