<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{

    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('area_name');
            $table->bigInteger('area_status')->default(1);
            $table->bigInteger('area_num')->default(0);
            $table->timestamps();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

        });
    }


    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
