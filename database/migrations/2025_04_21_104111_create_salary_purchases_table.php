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
        Schema::create('salary_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('people');
            $table->string('code')->nullable();
            $table->date('date')->nullable();

            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('interest_rate', 10, 2)->nullable();

            $table->decimal('dollarTotal', 10, 2)->nullable();//total en dolar
            $table->decimal('dollarPrice', 10, 2)->nullable();//Para el valor de tipo de cambio
            $table->text('observations')->nullable();

            $table->string('status')->nullable();

            $table->timestamps();            
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_purchases');
    }
};
