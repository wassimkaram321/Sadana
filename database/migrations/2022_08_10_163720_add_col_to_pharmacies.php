<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColToPharmacies extends Migration
{

    public function up()
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('from');
            $table->string('to');
            $table->string('statusToday');
            $table->text('Address');
            $table->integer('land_number');
        });
    }

    public function down()
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            //
        });
    }
}
