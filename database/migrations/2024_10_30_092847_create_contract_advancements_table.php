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
        Schema::create('contract_advancements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->nullable()->constrained('contracts');
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('cashierMovement_id')->nullable()->constrained('cashier_movements');

            $table->smallInteger('spreadsheet')->default(0);//0 para saber que ya estubo en planilla
            $table->integer('periodMonth')->nullable(); // Mes (1 a 12) para la planilla
            $table->integer('periodYear')->nullable(); // Año (por ejemplo, 2024) para la planilla

            $table->decimal('advancement', 11,2)->nullable();
            
            $table->dateTime('dateAdvancement')->nullable();   

            $table->text('observation')->nullable();

            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();

            $table->timestamps();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deletedObservation')->nullable();
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
        Schema::dropIfExists('contract_advancements');
    }
};
