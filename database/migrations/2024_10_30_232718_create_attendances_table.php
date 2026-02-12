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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // $table->foreignId('people_id')->nullable()->constrained('people');
            $table->smallInteger('spreadsheet')->default(0); //0 indica que no pertenece a una planilla 1 que ya pertenece
            $table->string('ci')->nullable();

            $table->date('date')->nullable();
            $table->time('hour')->nullable();
            // $table->dateTime('dateTime')->nullable();

            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
            $table->timestamps();

            // $table->foreignId('deleted_userId')->nullable()->constrained('users');
            // $table->string('deleted_agentType')->nullable();
            // $table->text('deletedObservation')->nullable();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
