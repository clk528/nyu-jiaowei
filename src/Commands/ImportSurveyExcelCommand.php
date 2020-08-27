<?php


namespace clk528\NyuJiaoWei\Commands;


use clk528\NyuJiaoWei\Imports\SurveyImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;

class ImportSurveyExcelCommand extends Command
{
    protected $signature = 'jiaowei:import-survery-excel {--file=}';

    protected $description = '导入安全培训人员名单';

    private $file;

    function handle()
    {
        $this->file = $this->option('file');

        if ((empty($this->file))) {
            $this->error("请输入文件路径 --file=file_path");
            return;
        }

        if (!file_exists($this->file)) {
            $this->error("文件不存在");
            return;
        }

        if (is_dir($this->file)) {
            $this->error("file 不是文件");
            return;
        }

        Excel::import(new SurveyImport(), $this->file);
    }
}
