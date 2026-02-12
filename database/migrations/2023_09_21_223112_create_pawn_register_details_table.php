<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePawnRegisterDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pawn_register_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pawn_register_id')->nullable()->constrained('pawn_registers');
            $table->foreignId('item_type_id')->nullable()->constrained('item_types');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('quantity', 10, 2)->nullable();

            $table->decimal('amountTotal', 10, 2)->nullable();
            $table->decimal('dollarTotal', 10, 2)->nullable();

            $table->string('image')->nullable();
            $table->text('observations')->nullable();

            $table->integer('inventory')->default(0);

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
        Schema::dropIfExists('pawn_register_details');
    }
}
