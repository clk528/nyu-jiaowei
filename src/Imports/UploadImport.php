<?php

namespace clk528\NyuJiaoWei\Imports;

use clk528\NyuJiaoWei\Models\UploadData;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class UploadImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    public $success = [];
    private $failed = [];

    private $operator;

    public function headingRow(): int
    {
        return 1;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getFailed()
    {
        return $this->failed;
    }

    public function setOperator($netId)
    {
        $this->operator = $netId;
        return $this;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $upload = [
            'NetID' => $this->getValue($data['netid'] ?? null),
            'XM' => $this->getValue($data['name'] ?? null),
            'ID_Type' => $this->getIdType($data['idtype'] ?? null),
            'ZJHM' => $this->getValue($data['idno'] ?? null),
            'SJH' => $this->getValue($data['mobile'] ?? null),
            'SFLX' => $this->getValue($data['type'] ?? null),
            'Subtype' => $this->getValue($data['subtype'] ?? null),
        ];

        if ($this->arrayValueIsNull($upload)) {
            return;
        }

        //todo 这里要做身份校验

        if (!$this->arrayValueIsNotNull($upload)) {
            $this->failed[] = [
                'line' => $row->getIndex(),
                'content' => $this->t($upload),
            ];
            return;
        }

        $upload['XXMC'] = '上海纽约大学';
        $upload['Last_Update_By'] = $this->operator;


        $user = UploadData::query()->where('NetID', $upload['NetID'])->first();

        if (empty($user)) {
            $result = UploadData::query()->create($upload);
        } else {
            $result = $user->fill($upload)->save();
        }

        if ($result) {
            $this->success[] = $upload;
        } else {
            $this->failed[] = [
                'line' => $row->getIndex(),
                'content' => $this->t($upload),
            ];
        }
    }

    /**
     * 数组中的value 是不是都是null
     *
     * @param array $data
     *
     * @return bool
     */
    private function arrayValueIsNull(array $data)
    {
        $bool = true;

        foreach ($data as $key => $value) {

            $item = trim($value);

            if (!empty($item)) {
                $bool = false;
                break;
            }
        }

        return $bool;
    }

    /**
     * 数组中的value 是不是都不是null
     *
     * @param array $data
     *
     * @return bool
     */
    private function arrayValueIsNotNull(array $data)
    {
        $bool = true;

        foreach ($data as $key => $value) {
            if ($key == 'ID_Type' && is_int($value) && $value >= 0) {
                continue;
            }

            $item = trim($value);

            if (empty($item)) {
                $bool = false;
                break;
            }
        }

        return $bool;
    }

    private function getValue(string $key)
    {
        if (empty($key)) {
            return null;
        }
        return trim($key);
    }

    private function getIdType($type)
    {
        if (empty($type)) {
            return null;
        }
        $type = trim($type);

        if ($type == '身份证') {
            return 0;
        }

        if ($type == '港澳台通行证') {
            return 1;
        }

        if ($type == '护照') {
            return 3;
        }
        return null;
    }

    private function t(array $data)
    {
        $str = null;
        foreach ($data as $k => $v) {
            $str .= "$k = $v,";
        }
        return $str;
    }
}

