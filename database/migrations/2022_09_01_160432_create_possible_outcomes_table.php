<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePossibleOutcomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('possible_outcomes', function (Blueprint $table) {
            $table->id();
            $table->integer('num')->unique();
            $table->boolean('isEven');
            $table->boolean('isOdd');
            $table->boolean('isLow');
            $table->boolean('isHigh');
            $table->boolean('isRed');
            $table->boolean('isBlack');
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
        Schema::dropIfExists('possible_outcomes');
    }
}
