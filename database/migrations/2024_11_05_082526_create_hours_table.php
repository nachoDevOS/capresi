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
        Schema::create('hours', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();

            $table->time('hourStart')->nullable();
            $table->time('hourFinish')->nullable();

            $table->integer('minuteLate')->nullable();//la tolerancia de cuanto minutos de espera al marcar
            $table->integer('minuteEarly')->nullable();//por si van a salir unos minutos antes del jornal
        

            $table->time('rangeStartInput')->nullable();//desde la hora que podra marcar cuando entra a trabajar
            $table->time('rangeStartOutput')->nullable();//lapso hasta que hora podra marcar cuando entra a trabajar

            $table->time('rangeFinishInput')->nullable();//desde la hora que podra marcar cuando sale de trabajar cuando se le asigna minutos de salida temprana
            $table->time('rangeFinishOutput')->nullable();//lapso hasta que hora podra marcar cuando sale de trabajar

            $table->double('day',10,2)->nullable();

            $table->text('description')->nullable();
            $table->string('status')->default('normal');


            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
            $table->timestamps();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deletedObservation')->nullable();
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
        Schema::dropIfExists('hours');
    }
};
