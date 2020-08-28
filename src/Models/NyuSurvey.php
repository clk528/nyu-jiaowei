<?php


namespace clk528\NyuJiaoWei\Models;


use Illuminate\Database\Eloquent\Model;

class NyuSurvey extends Model
{
    protected $table = 'nyu_surveys';

    protected $fillable = ['netId','email'];
}
