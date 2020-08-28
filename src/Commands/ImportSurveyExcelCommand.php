<?php


namespace clk528\NyuJiaoWei\Commands;


use clk528\NyuJiaoWei\Imports\SurveyImport;
use clk528\NyuJiaoWei\Models\NyuSurvey;
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

        $this->getSurveys();
    }

    private function getSurveys()
    {
        $data = file_get_contents($this->file);
        $data = json_decode($data, true);

        foreach ($data['responses'] as $item) {
            $email = $this->getEmail($item);

            if (empty($email)) {
                continue;
            }

            $user = explode('@', $email);

            $import = NyuSurvey::query()->where('netId', $user[0])->first();

            if (empty($import)) {
                $import = NyuSurvey::query()->create([
                    'netId' => $user[0],
                    'email' => $email
                ]);
            }
        }
    }

    private function getEmail(array $item)
    {
        $values = $item['values'] ?? null;

        if (empty($values)) {
            return null;
        }

        $finished = $values['finished'] ?? 0;
        if($finished !== 1){
            $this->error("c");
            return null;
        }

        $email = $values['NetID Email'] ?? null;
        if (empty($email)) {
            return null;
        }

        return $email;
    }
}
