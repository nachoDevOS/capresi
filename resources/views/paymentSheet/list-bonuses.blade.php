
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
        
        @forelse ($bonuses as $bonus)
            <div class="panel-body">
                <table width="100%">
                    <tr>
                        <td style="text-align: center;  width:95%">
                
                            <small style="font-size: 20px">
                                DETALLES 
                            </small>

                        </td>
                        <td style="text-align: center;  width:5%">
                            @if ($bonus->bonuDetail->first()->paid == 0 && $cashier)
                                <a href="#" class="btn btn-sm btn-success" id="id_payment" onclick="showConfirmationModal('aguinaldo', {{$bonus->bonuDetail->first()->id }})"  data-toggle="modal" title="Pagar" ><i class="fa-solid fa-file-invoice-dollar"></i> Pagar </a>
                            @endif
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
                                        <small>{{$bonus->bonuDetail->first()->people->ci}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px">
                                        <small style="color: red">NOMBRE:</small>
                                    </td>
                                    <td>
                                        <small>{{ strtoupper($bonus->bonuDetail->first()->people->first_name) }} {{ strtoupper($bonus->bonuDetail->first()->people->last_name1) }} {{ strtoupper($bonus->bonuDetail->first()->people->last_name2) }}</small>

                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px">
                                        <small style="color: red">CELULAR</small>
                                    </td>
                                    <td>
                                        <small>{{$bonus->bonuDetail->first()->people->cell_phone}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><small style="color: red">AÑO</small></td>
                                    <td>
                                        <small>{{$bonus->year}}</small>
                                    </td>
                                </tr>
                  
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
                                        <small style="color: red">AGUINALDO A PAGAR:</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <small>Bs. {{number_format($bonus->bonuDetail->first()->payment, 2, '.', '') }}</small>
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
                                        @if ($bonus->bonuDetail->first()->paid==1)
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