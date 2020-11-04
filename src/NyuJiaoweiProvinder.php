<?php

namespace clk528\NyuJiaoWei;

use clk528\NyuJiaoWei\Commands\EnabledAccessCommand;
use clk528\NyuJiaoWei\Commands\DownLoadSurveryCommand;
use clk528\NyuJiaoWei\Commands\ImportSurveyExcelCommand;
use clk528\NyuJiaoWei\Commands\DisableAccessCommand;
use Illuminate\Support\ServiceProvider;

class NyuJiaoweiProvinder extends ServiceProvider
{

    protected $commands = [
        DisableAccessCommand::class,
        ImportSurveyExcelCommand::class,
        DownLoadSurveryCommand::class,
        EnabledAccessCommand::class
    ];

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nyu-jiaowei-views');

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
