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
        Schema::create('pawn_register_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pawnRegister_id')->nullable()->constrained('pawn_registers');
            $table->date('start')->nullable();
            $table->date('finish')->nullable();
            $table->decimal('interest',11, 2)->nullable();
            $table->decimal('debt',11, 2)->nullable();
            $table->smallInteger('paid')->default(0);//indica que no fue pgado aun

            $table->decimal('dollarTotal', 10, 2)->nullable();
            $table->decimal('dollarPrice', 10, 2)->nullable();


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
        Schema::dropIfExists('pawn_register_months');
    }
};
