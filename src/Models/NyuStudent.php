<?php


namespace clk528\NyuJiaoWei\Models;


use Illuminate\Database\Eloquent\Model;

class NyuStudent extends Model
{
    protected $table = 'shnyu_student';

    protected $fillable = [
        'netId',
        'status',
        'alert_total'
    ];
}
