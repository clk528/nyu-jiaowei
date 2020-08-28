<?php

namespace clk528\NyuJiaoWei\Exporters;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class SurverExportor extends ExcelExporter implements WithMapping,ShouldAutoSize
{
    protected $fileName = '用户列表.xlsx';

    protected $columns = [
        'netId' => 'NetId',
        'email' => 'Email'
    ];

    public function map($user): array
    {
        return [
            $user->netId,
            $user->email
        ];
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }
}
