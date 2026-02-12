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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            
            $table->string('code')->nullable();
            $table->foreignId('person_id')->nullable()->constrained('people');
            $table->string('typeSale')->nullable();
            $table->dateTime('saleDate')->nullable();//Para registrar la fecha de la venta

            $table->decimal('amount', 10, 2)->nullable();//Monto  discount
            $table->decimal('discount', 10, 2)->nullable();//Monto de descuento
            $table->decimal('debt', 10, 2)->nullable();//Monto de deuda pendiente


            $table->decimal('amountTotal', 10, 2)->nullable();//Total en boliviano
            $table->decimal('dollarTotal', 10, 2)->nullable();//total en dolar
            $table->decimal('dollarPrice', 10, 2)->nullable();//Para el valor de tipo de cambio

            
            $table->date('datePayment')->nullable();//Para registrar la fecha de pago cuado se da a credito
            $table->string('status')->nullable(); // pagado/credito
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
        Schema::dropIfExists('sales');
    }
};
