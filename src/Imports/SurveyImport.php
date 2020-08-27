<?php


namespace clk528\NyuJiaoWei\Imports;


use clk528\NyuJiaoWei\Models\NyuSurvey;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SurveyImport implements ToModel, WithHeadingRow
{
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        if (isset($row[0]) && !empty($row[0])) {
            $import = NyuSurvey::query()->where('netId', $row[0])->first();

            if (empty($import)) {
                $import = NyuSurvey::query()->create([
                    'netId' => $row[0]
                ]);
            }

            return $import;
        }
        return null;
    }
}
