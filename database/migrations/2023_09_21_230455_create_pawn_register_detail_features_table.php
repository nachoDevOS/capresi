<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePawnRegisterDetailFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pawn_register_detail_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pawn_register_detail_id')->nullable()->constrained('pawn_register_details');
            $table->foreignId('item_feature_id')->nullable()->constrained('item_features');
            $table->string('title')->nullable();

            $table->string('value')->nullable();
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
        Schema::dropIfExists('pawn_register_detail_features');
    }
}
