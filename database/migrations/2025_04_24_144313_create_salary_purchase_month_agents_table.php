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
        Schema::create('salary_purchase_month_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salaryPurchaseMonth_id')->nullable()->constrained('salary_purchase_months'); // cuando es mes de interes, caso contrario se ignora
            $table->foreignId('salaryPurchase_id')->nullable()->constrained('salary_purchases');

            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->string('type')->nullable();//Para saber que tipo de pago fue "Interes o devolucion de dinero"

            $table->decimal('amount',11, 2)->nullable();

            $table->decimal('dollarTotal', 10, 2)->nullable();
            $table->decimal('dollarPrice', 10, 2)->nullable();


            $table->foreignId('agent_id')->nullable()->constrained('users');
            $table->string('agentType')->nullable();

            $table->smallInteger('status')->default(1);

            $table->timestamps();     
                   
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
        Schema::dropIfExists('salary_purchase_month_agents');
    }
};
