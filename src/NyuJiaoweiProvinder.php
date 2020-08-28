<?php

namespace clk528\NyuJiaoWei;

use clk528\NyuJiaoWei\Commands\DownLoadSurveryCommand;
use clk528\NyuJiaoWei\Commands\ImportSurveyExcelCommand;
use clk528\NyuJiaoWei\Commands\SyncAccessCommand;
use Illuminate\Support\ServiceProvider;

class NyuJiaoweiProvinder extends ServiceProvider
{

    protected $commands = [
        SyncAccessCommand::class,
        ImportSurveyExcelCommand::class,
        DownLoadSurveryCommand::class
    ];

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../migrations' => database_path('migrations')
            ], 'nyu-jiaowei-migrations');

            $source = __DIR__ . '/../config.php';

            $this->publishes([
                $source => config_path('jiaowei.php')
            ], 'nyu-jiaowei-migrations');

            $this->mergeConfigFrom($source, 'jiaowei');
        }
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}
