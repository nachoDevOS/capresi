<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');

            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('quantity', 10, 2)->nullable();//Para los gramos de joyas y cantidad o stock

            $table->decimal('amountTotal', 10, 2)->nullable();//Total en boliviano
            $table->decimal('dollarTotal', 10, 2)->nullable();//total en dolar
            $table->decimal('dollarPrice', 10, 2)->nullable();//Para el valor de tipo de cambio

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
        Schema::dropIfExists('sale_details');
    }
};
