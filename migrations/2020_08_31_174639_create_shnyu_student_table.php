<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShnyuStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shnyu_student', function (Blueprint $table) {
            $table->increments('id');

            $table->string('netId', 10)->unique()->comment('NetId');

            $table->enum('status', [
                'enabled',
                'disabled',
                'alert'
            ])->default('enabled');

            $table->tinyInteger('alert_total')->default(0)->comment('微信提醒次数');

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
        Schema::dropIfExists('shnyu_student');
    }
}
