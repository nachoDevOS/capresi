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
        Schema::create('spreadsheet_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained('contracts');
            $table->foreignId('spreadsheet_id')->nullable()->constrained('spreadsheets');

            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('cashierMovement_id')->nullable()->constrained('cashier_movements');
            $table->smallInteger('paid')->default(0);//indica que no fue pgado aun

            $table->date('dateStart')->nullable();
            $table->date('dateFinish')->nullable();

            $table->integer('dayWorked')->nullable();//Para acumular los dias trabajados del periodo
            $table->integer('dayWorkedFebrary')->nullable();//Para acumular los dias trabajados del periodo 'FEBRERO'

            $table->decimal('salary',20,6)->nullable(); // salario por mes
            $table->decimal('payment',20,6)->nullable(); // Monto total de pago total de trabajo sin descuento dias trabajados
            $table->decimal('liquidPaid',20,6)->nullable(); // monto liquido pagable con todos sus descuento

            $table->time('minuteLate')->nullable();//Para obtener el total de minutos acumulado de atrazo de las horas que pertenescan al dia 
            $table->decimal('minuteLateAmount',20,6)->default(0);//Para obtener el total de horas o horarios perdido o abandonado en Bs.
            $table->decimal('cantHourAmount',20,6)->default(0);//Para obtener el total de horas o horarios perdido o abandonado en Bs.
            $table->decimal('advancement',20,6)->default(0);//Para obtener todos los adelantos




            $table->foreignId('paid_userId')->nullable()->constrained('users');
            $table->string('paid_agentType')->nullable();
            $table->dateTime('paidDate')->nullable();//indica la fecha que fue pagado

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
        Schema::dropIfExists('spreadsheet_contracts');
    }
};
