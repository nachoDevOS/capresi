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
        Schema::create('bonus', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->nullable(); // Año (por ejemplo, 2024)

            $table->smallInteger('paid')->default(0);//indica que no fue pgado aun

            $table->decimal('totalPayment', 20,6)->nullable(); // Monto total (ejemplo opcional)

            $table->text('description')->nullable();
            
            $table->string('status')->default('pendiente');
            
            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
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
        Schema::dropIfExists('bonus');
    }
};
