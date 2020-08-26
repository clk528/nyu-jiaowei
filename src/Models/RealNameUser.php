<?php


namespace clk528\NyuJiaoWei\Models;


use Illuminate\Database\Eloquent\Model;

class RealNameUser extends Model
{
    protected $table = "realname_user";

    protected $connection = 'workflow';

    protected $fill = [
        'id',
        'n_number',
        'netid',
        'first_name',
        'last_name',
        'nationality',
        'nationality_form_type',
        'birthday',
        'last_process_instance_id',
        'done',
        'phone',
        'verity_result',
        'certify_no',
        'verity_status',
        'health_code',// => 1
        'tour_code',// =>1
        'health',// => 1
        'q1',
        'q2',
        'q3',
        'q4',
        'q5',
        'q6',
        'q7',
        'q8',
        'first',
        'is_create',
    ];

    public function getHealthCodeAttribute($code)
    {
        return $code ? 'Yes' : 'No';
    }

    public function getTourCodeAttribute($code)
    {
        return $code ? 'Yes' : 'No';
    }

    public function getHealthAttribute($code)
    {
        return $code ? 'Yes' : 'No';
    }
}
