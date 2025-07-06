<?php

namespace App\Mail;

// App\Mail\NotificationMail.php

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class NotificationMail extends Mailable
{
    use SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function build()
    {
        return $this->subject($this->notification->title)
            ->view('emails.notification')
            ->with(['notification' => $this->notification]);
    }
}
