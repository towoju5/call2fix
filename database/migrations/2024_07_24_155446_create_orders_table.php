<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained();
            $table->string('seller_id')->constrained('users');
            $table->string('product_id')->constrained();
            $table->string('status');
            $table->string('order_id');
            $table->enum('delivery_type', ['home_delivery', 'pickup'])->default('home_delivery');
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2);
            $table->string('delivery_address')->nullable();
            $table->string('delivery_longitude')->nullable();
            $table->string('delivery_latitude')->nullable();
            $table->string('shipping_fee')->nullable();
            $table->string('kwik_order_id')->nullable();
            $table->text('additional_info')->nullable();
            $table->dateTime('estimated_delivery')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
