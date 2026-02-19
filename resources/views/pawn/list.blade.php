<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">Codigo</th>
                    <th style="text-align: center">Nombre Cliente</th>
                    <th style="text-align: center">Detalles</th>
                    <th style="text-align: center">Monto Prestado</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    @php
                        $subtotal = 0;
                        $subtotalDollar =0;
                        foreach ($item->details as $detail) {
                            $subtotal += $detail->amountTotal;
                            $subtotalDollar += $detail->dollarTotal;
                        }
                        $interest_rate = $subtotal * ($item->interest_rate /100);
                        $total = $subtotal + $interest_rate;
                    @endphp
                    <tr>
                        <td style="vertical-align: middle">
                            <small>
                                {{ $item->code }}
                                @if($item->codeManual)
                                    <br>
                                    <span class="text-muted">{{ $item->codeManual }}</span>
                                @endif
                            </small>
                        </td>
                        <td style="vertical-align: middle">
                            <div style="display: flex; align-items: center;">
                                @php
                                    $image = asset('images/default.jpg');
                                    if($item->person->image){
                                        $image = asset('storage/'.str_replace('.', '-cropped.', $item->person->image));
                                    }
                                @endphp
                                <img src="{{ $image }}" alt="Avatar" class="image-expandable" style="width: 45px; height: 45px; border-radius: 50%; margin-right: 10px; object-fit: cover; border: 1px solid #ddd;">
                                <div>
                                    <small>CI: {{ $item->person->ci }}</small><br>
                                    <span style="font-weight: 600; font-size: 13px;">{{strtoupper($item->person->first_name)}} {{strtoupper($item->person->last_name1)}} {{strtoupper($item->person->last_name2)}}</span>
                                    @if ($item->user)
                                        <br>
                                        <small class="text-primary" style="font-weight: bold"><i class="fa-solid fa-user-check"></i> {{ $item->user->name }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align: middle">
                            <div style="font-size: 12px;">
                                <div style="margin-bottom: 4px;">
                                    <b>Artículos:</b><br>
                                    @foreach ($item->details as $detail)
                                        <span>
                                            {{ floatval($detail->quantity) == intval($detail->quantity) ? intval($detail->quantity) : $detail->quantity }}
                                            {{ $detail->type->unit }} {{ $detail->type->name }}
                                        </span><br>
                                    @endforeach
                                </div>
                                <div style="margin-bottom: 2px;">
                                    <i class="fa-regular fa-calendar" title="Fecha Solicitud"></i> <span class="text-muted">Sol:</span> {{ date("d-m-Y", strtotime($item->date)) }}
                                </div>
                                @if($item->dateDelivered)
                                    <div>
                                        <i class="fa-solid fa-calendar-check text-success" title="Fecha Entrega"></i> <span class="text-muted">Ent:</span> {{ date("d-m-Y", strtotime($item->dateDelivered)) }}
                                    </div>
                                @endif
                                @if ($item->date_limit && ($item->status == 'entregado' || $item->status == 'recogida'))
                                    <div>
                                        <i class="fa-solid fa-calendar-xmark text-danger" title="Fecha Devolución"></i> <span class="text-muted">Dev:</span> {{ date("d-m-Y", strtotime($item->date_limit)) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; vertical-align: middle">
                            <div style="font-size: 12px;">
                                @if($subtotalDollar > 0)
                                    <div><small class="text-muted">USD:</small> <b>{{ number_format($subtotalDollar, 2) }}</b></div>
                                @endif
                                <div><small class="text-muted">Prestado:</small> <b>{{ number_format($subtotal, 2) }}</b></div>
                                <div><small class="text-muted">Interés:</small> <b>{{ number_format($interest_rate, 2) }}</b></div>
                                <div style="border-top: 1px solid #eee; margin-top: 2px; padding-top: 2px;">
                                    <small class="text-muted">Total:</small> <b class="text-primary" style="font-size: 14px;">{{ number_format($total, 2) }}</b>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle">
                            <div style="margin-bottom: 5px">
                                @php
                                    switch ($item->status) {
                                        case 'pendiente': $label = 'warning'; break;
                                        case 'pagado': $label = 'primary'; break;
                                        case 'aprobado': $label = 'primary'; break;
                                        case 'entregado': $label = 'success'; break;
                                        case 'rechazado': $label = 'danger'; break;
                                        default: $label = 'default'; break;
                                    }
                                @endphp
                                @if ($item->deleted_at==null)
                                    <span class="label label-{{ $label }}">{{ strtoupper($item->status) }}</span>
                                @else
                                    <span class="label label-danger">ELIMINADO</span>
                                @endif
                            </div>
                            @if ($item->inventory == 1)
                                <div>
                                    <span class="label label-danger"><i class="fa-solid fa-truck-ramp-box"></i> EN INVENTARIO</span>
                                </div>
                            @endif
                        </td>
                        <td style="width: 15%" class="no-sort no-click bread-actions text-right">
                            
                            @if ($item->status == 'aprobado' && $item->deleted_at == null && auth()->user()->hasPermission('deliverMoney_pawn'))
                                <a title="Entregar dinero al Beneficiario" class="btn btn-sm btn-success" onclick="deliverItem('{{ route('pawn-money.deliver', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#deliver-modal" data-fechass="{{$item->date}}">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Entregar</span>
                                </a>
                            @endif

                            <div class="btn-group" style="margin-right: 3px">
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                                    Mas <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    @if ($item->dateDelivered)
                                        <li><a href="{{ route('pawn-voucher.print', $item->id) }}" title="Imprimir" target="_blank"><i class="fa-solid fa-print"></i> Comprobante</a></li>
                                        @if ($item->inventory == 0)
                                            <li><a onclick="codeItem('{{ route('pawn.code', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#code-modal" title="Agregar Codigo"><i class="fa-solid fa-barcode"></i> Codigo Manual</a></li>
                                        @endif
                                        <li><a href="{{ route('pawn.print', $item->id) }}" title="Imprimir" target="_blank"><i class="fa-solid fa-file-contract"></i> Contrato General</a></li>
                                        <li><a href="{{ route('pawn-vehicular.print', $item->id) }}" title="Imprimir" target="_blank"><i class="fa-solid fa-car"></i> Contrato Vehicular</a></li>
                                    @endif
                                    @if (auth()->user()->hasRole('admin') && $item->status == 'aprobado' && $item->inventory == 0)
                                        <li role="separator" class="divider"></li>
                                        <li><a href="{{ route('pawns.deliveredMoney', ['id'=>$item->id]) }}" title="Imprimir" target="_blank"><i class="fa-solid fa-file-invoice-dollar"></i> Comprobante DEV</a></li>
                                    @endif
                                </ul>
                            </div>

                            @if (auth()->user()->hasPermission('read_pawn'))
                                <a href="{{ route('pawn.show', $item->id) }}" title="Ver" class="btn btn-sm btn-warning">
                                    <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                                </a>
                            @endif
                            

                            @if (($item->status == "pendiente" || $item->status == "por validar") && auth()->user()->hasPermission('successPawn_pawn') && $item->status != 'rechazado' && $item->deleted_at ==null)
                                <a title="Aprobar prestamo" class="btn btn-sm btn-dark" onclick="successItem('{{ route('pawn.success', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#success-modal">
                                    <i class="fa-solid fa-check"></i><span class="hidden-xs hidden-sm"> Aprobar</span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_pawn') && ($item->status == "pendiente" || $item->status == "por validar") && $item->deleted_at == null)
                                <button title="Rechazar" class="btn btn-sm btn-dark" onclick="rechazarItem('{{ route('pawn.rechazar', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#rechazar-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm"></span>
                                </button>
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('pawn.destroy', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                </button>
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
    });
</script>