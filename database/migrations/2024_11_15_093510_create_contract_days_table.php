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
        Schema::create('contract_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained();
            $table->foreignId('shift_id')->nullable()->constrained('shifts');

            $table->smallInteger('spreadsheet')->default(0);//0 para saber que ya estubo en planilla
            $table->integer('periodMonth')->nullable(); // Mes (1 a 12) para la planilla
            $table->integer('periodYear')->nullable(); // Año (por ejemplo, 2024) para la planilla

            $table->date('date')->nullable();//Para la fecha 
            $table->decimal('paymentDay', 20,6)->nullable();//el total ganado por dia
            $table->integer('dayWeekNumber')->nullable();//dia en numero
            $table->string('dayWeekName')->nullable();//dia en letra


            $table->smallInteger('job')->default(1);//Para identificar si el dia fue trabajado o no sin horario de trabajo como por ejemplo Domingo
            $table->time('minuteLate')->nullable();//Para obtener el total de minutos acumulado de atrazo de las horas que pertenescan al dia 
            $table->integer('cantHour')->default(0);//Para obtener el total de horario o horas de ese dia 
            $table->decimal('cantHourAmount',20,6)->default(0);//Para obtener el total de horas o horarios perdido o abandonado en Bs.

            $table->smallInteger('status')->default(1);
            $table->string('typeLicense')->nullable();
            // $table->smallInteger('day31')->default(0);//0 que no es de los dias 31, 1 que pertenece al 31 y que node
            
            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
            $table->timestamps();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deleted_observation')->nullable();
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
        Schema::dropIfExists('contract_days');
    }
};
