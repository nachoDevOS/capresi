<style>
.debt-widget {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.debt-svg-wrap {
    position: relative;
    width: 60px;
    height: 60px;
}
.debt-svg-wrap svg {
    transform: rotate(-90deg);
}
.debt-center-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 9px;
    font-weight: 700;
    line-height: 1.1;
    text-align: center;
    pointer-events: none;
}
.debt-center-label span {
    display: block;
    font-size: 8px;
    font-weight: 400;
    color: #888;
}
.debt-badge {
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
</style>

<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 5%">Codigo</th>
                    <th style="text-align: center">Nombre Cliente</th>    
                    <th style="text-align: center">Detalles</th>
                    <th style="text-align: center">Monto Prestado</th>             
                    <th style="text-align: center">Deuda</th>      
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                @php
                    /* ── Cálculo del arco SVG (hecho en PHP, sin JS) ── */
                    $total     = $item->amountTotal > 0 ? $item->amountTotal : 1;
                    $paid      = max(0, $total - $item->debt);
                    $pctPaid   = round(($paid / $total) * 100);

                    /* Parámetros del círculo SVG */
                    $r         = 24;          /* radio */
                    $cx        = 30; $cy = 30;
                    $circ      = round(2 * M_PI * $r, 2);   /* 150.8 */
                    $dashPaid  = round($circ * $pctPaid / 100, 2);
                    $dashDebt  = round($circ - $dashPaid, 2);

                    /* Color del arco según porcentaje pagado */
                    if ($pctPaid >= 100)       $arcColor = '#28a745';
                    elseif ($pctPaid >= 60)    $arcColor = '#17a2b8';
                    elseif ($pctPaid >= 30)    $arcColor = '#ffc107';
                    else                       $arcColor = '#dc3545';
                @endphp
                <tr>
                    <td style="text-align: center; vertical-align: middle">
                        <small>{{ $item->code }}</small>
                        <br>
                        @if ($item->status=='entregado' && !auth()->user()->hasRole('cobrador'))
                            <a href="#" data-toggle="modal" data-target="#enableNotification-modal" onclick="loanNotification('{{ route('notificationAutomatic', ['loan' => $item->id]) }} ')" title="Notificación" class="btn btn-sm">
                                @if ($item->notification == 'si')
                                    <i class="fa-regular fa-bell" style="font-size: 20px; color: #000000"></i>
                                @else
                                    <i class="fa-regular fa-bell-slash" style="font-size: 20px; color: #000000"></i>
                                @endif
                            </a>
                        @endif
                    </td>
                    
                    <td style="vertical-align: middle">
                        <div style="display: flex; align-items: center;">
                            @php
                                $image = asset('images/default.jpg');
                                if($item->people->image){
                                    $image = asset('storage/'.str_replace('.', '-cropped.', $item->people->image));
                                }
                            @endphp
                            <img src="{{ $image }}" alt="Avatar" class="image-expandable" style="width: 45px; height: 45px; border-radius: 50%; margin-right: 10px; object-fit: cover; border: 1px solid #ddd;">
                            <div>
                                <small>CI: {{ $item->people->ci }}</small><br>
                                <span style="font-weight: 600; font-size: 13px;">{{strtoupper($item->people->first_name)}} {{strtoupper($item->people->last_name1)}} {{strtoupper($item->people->last_name2)}}</span>
                                @if ($item->manager)
                                    <br>
                                    <small class="text-primary" style="font-weight: bold"><i class="fa-solid fa-user-check"></i> {{ $item->manager->name }}</small>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td style="vertical-align: middle">
                        <div style="font-size: 14px;">
                            <div style="margin-bottom: 4px; text-align: center;">
                                <small class="text-muted">Tipo:
                                @if ($item->typeLoan == 'diario')
                                    Diario
                                @elseif ($item->typeLoan == 'diarioespecial')
                                    Diario Especial
                                @endif
                                </small>
                                @if ($item->payments_period_id)
                                    <span class="label label-default" style="background-color: {{ $item->payments_period->color }}; color: #fff;">{{ $item->payments_period->name }}</span>
                                @endif
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <span><i class="fa-regular fa-calendar" title="Fecha Solicitud"></i> <small class="text-muted">F. Solicitud:</small></span> <b>{{ date("d-m-Y", strtotime($item->date)) }}</b>
                            </div>
                            @if($item->dateDelivered)
                                <div style="display: flex; justify-content: space-between;">
                                    <span><i class="fa-solid fa-calendar-check text-success" title="Fecha Entrega"></i> <small class="text-muted">F. Entrega:</small></span> <b>{{ date("d-m-Y", strtotime($item->dateDelivered)) }}</b>
                                </div>
                            @endif
                        </div>
                    </td>

                    <td style="text-align: right; vertical-align: middle">
                        <div style="font-size: 14px;">
                            <div style="display: flex; justify-content: space-between"><small class="text-muted">Prestado:</small> <b>{{ number_format($item->amountLoan, 2) }}</b></div>
                            <div style="display: flex; justify-content: space-between"><small class="text-muted">Interés:</small> <b>{{ number_format($item->amountPorcentage, 2) }}</b></div>
                            <div style="border-top: 1px solid #eee; margin-top: 2px; padding-top: 2px; display: flex; justify-content: space-between">
                                <small class="text-muted">Total:</small> <b class="text-primary" style="font-size: 14px;">{{ number_format($item->amountTotal, 2) }}</b>
                            </div>
                        </div>
                    </td>

                    {{-- ── CELDA DE DEUDA con mini donut chart SVG ── --}}
                    <td style="text-align: center; vertical-align: middle; padding: 6px 4px;">
                        <div class="debt-widget">

                            {{-- Donut chart SVG puro, 100% PHP, sin JS ni librerías --}}
                            <div class="debt-svg-wrap">
                                <svg width="60" height="60" viewBox="0 0 60 60">
                                    {{-- Track gris (fondo) --}}
                                    <circle
                                        cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                        fill="none"
                                        stroke="#e9ecef"
                                        stroke-width="7"
                                    />
                                    {{-- Arco de lo PAGADO --}}
                                    @if($pctPaid > 0)
                                    <circle
                                        cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                        fill="none"
                                        stroke="{{ $arcColor }}"
                                        stroke-width="7"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $dashPaid }} {{ $dashDebt }}"
                                        stroke-dashoffset="0"
                                    />
                                    @endif
                                </svg>
                                {{-- Porcentaje al centro --}}
                                <div class="debt-center-label">
                                    <strong style="color: {{ $arcColor }}; font-size: 10px;">{{ $pctPaid }}%</strong>
                                    <span>pagado</span>
                                </div>
                            </div>

                            {{-- Estado de deuda --}}
                            @if ($item->debt != 0)
                                <span class="label label-danger debt-badge">Bs. {{ number_format($item->debt, 2) }}</span>
                            @endif
                            {{-- @if ($item->debt == 0)
                                <span class="label label-success">PAGADO</span>
                            @else
                                <span class="label label-danger debt-badge">Bs. {{ number_format($item->debt, 2) }}</span>
                            @endif --}}

                            {{-- Estado del préstamo --}}
                            {{-- <div>
                                @if ($item->status == 'pendiente')
                                    <span class="label label-danger">PENDIENTE</span>
                                @elseif ($item->status == 'verificado')
                                    <span class="label label-warning">VERIFICADO</span>
                                @elseif ($item->status == 'aprobado')
                                    <span class="label label-primary">APROBADO</span>
                                @elseif ($item->status == 'entregado')
                                    <span class="label label-success">ACTIVO</span>
                                @elseif ($item->status == 'rechazado')
                                    <span class="label label-danger">RECHAZADO</span>
                                @endif
                            </div> --}}
                        </div>
                    </td>

                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                        @if ($item->status == 'entregado' && $item->status != 'rechazado')
                            <a href="{{ route('loans-daily.money', ['loan' => $item->id]) }}" title="Pagar" class="btn btn-sm btn-success">
                                <i class="fa-solid fa-calendar-days"></i>
                            </a>
                        @endif

                        @if ($item->status == 'aprobado')
                            @if (auth()->user()->hasPermission('deliverMoney_loans'))
                                <a title="Entregar dinero al Beneficiario" class="btn btn-sm btn-success" onclick="deliverItem('{{ route('loans-money.deliver', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#deliver-modal" data-fechass="{{$item->date}}">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Entregar</span>
                                </a>
                            @endif
                        @endif

                        @if(!auth()->user()->hasRole('cobrador') && !auth()->user()->hasRole('cajeros'))
                            <a href="{{ route('loan-routeOld.index', ['loan' => $item->id]) }}" title="Rutas del Prestamo" class="btn btn-sm btn-dark">
                                <i class="fa-solid fa-route"></i>
                            </a>
                        @endif
                        
                        @if ($item->status=='verificado' && auth()->user()->hasPermission('successLoan_loans') && $item->status != 'rechazado')
                            <a title="Aprobar prestamo" class="btn btn-sm btn-dark" onclick="approveItem('{{ route('loans.approve', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#approve-modal">
                                <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Aprobar</span>
                            </a>
                        @endif

                        @if($item->status != 'rechazado')
                            <div class="btn-group" style="margin-right: 3px">
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                                    Mas <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    @if ($item->status == 'entregado' && $item->delivered == 'Si')
                                        <li><a href="{{ route('loans-list.transaction', ['loan'=>$item->id])}}" class="btn-transaction" data-toggle="modal" title="Imprimir Calendario"><i class="fa-solid fa-money-bill-transfer"></i> Transacciones</a></li> 
                                    @endif
                                    @if ($item->status != 'pendiente' && $item->status != 'verificado' && !auth()->user()->hasRole('cobrador'))
                                        <li><a href="{{ route('loans-print.calendar', ['loan'=>$item->id])}}" data-toggle="modal" target="_blank" title="Imprimir Calendario"><i class="fa-solid fa-print"></i> Imprimir Calendario</a></li>
                                        <li><a style="cursor: pointer" onclick="loan({{$item->id}})" data-toggle="modal" title="Imprimir Contrato"><i class="fa-solid fa-print"></i> Imprimir Contrato</a></li>
                                        <li><a style="cursor: pointer" onclick="handlePrintClick(this, '{{ setting('servidores.print') }}',{{ json_encode($item) }}, '{{ url('admin/loans/comprobante/print') }}')" data-toggle="modal" title="Imprimir Comprobante de Entrega de Prestamos"><i class="fa-solid fa-print"></i> Imprimir Comprobante Entrega</a></li>
                                        <li><a href="#" class="btn-payments-period" data-id="{{ $item->id }}" data-toggle="modal" data-target="#payments-period-modal" title="Cambiar periodo de pago"><i class="voyager-calendar"></i> Cambiar periodo de pago</a></li>
                                    @endif                      
                                </ul>
                            </div>
                        @endif

                        @if (auth()->user()->hasPermission('delete_loans'))
                            @if ($item->status != 'rechazado' && $item->status != 'entregado')
                                <button title="Rechazar" class="btn btn-sm btn-dark" onclick="declineItem('{{ route('loans.decline', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#decline-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm">Rechazar</span>
                                </button>
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('loans.destroy', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                    <i class="voyager-trash"></i>
                                </button>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td style="text-align: center" valign="top" colspan="6" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($data)>0)
            <p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $data->links() }}
        </nav>
    </div>
</div>

<script>
    var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('.page-link').click(function(e){
            e.preventDefault();
            let link = $(this).attr('href');
            if(link){
                page = link.split('=')[1];
                list(page);
            }
        });

        $('.btn-payments-period').click(function(){
            let id = $(this).data('id');
            $('#form-payments-period input[name="id"]').val(id);
        });
    });
</script>