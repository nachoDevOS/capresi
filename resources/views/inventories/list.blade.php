<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Actículos</th>
                    <th>Detalles</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Registrado por</th>
                    <th style="width: 15%; text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                @endphp
                @forelse ($data as $item)
                    @php
                        $subtotal = 0;
                        $subtotalDollar =0;
                    @endphp
                    <tr>
                        <td>
                            {{ $item->code }}
                             {{-- - {{ $item->codeManual?$item->codeManual:'S/N' }} --}}
                            
                        </td>
                        <td>                            
                            <table style="width: 100%">
                                @php
                                    $image = asset('images/default.jpg');
                                    if($item->image){
                                        $image = asset('storage/'.str_replace('.', '-cropped.', $item->image));
                                    }                                                                                                                       
                                @endphp
                                <tr>
                                    <td style="width: 10%;"><img src="{{ $image }}" alt="{{ $item->image }} " style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px"></td>
                                    <td>
                                        <ul>
                                            <li style="font-size: 15px">
                                                <small>
                                                    {{ floatval($item->quantity) == intval($item->quantity) ? intval($item->quantity) : $item->quantity }}
                                                {{ $item->item->unit }} {{ $item->item->name }} a {{ floatval($item->price) == intval($item->price) ? intval($item->price) : $item->price }}
                                                Bs.
                                                </small>
                                            </li>
                                        </ul>
                                        @php
                                            $features_list = '';
                                            foreach ($item->features as $feature) {
                                                if ($feature->value) {
                                                    $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                }
                                            }
                                        @endphp
                                        {!! $features_list !!}
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                        <td style="width: 17%">
                            <table style="width: 100%">
                                <tr>
                                    <td style="width: 45%"><b>Tipo Ingreso</b></td>
                                    <td class="text-right">
                                        @if ($item->pawnRegisterDetail_id)
                                            <small style="color: #198754">Empeño</small>
                                        @else
                                            <small style="color: #0d6efd">Manual</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td rowspan="2" style="width: 45%"><b>Precio Venta</b></td>
                                    <td class="text-right">
                                        <small>Bs {{ number_format($item->amountTotal, 2, ',', '.') }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-right">
                                        <small>$ {{ number_format($item->dollarTotal, 2, ',', '.') }}</small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center">
                            @php
                                switch ($item->status) {
                                    case 'disponible':
                                        $label = 'success';
                                        break;
                                    case 'vendido':
                                        $label = 'danger';
                                        break;
                                }
                            @endphp
                            <label class="label label-{{ $label }}">{{ $item->status }}</label> <br>
                            @if ($item->stock == 0)
                                <small>Sin Stock</small>
                            @else
                                <small>Stock: {{$item->stock}}</small>                                
                            @endif                           
                        </td>
                        <td style="text-align: center">
                            {{$item->register->name}} <br>
                            {{ date('d/', strtotime($item->created_at)).$meses[intval(date('m', strtotime($item->created_at)))].date('/Y H:i', strtotime($item->created_at)) }} <br>
                            <small>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            @if (auth()->user()->hasPermission('edit_inventories') && $item->status == 'disponible')
                                <a title="Editar Precio" class="btn btn-sm btn-primary" onclick="priceItem('{{ route('inventories-price.update', ['id' => $item->id])}}', {{$item->quantity}}, {{$item->price}})" data-toggle="modal" data-target="#price-modal">
                                    <i class="fa-solid fa-pen-to-square"></i> <span class="hidden-xs hidden-sm"> Precio</span>
                                </a>
                            @endif
                            {{-- @if (auth()->user()->hasPermission('delete_inventories') && $item->status == "disponible"  && $item->deleted_at == null)
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('pawn.destroy', ['pawn' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Eliminar</span>
                                </button>
                            @endif --}}
                        </td>
                    </tr>
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