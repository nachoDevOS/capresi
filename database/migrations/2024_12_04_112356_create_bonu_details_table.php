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
        Schema::create('bonu_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('people_id')->nullable()->constrained('people');
            $table->foreignId('bonu_id')->nullable()->constrained('bonus');

            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('cashierMovement_id')->nullable()->constrained('cashier_movements');
            $table->smallInteger('paid')->default(0);//indica que no fue pgado aun
            $table->decimal('payment',20,6)->nullable();
            $table->integer('dayWorked')->nullable();
            

            $table->foreignId('paid_userId')->nullable()->constrained('users');
            $table->string('paid_agentType')->nullable();
            $table->dateTime('paidDate')->nullable();//indica la fecha que fue pagado

            $table->timestamps();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deleted_observation')->nullable();
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
        Schema::dropIfExists('bonu_details');
    }
};
