<?php


namespace clk528\NyuJiaoWei\Models;


use Illuminate\Database\Eloquent\Model;

class NyuStudentExpire extends Model
{
    protected $table = 'shnyu_student_expire';

    protected $fillable = [
        'netId',
        'expire'
    ];
}
