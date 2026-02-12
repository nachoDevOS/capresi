<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('people_id')->nullable()->constrained('people');
            $table->foreignId('guarantor_id')->nullable()->constrained('people');
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('payments_period_id')->nullable()->constrained('payments_periods');
            $table->foreignId('manager_id')->nullable()->constrained('managers');
            $table->string('code')->nullable();
            $table->string('typeLoan')->nullable();
            $table->date('date')->nullable();
            $table->integer('day')->nullable();
            $table->integer('month')->nullable();
            $table->decimal('porcentage',11,2)->nullable();
            $table->decimal('amountLoan',11,2)->nullable();


            $table->decimal('debt',11,2)->nullable();

            $table->decimal('amountPorcentage',11,2)->nullable();
            $table->decimal('amountTotal',11,2)->nullable();
            // $table->foreignId('agentCollection_id')->nullable()->constrained('agent_collections');

            $table->text('observation')->nullable();
            $table->string('status')->nullable('pendiente');

            $table->string('delivered')->default('No');
            $table->date('dateDelivered')->nullable();
            $table->foreignId('delivered_userId')->nullable()->constrained('users');
            $table->string('delivered_agentType')->nullable();

            $table->string('transaction_id')->nullable();


            $table->foreignId('inspector_userId')->nullable()->constrained('users');
            $table->string('inspector_agentType')->nullable();

            $table->foreignId('success_userId')->nullable()->constrained('users');
            $table->string('success_agentType')->nullable();
            
            $table->foreignId('cashierRegister_id')->nullable()->constrained('cashiers');
            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deleteObservation')->nullable();
            $table->string('deletedKey')->nullable();
            $table->date('notificationDate')->default(date('Y-m-d'));
            $table->bigInteger('notificationQuantity')->default(0);

            $table->string('notification')->nullable()->default('si');


            $table->smallInteger('mora')->default(0);
            $table->string('recovery')->default('no');//Para identificar que el prestamos entra en recuperacion de lo saldo que deben, Para los bonus del cobrador


        
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
        Schema::dropIfExists('loans');
    }
}
