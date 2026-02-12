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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('codeManual')->nullable();
            $table->string('typeRegister')->nullable();


            $table->foreignId('pawnRegisterDetail_id')->nullable()->constrained(('pawn_register_details'));// Para cuando pasa de prendario a inventaro
            $table->foreignId('itemType_id')->nullable()->constrained('item_types');      //Para agregar un inventario de forma independiente
            $table->text('image')->nullable();
            //:::::::::::::::::::::::::::::
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('quantity', 10, 2)->nullable();//Para los gramos de joyas y cantidad o stock
            $table->integer('stock')->nullable(); //Para el stock de los articulos que se ingresa

            $table->decimal('amountTotal', 10, 2)->nullable();//Total en boliviano
            $table->decimal('dollarTotal', 10, 2)->nullable();//total en dolar
            $table->decimal('dollarPrice', 10, 2)->nullable();//Para el valor de tipo de cambio
            //:::::::::::::::::::::::::::::

            $table->text('description')->nullable();
            $table->string('status')->default('disponible'); //vendido
       

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
        Schema::dropIfExists('inventories');
    }
};
