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
        Schema::create('pawn_register_amount_aditionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pawnRegister_id')->nullable()->constrained('pawn_registers');
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');

            $table->decimal('amountTotal', 10, 2)->nullable();//Total en boliviano
            $table->decimal('dollarTotal', 10, 2)->nullable();//total en dolar
            $table->decimal('dollarPrice', 10, 2)->nullable();//Para el valor de tipo de cambio

            $table->text('description')->nullable();


            $table->timestamps();
            
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deletedUser_id')->nullable()->constrained('users');
            $table->string('deletedRole')->nullable();
            $table->text('deletedObservation')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pawn_register_amount_aditionals');
    }
};
