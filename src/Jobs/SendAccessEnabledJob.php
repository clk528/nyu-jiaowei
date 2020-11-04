<?php

namespace clk528\NyuJiaoWei\Jobs;

use clk528\NyuJiaoWei\Email\SendAccessEnabledMail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendAccessEnabledJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $netId;

    public function __construct($netId)
    {
        $this->netId = $netId;
    }

    public function handle()
    {
        \Mail::to("{$this->netId}@nyu.edu")->cc('shanghai.publicsafety@nyu.edu')->send(new SendAccessEnabledMail($this->netId));
    }
}
