<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePawnRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pawn_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('person_id')->nullable()->constrained('people');
            $table->string('code')->nullable();
            $table->string('codeManual')->nullable();

            $table->date('date')->nullable();
            $table->date('date_limit')->nullable();


            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('delivered_userId')->nullable()->constrained('users');
            $table->string('delivered_userType')->nullable();
            $table->integer('cantMonth')->nullable();

          
            $table->date('dateDelivered')->nullable();

            $table->decimal('amountTotal', 10, 2)->nullable();
            $table->decimal('dollarTotal', 10, 2)->nullable();
            $table->decimal('dollarPrice', 10, 2)->nullable();
            $table->string('endeavor')->nullable();//Para identificar si es nuevo o antiguo el empeño

            
            $table->text('observations')->nullable();
            $table->decimal('interest_rate', 10, 2)->nullable();
            
            $table->integer('inventory')->default(0);

            $table->string('status')->nullable()->default('pendiente');
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
        Schema::dropIfExists('pawn_registers');
    }
}
