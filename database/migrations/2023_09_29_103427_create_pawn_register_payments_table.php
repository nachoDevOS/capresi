<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePawnRegisterPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pawn_register_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pawn_register_id')->nullable()->constrained('pawn_registers');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->date('date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('observations')->nullable();
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
        Schema::dropIfExists('pawn_register_payments');
    }
}
