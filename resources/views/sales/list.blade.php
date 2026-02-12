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
                            {{ $item->code }} <br>
                            <small>F. Venta: 
                            {{ date('d/', strtotime($item->saleDate)).$meses[intval(date('m', strtotime($item->saleDate)))].date('/Y h:i:s a', strtotime($item->saleDate)) }}
                            </small>
                            
                        </td>
                        <td>
                            @foreach ($item->saleDetails as $detail)
                                <table style="width: 100%">
                                    @php
                                        $image = asset('images/default.jpg');
                                        if($detail->inventory->image){
                                            $image = asset('storage/'.str_replace('.', '-cropped.', $detail->inventory->image));
                                        }                                                                                                                       
                                    @endphp
                                    <tr>
                                        <td style="width: 10%;"><img src="{{ $image }}" alt="{{ $detail->inventory->image }} " style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px"></td>
                                        <td>
                                            <ul>
                                                <li style="font-size: 15px">
                                                    <small>
                                                        {{ floatval($detail->inventory->quantity) == intval($detail->inventory->quantity) ? intval($detail->inventory->quantity) : $detail->inventory->quantity }}
                                                    {{ $detail->inventory->item->unit }} {{ $detail->inventory->item->name }} a {{ floatval($detail->price) == intval($detail->price) ? intval($detail->price) : $detail->price }}
                                                    Bs.
                                                    </small>
                                                </li>
                                            </ul>
                                           
                                            @php
                                                $features_list = '';
                                                foreach ($detail->inventory->features as $feature) {
                                                    if ($feature->value) {
                                                        $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                    }
                                                }
                                            @endphp
                                            {!! $features_list !!}
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                        <td style="width: 17%">
                            <table style="width: 100%">
                                <tr>
                                    <td style="width: 45%"><b>Monto Total</b></td>
                                    <td class="text-right">
                                        Bs {{ number_format($item->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 45%"><b>Monto Descuento</b></td>
                                    <td class="text-right">
                                        Bs {{ number_format($item->discount, 2, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 45%"><b>Total a Pagar</b></td>
                                    <td class="text-right">
                                        <small>Bs {{ number_format($item->amountTotal, 2, ',', '.') }}</small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center">
                            @if ($item->debt != 0)
                                <label class="label label-warning">En Pago</label>  <br>
                                <small>Deuda: Bs {{ number_format($item->debt, 2, ',', '.') }}</small>
                            @else    
                                <label class="label label-success">Pagado</label>         
                            @endif                   
                        </td>
                        <td style="text-align: center">
                            {{$item->register->name}} <br>
                            {{ date('d/', strtotime($item->created_at)).$meses[intval(date('m', strtotime($item->created_at)))].date('/Y h:i:s a', strtotime($item->created_at)) }} <br>
                            <small>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('sales.prinf', ['id' => $item->id]) }}" title="Imprimir" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            @if (auth()->user()->hasPermission('read_sales'))
                                <a href="{{ route('sales.show', ['sale' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                    <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                                </a>
                            @endif
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
        // $('[data-toggle="popover"]').popover();

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