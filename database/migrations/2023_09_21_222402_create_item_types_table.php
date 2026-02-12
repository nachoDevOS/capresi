<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_category_id')->nullable()->constrained('item_categories');
            $table->string('name')->nullable();
            $table->string('unit')->nullable()->default('unid.');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('images')->nullable();
            $table->smallInteger('status')->nullable()->default(1);
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
        Schema::dropIfExists('item_types');
    }
}
