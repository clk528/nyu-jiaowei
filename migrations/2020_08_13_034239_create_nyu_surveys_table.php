<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNyuSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nyu_surveys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('netId', 10)->unique()->comment('NetId');
            $table->string('email', 20)->unique()->comment('email');
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
        Schema::dropIfExists('nyu_surveys');
    }
}
