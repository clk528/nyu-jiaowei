<?php


namespace clk528\NyuJiaoWei\Commands;


use App\Services\AccessService;
use clk528\NyuJiaoWei\Traits\JiaoweiTrait;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    use JiaoweiTrait;

    protected $name = "nyu-jiaowei:test";

    protected $taotal = 0;

    protected $index = 1;

    public function __construct()
    {
        parent::__construct();


        $this->fileName = date('Y-m-d-H-i') . '-enable.log';

        $this->logPath = storage_path('logs/checkHealth');

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath);
        }

        $this->client = new Client([
            'base_uri' => 'https://review.shanghai.nyu.edu'
        ]);
    }

    function handle(){
       if($this->realNameIsSuccess("zq2053ddd")){
           $this->info("ok");
       }else{
           $this->warn("not ok");
       }
    }
}
