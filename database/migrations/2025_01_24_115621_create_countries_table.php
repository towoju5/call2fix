<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('dialing_code')->nullable();
            $table->string('country_name');
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();
            $table->string('currency_name')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_symbol')->nullable();
            $table->json('supported_payment_methods')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
