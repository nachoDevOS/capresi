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
        Schema::create('bonu_detail_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bonuDetail_id')->nullable()->constrained('bonu_details');
            $table->foreignId('contract_id')->nullable()->constrained('contracts');


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
        Schema::dropIfExists('bonu_detail_contracts');
    }
};
