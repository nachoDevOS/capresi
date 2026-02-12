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
        Schema::create('sale_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales');

            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');

            
            $table->decimal('amount',11, 2)->nullable();

            $table->decimal('dollarTotal', 10, 2)->nullable();
            $table->decimal('dollarPrice', 10, 2)->nullable();


            

            $table->smallInteger('status')->default(1);

            $table->timestamps(); 
            $table->foreignId('agent_id')->nullable()->constrained('users');
            $table->string('agentType')->nullable();
                       
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
        Schema::dropIfExists('sale_agents');
    }
};
