<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_category_id')->nullable()->constrained('item_categories');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('required')->nullable()->default(1);
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
        Schema::dropIfExists('item_features');
    }
}
