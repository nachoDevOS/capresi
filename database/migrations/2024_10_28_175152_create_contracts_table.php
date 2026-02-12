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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('people_id')->nullable()->constrained('people');
            $table->string('type')->nullable();
            $table->string('work')->nullable();
            $table->decimal('salary', 11,2)->nullable();
            $table->decimal('advancement', 11,2)->nullable();
            $table->decimal('totalSalary', 11,2)->nullable();


            
            $table->date('dateStart')->nullable();            
            $table->date('dateFinish')->nullable(); 

            $table->text('observation')->nullable();

            $table->smallInteger('paid')->default(0); // "0" que no se encuentra pagado y 1 que ya fue pagado opagada

            $table->string('status')->default('pendiente'); //"pendiente , aprobado, finalizado


            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();

            $table->foreignId('rejected_userId')->nullable()->constrained('users');
            $table->string('rejected_agentType')->nullable();
            $table->text('rejectedObservation')->nullable();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deletedObservation')->nullable();
            
            $table->foreignId('success_userId')->nullable()->constrained('users');
            $table->string('success_agentType')->nullable();

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
        Schema::dropIfExists('contract');
    }
};
