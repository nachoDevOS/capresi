<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuilatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quilates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jewel_id')->nullable()->constrained('jewels');
            $table->string('name')->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->decimal('pricemin', 11, 2)->nullable();
            $table->smallInteger('status')->default(1);
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
        Schema::dropIfExists('quilates');
    }
}
