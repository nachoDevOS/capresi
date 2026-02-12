{{-- <form class="form-submit" action="{{ route('bonuses.store') }}" method="post">
    @csrf --}}
    {{-- <input type="hidden" name="direccion_id" value="{{ $direccion->id }}">
    <input type="hidden" name="procedure_type_id" value="{{ $procedure_type_id }}"> --}}
    <input type="hidden" name="year" value="{{ $year }}">
    <div class="col-md-12">
        <div id="dataTable" class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="text-center">N&deg;</th>
                        <th class="text-center">ID</th>
                        <th class="text-center">NOMBRE COMPLETO</th>
                        <th class="text-center">CI</th>
                        <th class="text-center">DIRECCIÓN ADMINISTRATIVA</th>
                        <th class="text-center">DETALLES</th>
                        <th class="text-center">MONTO</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cont = 1;
                        $total = 0;
                    @endphp
                    @foreach ($bonuses as $bonus)
                        @php
                            /*
                                Si se seleccionó el tipo de planilla se debe recorrer todos los primeros contratos
                                para verificar si es de ese tipo de planilla exite en la lista
                            */
                            // if($procedure_type_id){
                            //     $encontrado = false;
                            //     foreach ($bonus->contracts_list as $contracts_list) {
                                    
                            //         if($contracts_list['contracts']['0']->procedure_type_id == $procedure_type_id){
                            //             $encontrado = true;
                            //         }
                            //     }
                            //     // Si no se encontró se hace un salto de interacción
                            //     if (!$encontrado) {
                            //         continue;
                            //     }
                            // }

                            $amount_accumulated = 0;
                        @endphp
                        <tr>
                            <td>{{ $cont }}</td>
                            <td>{{ $bonus->id }}</td>
                            <td>{{ $bonus->first_name }} {{ $bonus->last_name }}</td>
                            <td>{{ $bonus->ci }}</td>
                            <td></td>
                            <td>
                                <table class="table" style="margin: 0px">
                                    @php
                                        $index = 1;
                                        $days_total = 0;
                                    @endphp
                                    @foreach ($bonus->contracts_list as $contracts_list)
                                        @php
                                            $amounts = array();
                                            $subtotal = 0;
                                            $count = 1;
					                        $count_contracts = 1;
                                            $contracts = array();
                                            $current_contract = $contracts_list['contracts'][0];
                                            foreach ($contracts_list['contracts'] as $contract) {
                                                array_push($contracts, $contract->id);
                                                $contract_paymentschedules_details = $contract->paymentschedules_details->sortByDesc('paymentschedule.period.name')->groupBy('paymentschedule.period.name');
                                                
                                                // El último pago no se toma en cuenta a menos que sea de diciembre (porque no se ha planillado)
                                                // solo se aplica para el último pago del primer contrato (último contrato ORDER BY DESC)
                                                if(date('m', strtotime($contract->finish)) < 12 && $count_contracts == 1){
                                                    $contract_paymentschedules_details = $contract_paymentschedules_details->slice(1);
                                                }

                                                foreach ($contract_paymentschedules_details as $key => $paymentschedules_detail) {
                                                    $salary = $paymentschedules_detail->sum('partial_salary') / $paymentschedules_detail->count();
                                                    $seniority_bonus_amount = $paymentschedules_detail->sum('seniority_bonus_amount') / $paymentschedules_detail->count();
                                                    $subtotal += $salary + $seniority_bonus_amount;

                                                    // Buscar que el perido no haya sido registrado
                                                    $index_search = array_search($key, array_column($amounts, 'period'));
                                                    if ($index_search !== false) {
                                                        $amounts[$index_search]['salary'] += $salary;
                                                        $amounts[$index_search]['seniority_bonus_amount'] += $seniority_bonus_amount;
                                                    }else{
                                                        array_push($amounts, ['period' => $key, 'salary' => $salary, 'seniority_bonus_amount' => $seniority_bonus_amount]);
                                                        // En caso de que haya un contrato que se haya pagado en 2 periodos se quita un recorrido
                                                        if(count($contracts_list['contracts']) >= $count_contracts){
                                                            for ($i=$count_contracts; $i < count($contracts_list['contracts']); $i++) { 
                                                                foreach ($contracts_list['contracts'][$i]->paymentschedules_details->sortByDesc('paymentschedule.period.name')->groupBy('paymentschedule.period.name') as $period => $value) {
                                                                    if($period == $key){
                                                                        $count--;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    $count++;

                                                    if ($count > 3) {
                                                        break;
                                                    }
                                                }
                                                if ($count > 3) {
                                                    break;
                                                }
						                        $count_contracts++;
                                            }
                                            $amount_subtotal = (($subtotal /3) /360) * ($contracts_list['days']);
                                            $amount_accumulated += $amount_subtotal;
                                            $days_total += $contracts_list['days'];
                                            $start = $contracts_list['contracts'][count($contracts_list['contracts']) -1]->start;
                                            $finish = $current_contract->finish;
                                        @endphp
                                        {{-- Si no se seleccionó el tipo de planilla o seleccionó y es igual a la del último contrato (Primero ORDER BY DESC) --}}
                                        @if (!$procedure_type_id || $current_contract->procedure_type_id == $procedure_type_id)
                                            <tr>
                                                <td title="{{ date('d/m/Y', strtotime($start)) }} - {{ $finish ? date('d/m/Y', strtotime($finish)) : 'No definida' }}"><a href="{{ route('contracts.show', $current_contract->id) }}#table-payments-history" target="_blank">{{ $current_contract->type->name }}</a></td>
                                                @php
                                                    $index = 1;
                                                    $average = 0;
                                                @endphp
                                                {{-- Mostrar los últimos 3 meses planillados --}}
                                                @foreach ($amounts as $amount)
                                                <td class="text-right" title="{{ $amount['period'] }}">
                                                    {{ $amount['salary'] + $amount['seniority_bonus_amount'] }}
                                                    <input type="hidden" name="partial_salary_{{ $index }}[]" value="{{ $amount['salary'] }}">
                                                    <input type="hidden" name="seniority_bonus_{{ $index }}[]" value="{{ $amount['seniority_bonus_amount'] }}">
                                                </td>
                                                @php
                                                    $index++;
                                                    $average += $amount['salary'] + $amount['seniority_bonus_amount'];
                                                @endphp
                                                @endforeach
                                                <td class="text-right"><b>{{ number_format($average /3, 2, ',', '.') }}</b></td>
                                                <td class="text-right"><b>{{ $contracts_list['days'] }}</b></td>
                                                <td class="text-right">
                                                    <b>{{ $amount_subtotal == intval($amount_subtotal) ? intval($amount_subtotal) : number_format($amount_subtotal, 2, ',', '.') }}</b>
                                                    <input type="hidden" name="contract_id[]" value="{{ $current_contract->id }}">
                                                    <input type="hidden" name="contract_procedure_type_id[]" value="{{ $current_contract->procedure_type_id }}">
                                                    <input type="hidden" name="contracts[]" value="{{ json_encode($contracts) }}">
                                                    <input type="hidden" name="start[]" value="{{ $start }}">
                                                    <input type="hidden" name="finish[]" value="{{ $finish ?? '' }}">
                                                    <input type="hidden" name="days[]" value="{{ $contracts_list['days'] }}">
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr>
                                        <td colspan="5" class="text-right"><b>TOTAL</b></td>
                                        <td class="text-right"><b @if($days_total > 360) class="text-danger" @endif>{{ $days_total }}</b></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="text-right">{{ $amount_accumulated == intval($amount_accumulated) ? intval($amount_accumulated) : number_format($amount_accumulated, 2, ',', '.') }}</td>
                        </tr>
                        @php
                            $total += $amount_accumulated;
                            $cont++;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-right"><b>TOTAL</b></td>
                        <td class="text-right"><b>{{ number_format($total, 2, ',', '.') }}</b></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-12 text-right" style="margin-top: 50px">
        <label class="checkbox-inline"><input type="checkbox" value="" required>Aceptar y continuar</label>
    </div>
    <div class="col-md-12 text-right">
        <button type="submit" class="btn btn-success btn-lg btn-submit">Guardar</button>
    </div>    
{{-- </form> --}}
<script>
    $(document).ready(function(){
        $('.form-submit').submit(function(e){
            $('.form-submit .btn-submit').attr('disabled', true);
        });
    });
</script>