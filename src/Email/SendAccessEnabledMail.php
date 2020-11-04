<?php

namespace clk528\NyuJiaoWei\Email;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAccessEnabledMail extends Mailable
{
    use Queueable, SerializesModels;

    private $netId;

    public function __construct($netId)
    {
        $this->netId = $netId;
    }

    public function build()
    {
        return $this->view("nyu-jiaowei-views::email.jiaowei-clk")->subject("Access privileges restored");
    }
}
