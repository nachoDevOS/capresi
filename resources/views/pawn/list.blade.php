<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>N&deg;</th>
                    <th>Codigo</th>
                    <th>Persona</th>
                    <th>Actículos</th>
                    <th>Fecha</th>
                    <th>Detalles</th>
                    <th>Estado</th>
                    <th>Registrado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                    $cont = 1;
                @endphp
                @forelse ($data as $item)
                    @php
                        $subtotal = 0;
                        $subtotalDollar =0;
                    @endphp
                    <tr>
                        <td>{{ $cont }}</td>
                        <td>
                            {{ $item->code }} - {{ $item->codeManual?$item->codeManual:'S/N' }}

                        </td>
                        <td>
                            {{ $item->person->first_name }} {{ $item->person->last_name1 }} {{ $item->person->last_name2 }} <br>
                            <b>CI: {{ $item->person->ci ?? 'No definido' }}</b>
                        </td>
                        <td>
                            <ul>
                                @foreach ($item->details as $detail)
                                    @php
                                        $features_list = '';
                                        foreach ($detail->features_list as $feature) {
                                            if ($feature->value) {
                                                $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                            }
                                        }
                                    @endphp
                                    <li style="font-size: 12px">{{ floatval($detail->quantity) == intval($detail->quantity) ? intval($detail->quantity) : $detail->quantity }}
                                        {{ $detail->type->unit }} {{ $detail->type->name }} a {{ floatval($detail->price) == intval($detail->price) ? intval($detail->price) : $detail->price }}
                                        <span style="font-size: 10px">Bs.</span>
                                    </li>
                                    @php
                                        $subtotal += $detail->amountTotal;
                                        $subtotalDollar += $detail->dollarTotal;
                                    @endphp
                                @endforeach
                            </ul>
                        </td>
                        <td style="width: 150px">
                            <table style="width: 100%">
                                <tr>
                                    <td><b>Solicitud</b></td>
                                    <td class="text-right">{{ date('d', strtotime($item->date)).'/'.$meses[intval(date('m', strtotime($item->date)))].'/'.date('Y', strtotime($item->date)) }}</td>
                                </tr>
                                <tr>
                                    <td><b>Entrega</b></td>
                                    <td class="text-right">
                                        @if ($item->dateDelivered)
                                            {{ date('d', strtotime($item->dateDelivered)).'/'.$meses[intval(date('m', strtotime($item->dateDelivered)))].'/'.date('Y', strtotime($item->dateDelivered)) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Devolución</b></td>
                                    <td class="text-right">
                                        @if ($item->date_limit && ($item->status == 'entregado' || $item->status == 'recogida'))
                                            {{ date('d', strtotime($item->date_limit)).'/'.$meses[intval(date('m', strtotime($item->date_limit)))].'/'.date('Y', strtotime($item->date_limit)) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                        @php
                            $interest_rate = $subtotal * ($item->interest_rate /100);
                            $payment = $item->payments->sum('amount');
                            $debt = $subtotal + $interest_rate - $payment;
                        @endphp
                        <td style="width: 150px">
                            <table style="width: 100%">
                                <tr>
                                    <td colspan="2" class="text-center"><i class="fa-solid fa-dollar-sign"></i> {{ $subtotalDollar }}<span style="font-size: 10px"></span></td>
                                </tr>
                                <tr>
                                    <td><b>Prestamos</b></td>
                                    <td class="text-right">{{ $subtotal }}<span style="font-size: 10px">Bs.</span></td>
                                </tr>
                                <tr>
                                    <td><b>Interes</b></td>
                                    <td class="text-right">{{ $interest_rate }}<span style="font-size: 10px">Bs.</span></td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center">
                            @php
                                switch ($item->status) {
                                    case 'pendiente':
                                        $label = 'warning';
                                        break;
                                    case 'pagado':
                                        $label = 'primary';
                                        break;
                                    case 'aprobado':
                                        $label = 'primary';
                                        break;
                                    case 'entregado':
                                        $label = 'success';
                                        break;
                                    default:
                                        $label = 'danger';
                                        break;
                                }
                            @endphp
                            @if ($item->deleted_at==null)
                                <label class="label label-{{ $label }}">{{ $item->status }}</label>
                            @else
                                <label class="label label-danger">Eliminado</label>
                            @endif

                            @if ($item->inventory == 1)
                                <br>
                                <label class="label label-danger"><i class="fa-solid fa-truck-ramp-box"></i> En Inventario</label>
                            @endif
                        </td>
                        <td>
                            {{ $item->user ? $item->user->name : '' }} <br>
                            {{ date('d/', strtotime($item->created_at)).$meses[intval(date('m', strtotime($item->created_at)))].date('/Y H:i', strtotime($item->created_at)) }} <br>
                            <small>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            @if (auth()->user()->hasRole('admin') && $item->status == 'aprobado' && $item->inventory == 0)
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" style="margin-right: 5px">
                                        DEV <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu" style="left: -90px !important">
                                        <li><a href="{{ route('pawns.deliveredMoney', ['id'=>$item->id]) }}" title="Imprimir" target="_blank">Comprobante</a></li>
                                    </ul>
                                </div>
                            @endif

                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" style="margin-right: 5px">
                                    Más <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="left: -90px !important">
                                    @if ($item->dateDelivered)
                                        <li><a href="{{ route('pawn-voucher.print', $item->id) }}" title="Imprimir" target="_blank">Comprobante</a></li>
                                        @if ($item->inventory == 0)
                                            <li><a onclick="codeItem('{{ route('pawn.code', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#code-modal" title="Agregar Codigo">Codigo Manual</a></li>
                                        @endif
                                        <li><a href="{{ route('pawn.print', $item->id) }}" title="Imprimir" target="_blank">Contrato General</a></li>
                                        <li><a href="{{ route('pawn-vehicular.print', $item->id) }}" title="Imprimir" target="_blank">Contrato Vehicular</a></li>
                                    @endif
                                </ul>
                            </div>

                            @if (auth()->user()->hasPermission('read_pawn'))
                                <a href="{{ route('pawn.show', $item->id) }}" title="Ver" class="btn btn-sm btn-warning view">
                                    <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                                </a>
                            @endif
                            

                            @if ($item->status == 'aprobado' && $item->deleted_at == null && auth()->user()->hasPermission('deliverMoney_pawn'))
                                <a title="Entregar dinero al Beneficiario" class="btn btn-sm btn-success" onclick="deliverItem('{{ route('pawn-money.deliver', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#deliver-modal" data-fechass="{{$item->date}}">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Entregar</span>
                                </a>
                            @endif
                            {{-- @if (($item->status == "pendiente" || $item->status == "por validar") && $item->status != 'rechazado' && $item->deleted_at ==null) --}}
                            @if (($item->status == "pendiente" || $item->status == "por validar") && auth()->user()->hasPermission('successPawn_pawn') && $item->status != 'rechazado' && $item->deleted_at ==null)
                                <a title="Aprobar prestamo" class="btn btn-sm btn-dark" onclick="successItem('{{ route('pawn.success', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#success-modal">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Aprobar</span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_pawn') && ($item->status == "pendiente" || $item->status == "por validar") && $item->deleted_at == null)
                                <button title="Rechazar" class="btn btn-sm btn-dark" onclick="rechazarItem('{{ route('pawn.rechazar', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#rechazar-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm">Rechazar</span>
                                </button>
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('pawn.destroy', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Eliminar</span>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @php
                        $cont++;
                    @endphp
                @empty
                    <tr class="odd">
                        <td valign="top" style="text-align: center" colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
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

<style>
    #dataTable ul {
        padding-left: 20px
    }
    .bread-actions .btn{
        border: 0px
    }
    .mce-edit-area{
        max-height: 250px !important;
        overflow-y: auto;
    }
</style>

<script>
    moment.locale('es');
    var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();

        $('.btn-payment').click(function(){
            let id = $(this).data('id');
            let debt = $(this).data('debt');
            $('#form-payment input[name="id"]').val(id);
            $('#form-payment input[name="amount"]').val(debt);
            $('#form-payment input[name="amount"]').attr('max', debt);
        });

        $('.page-link').click(function(e){
            e.preventDefault();
            let link = $(this).attr('href');
            if(link){
                page = link.split('=')[1];
                list(page);
            }
        });
    });
</script>