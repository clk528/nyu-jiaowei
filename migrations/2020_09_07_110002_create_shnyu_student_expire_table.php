<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShnyuStudentExpireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shnyu_student_expire', function (Blueprint $table) {
            $table->increments('id');
            $table->string('netId', 10)->unique()->comment('NetId');
            $table->date("expire")->nullable('到期时间');
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
        Schema::dropIfExists('shnyu_student_expire');
    }
}
