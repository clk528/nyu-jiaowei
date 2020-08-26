<?php

namespace clk528\NyuJiaoWei\Models;

use Illuminate\Database\Eloquent\Model;

class UploadData extends Model
{
    protected $table = 'shnyu_jkxx_ssjbxx_post';

    protected $primaryKey = 'ID';

    const CREATED_AT = 'Created_At';

    public const UPDATED_AT = 'Last_Update';

    protected $connection = 'jiaowei';

    protected $fillable = [
        'NetID',
        'XM',
        'ID_Type',
        'ZJHM',
        'SJH',
        'SFLX',
        'Subtype',
        'XXMC',
        'Last_Update_By',
    ];
}
