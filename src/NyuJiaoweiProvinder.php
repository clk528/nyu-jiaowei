<?php

namespace clk528\NyuJiaoWei;

class NyuJiaoweiProvinder extends ServiceProvider{
    public function boot(){
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../migrations' => database_path('migrations')
            ],'nyu-jiaowei-migrations');
        }
    }
}