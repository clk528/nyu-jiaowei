<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShnyuJkxxSsjbxxGetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shnyu_jkxx_ssjbxx_get', function (Blueprint $table) {
            $table->id('t_id');

            $table->string('ID', 10)->unique();

            $table->string('XM', 120)->comment('姓名');
            $table->string('ZJHM', 32)->comment('证件号码');
            $table->string('SJH', 30)->comment('手机号');
            $table->string('SFLX', 32)->comment('身份类型');
            $table->string('XXMC', 120)->comment('学校名称');
            $table->string('XWFX_SFZSH', 500)->comment('行为分析是否在上海');
            $table->string('JW_CRJ', 500)->comment('境外出入国家信息');
            $table->string('JW_FHRQ', 10)->comment('境外返沪日期');
            $table->string('SSM_ZT', 10)->comment('随申码状态');
            $table->string('SSM_ZTMC', 10)->comment('随申码状态名称');
            $table->string('SSM_LY', 30)->comment('随申码来源');
            $table->string('MQ_JC', 30)->comment('判断是否密切接触');
            $table->string('QG_ZT', 30)->comment('全国码状态');
            $table->string('QG_ZTMC', 30)->comment('全国码状态名称');
            $table->string('QG_FKRQ', 10)->comment('全国码反馈日期');
            $table->string('QG_JXHGJBG', 4000)->comment('全国码解析后国家报告');
            $table->string('QG_JXQGJBG', 500)->comment('全国码解析国家报告');
            $table->string('TXRJQZZT', 500)->comment('同行入境确诊状态');

            $table->string('UPDATE_TIME', 10)->comment('更新时间');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shnyu_jkxx_ssjbxx_get');
    }
}
