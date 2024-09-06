<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_material_type');
            $table->text('item_description');
            $table->decimal('item_unit_price', 10, 2);
            $table->string('item_quantity')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_artisan')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}
