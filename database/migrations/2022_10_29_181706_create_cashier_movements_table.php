<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashier_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('cashier_movement_category_id')->nullable()->constrained('cashier_movement_categories');
            $table->decimal('balance', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('transferCashier_id')->nullable()->constrained('cashiers');


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
        Schema::dropIfExists('cashier_movements');
    }
}
