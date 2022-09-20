<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserImportTable extends Migration
{
    public function up()
    {
        Schema::create('user_import_excel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f_name');
            $table->string('l_name');
            $table->bigInteger('phone1');
            $table->bigInteger('phone2');
            $table->text('password');
            $table->string('street_address');
            $table->bigInteger('city_id');
            $table->bigInteger('area_id');
            $table->bigInteger('group_id');
            $table->boolean('is_active');
            $table->text('lat');
            $table->text('lng');
            $table->string('pharmacy_name');
            $table->time('to');
            $table->time('from');
            $table->bigInteger('land_number');
            $table->double('card_number');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->double('card_number');
            $table->bigInteger('group_id');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_import');
    }
}
