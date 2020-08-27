<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShnyuJkxxSsjbxxPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shnyu_jkxx_ssjbxx_post', function (Blueprint $table) {
            $table->increments('ID');

            $table->string('NetID', 10)->unique()->comment('NetId');
            $table->string('XM', 120)->comment('姓名');
            $table->tinyInteger('ID_Type')->comment('证件类型 0=身份证,1=台港澳通行证,2=护照');
            $table->string('ZJHM', 32)->comment('证件号码');
            $table->string('SJH',30)->comment('手机号');
            $table->string('SFLX', 32)->comment('身份类型');
            $table->integer('Subtype')->comment('身份子类型,参见字典');
            $table->string('XXMC', 120)->default('上海纽约大学')->comment('学校名称');
            $table->string('Last_Update_By', 10)->comment('最后更新人NetID');

            $table->timestamp("Created_At", 0)->nullable()->comment('写入时间');
            $table->timestamp("Last_Update", 0)->nullable()->comment('最后更新日期');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shnyu_jkxx_ssjbxx_post');
    }
}
