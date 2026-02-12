
{{-- <div class="col-md-12 text-right">

    <button type="button" onclick="report_excel()" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Excel</button>
    <button type="button" onclick="report_print()" class="btn btn-dark"><i class="glyphicon glyphicon-print"></i> Imprimir</button>

</div> --}}
<div class="col-md-12">
    <div class="panel panel-bordered">
        @if (!$cashier)
            <div class="alert alert-warning">
                <strong>Advertencia:</strong>
                <p>No puedes pagar debido a que no tiene una caja activa.</p>
            </div>
        @endif
        
        @forelse ($spreadsheets as $spreadsheet)
            <div class="panel-body">
                <table width="100%">
                    <tr>
                        <td style="text-align: center;  width:95%">
                
                            <small style="font-size: 20px">
                                DETALLES 
                            </small>

                        </td>
                        <td style="text-align: center;  width:5%">
                            @if ($spreadsheet->spreadsheetContract->first()->paid == 0 && $cashier)
                                <a href="#" class="btn btn-sm btn-success" id="id_payment" onclick="showConfirmationModal('periodo', {{$spreadsheet->spreadsheetContract->first()->id }})"  data-toggle="modal" title="Pagar" ><i class="fa-solid fa-file-invoice-dollar"></i> Pagar </a>
                            @endif

                            {{-- @if ($contract->paid == 1)
                                <a href="#" class="btn btn-sm btn-success" onclick="openPrinf({{$contract}})" data-toggle="modal" title="Imprimir Comprobante" ><i class="fa-solid fa-print"></i> </a>
                            @endif --}}
                        </td>
                    </tr>
                </table>
                <hr style="margin: 0px">
                <table width="100%" cellpadding="10" style="font-size: 15px">
                    <tr>
                        <td width="40%">
                            <table width="100%" cellpadding="15">
                                <tr>
                                    <td width="100px">
                                        <small style="color: red">CI:</small>
                                    </td>
                                    <td>
                                        <small>{{$spreadsheet->spreadsheetContract->first()->contract->people->ci}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px">
                                        <small style="color: red">NOMBRE:</small>
                                    </td>
                                    <td>
                                        <small>{{ strtoupper($spreadsheet->spreadsheetContract->first()->contract->people->first_name) }} {{ strtoupper($spreadsheet->spreadsheetContract->first()->contract->people->last_name1) }} {{ strtoupper($spreadsheet->spreadsheetContract->first()->contract->people->last_name2) }}</small>

                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px">
                                        <small style="color: red">CELULAR</small>
                                    </td>
                                    <td>
                                        <small>{{$spreadsheet->spreadsheetContract->first()->contract->people->cell_phone}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">CARGO</small></td>
                                    <td>
                                        <small>{{$spreadsheet->spreadsheetContract->first()->contract->work}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">SUELDO</small></td>
                                    <td>
                                        <small>Bs. {{$spreadsheet->spreadsheetContract->first()->contract->salary}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">PERIODO</small></td>
                                    <td>
                                        <small>{{$spreadsheet->month}}-{{$spreadsheet->year}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">F. INICIO</small></td>
                                    <td>
                                        <small>{{date('d/m/Y', strtotime($spreadsheet->spreadsheetContract->first()->contract->dateStart))}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">F. FIN</small></td>                            
                                    <td>
                                        <small>{{date('d/m/Y', strtotime($spreadsheet->spreadsheetContract->first()->contract->dateFinish))}}</small>
                                    </td>
                                </tr>
                            </table>
                            <br>
                            <table id="dataStyle" width="100%" cellpadding="15" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align: center">Periodo</th>
                                        <th style="text-align: center">Fecha</th>
                                        <th>Observaciones</th>
                                        <th style="text-align: center">Registrado por</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $total = 0;
                                    @endphp
                                    @php
                                        $data = \App\Models\ContractAdvancement::where('contract_id', $spreadsheet->spreadsheetContract->first()->contract->id)
                                            ->where('periodMonth', $spreadsheet->month)
                                            ->where('periodYear', $spreadsheet->year) 
                                            ->where('deleted_at', null)
                                            ->get();
                                        // dump($data);
                                    @endphp
                                    @forelse ($data as $item)
                                        <tr >
                                            <td>
                                                {{$item->periodMonth}}-{{$item->periodYear}}     
                                            </td>
                                            <td style="text-align: center">
                                                <small>{{date('d/m/Y H:i:s', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans()}}</small>
                                            </td>
                                            <td>
                                                {{$item->observation}}     
                                            </td>
                                            <td style="text-align: center">{{$item->register_agentType}} <br> {{$item->registerUser->name}}</td>
                                            <td>
                                                {{$item->advancement}}
                                            </td>
                    
                                            @php
                                                $total+=$item->advancement;
                                            @endphp
                                        </tr>
                                    @empty
                                        <tr>
                                            <td style="text-align: center" valign="top" colspan="5" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse

                                    <tr>
                                        <td colspan="4">Total</td>
                                        <td>{{ number_format($total, 2, ',', '.') }}</td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        
                        </td>
                        <td width="60%">
                            <table width="100%" cellpadding="15">
                                <tr>
                                    <td colspan="3" style="text-align: center"><small>DETALLES.</small></td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>
                                    <td width="80%">
                                        <small style="color: red">ADELANTOS DEL CONTRATO:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <small>Bs. {{ number_format($spreadsheet->spreadsheetContract->first()->advancement, 2, '.', '') }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                    </td>
                                    <td style="text-align: right;">
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        <small style="color: red">MINUTOS ACUMULADOS:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <small > {{$spreadsheet->spreadsheetContract->first()->minuteLate}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        
                                    </td>
                                    <td style="text-align: right;">
                                        <small >Bs. {{ number_format($spreadsheet->spreadsheetContract->first()->minuteLateAmount, 2, '.', '') }}</small>
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        <small style="color: red">DIAS ACUMULADOS:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        {{-- <small>Total de días {{$contract->dayLate}}</small> --}}
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        <small style="color: red"></small>
                                    </td>
                                    <td style="text-align: right;">
                                        <small>Bs. {{ number_format($spreadsheet->spreadsheetContract->first()->cantHourAmount, 2, '.', '') }}
                                            
                                        </small>
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        <small style="color: red">TOTAL A PAGAR:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <small>Bs. {{number_format($spreadsheet->spreadsheetContract->first()->liquidPaid, 2, '.', '') }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                    </td>
                                    <td style="text-align: right;">
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>

                                    <td width="80%">
                                        <small style="color: red">ESTADO:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        @if ($spreadsheet->spreadsheetContract->first()->paid==1)
                                            <label class="label label-success">Pagado</label>
                                        @else
                                            <label class="label label-warning">Sin Pagar</label>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <br>
                        
                        </td>
                    </tr>
                    
                </table>
            </div>
        @empty
            <div class="panel-body">
                <table width="100%">
                    <tr>
                        <td style="text-align: center;  width:95%">
                
                            <small style="font-size: 20px">
                                Sin registro 
                            </small>

                        </td>
                    </tr>
                </table>
            </div>
        @endforelse
        
    </div>
</div>

    





<style>
    body{
        margin: 0px auto;
        font-family: Arial, sans-serif;
        font-weight: 100;
        /* max-width: 740px; */
    }
    #watermark {
        position: absolute;
        opacity: 0.1;
        z-index:  -1000;
    }
    #watermark img{
        position: relative;
        width: 300px;
        height: 300px;
        left: 205px;
    }
    .show-print{
        display: none;
        padding-top: 15px
    }
    .btn-print{
        padding: 5px 10px
    }
    @media print{
        .hide-print, .btn-print{
            display: none
        }
        .show-print, .border-bottom{
            display: block
        }
        .border-bottom{
            border-bottom: 1px solid rgb(90, 90, 90);
            padding: 20px 0px;
        }
    }
</style>

<script>
    $(document).ready(function(){
        
    })

    



</script>