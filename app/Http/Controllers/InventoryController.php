<?php

namespace App\Http\Controllers;

use App\Models\InventoriesFeature;
use App\Models\Inventory;
use App\Models\PawnRegister;
use App\Models\PawnRegisterDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\ItemType;
use App\Models\People;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_inventories');
        return view('inventories.browse');
    }

    public function list(){
        $this->custom_authorize('browse_pawn');
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;
        
        $data = Inventory::with(['features', 'item', 'register'])
                    ->where(function($query) use ($search){
                        if($search){
                            $query
                            ->OrWhereHas('register', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('item', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('features', function($query) use($search){
                                $query->whereRaw($search ? 'value like "%'.$search.'%"' : 1);
                            })
                            // ->OrWhereHas('details.type.category', function($query) use($search){
                            //     $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            // })
                            ->OrWhereRaw($search ? "codeManual like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "code like '%$search%'" : 1);

                        }
                    })
                    ->whereRaw($status ? " status = '$status'" : 1)
                    ->orderBy('id', 'desc')
                    ->paginate($paginate);
        return view('inventories.list', compact('data'));
    }

    public function create()
    {
        $this->custom_authorize('add_inventories');
        return view('inventories.edit-add');
    }


    //Para poder pasar de prendario a inventario
    public function storePawn(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pawn = PawnRegister::with(['details'=>function($q){
                    $q->where('deleted_at', null);
                }, 'details.features_list'])
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            foreach ($request->price as $id => $valor) {
                $detailFeature = PawnRegisterDetail::with(['features_list'])
                    ->where('id', $id)
                    ->where('deleted_at', null)
                    ->first();
                // return $detailFeature;
                $totaAux= $detailFeature->quantity * $valor;

                $inventory = Inventory::create([
                    'pawnRegisterDetail_id'=>$detailFeature->id,
                    'itemType_id'=>$detailFeature->item_type_id,
                    'price'=>$valor,
                    'quantity'=>$detailFeature->quantity,
                    'stock'=>1,
                    'amountTotal'=>$totaAux,
                    'dollarTotal'=>$totaAux/setting('configuracion.dollar'),
                    'dollarPrice'=>setting('configuracion.dollar'),

                    // 'image' => isset($request->image[$i]) ? $this->store_image($request->image[$i], 'pawn_register', 1000) : null,

                    'registerUser_id'=>Auth::user()->id,
                    'registerRole'=>Auth::user()->role->name,
                    'description'=>$request->description,     
                    
                    'typeRegister'=>'Prendario'
                ]);

                foreach ($detailFeature->features_list as $item) {
                    InventoriesFeature::create([
                        'title'=>$item->title,
                        'value'=>$item->value,
                        'inventory_id'=>$inventory->id
                    ]);
                }
                $detailFeature->update([
                    'inventory'=>1
                ]);
                $inventory->update(['code'=>'PROD-'.str_pad($inventory->id, 6, "0", STR_PAD_LEFT)]);

            }
            $pawn->update([
                'inventory'=>1
            ]);
            DB::commit();
            return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success']);         
        } catch (\Throwable $th) {
            DB::rollBack();
            // return 0;
            return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);

        }
    }

    //Para registrar inventario de forma independiente
    public function store(Request $request)
    {
        // return $request;
        DB::beginTransaction();
        try {

            for ($i=0; $i < count($request->item_type_id); $i++) { 
                $inventory = Inventory::create([
                    // 'pawn_register_id' => $pawn_register->id,
                    'itemType_id' => $request->item_type_id[$i],
                    'stock'=>1,
                    'price' => $request->price[$i],
                    'quantity' => $request->quantity[$i] - $request->quantity_discount[$i] ?? 0,
                    'amountTotal' => $request->subtotal[$i],
                    'dollarTotal'=> $request->subtotal[$i]/setting('configuracion.dollar'),
                    'image' => isset($request->image[$i]) ? $this->store_image($request->image[$i], 'inventories', 1000) : null,
                    'registerUser_id'=>Auth::user()->id,
                    'registerRole'=>Auth::user()->role->name,
                    'description'=>$request->observations,
                    'dollarPrice'=>setting('configuracion.dollar'),

                    'typeRegister'=>'Manual'
                ]);
                // return $inventory;

                // Registrar características de cada item
                if (isset($request->{'features_'.$i})) {
                    for ($j=0; $j < count($request->{'features_'.$i}); $j++) { 
                        InventoriesFeature::create([
                            'inventory_id' => $inventory->id,
                            'title' => $request->{'features_'.$i}[$j] ,
                            'value' => ucfirst($request->{'features_value_'.$i}[$j])
                        ]);
                    }
                }
                $inventory->update([
                    'code'=>'PROD-'.str_pad($inventory->id, 6, "0", STR_PAD_LEFT)
                ]);

            }
            DB::commit();
            return redirect()->route('inventories.index')->with(['message' => 'Actualizado exitosamente', 'alert-type' => 'success']);         
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('inventories.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //Para actualizar los precios de los item del inventario
    public function updatePrice(Request $request, $id)
    {
        // return $id;
        DB::beginTransaction();
        try {
            Inventory::where('deleted_at', null)->where('id', $id)->update([
                'price'=>$request->price,
                'quantity'=>$request->quantity,
                'amountTotal'=>$request->amountTotal,
                'dollarTotal'=>$request->amountTotal/(setting('configuracion.dollar')?setting('configuracion.dollar'):7),
                'dollarPrice'=>setting('configuracion.dollar'),
            ]);
            DB::commit();
            return redirect()->route('inventories.index')->with(['message' => 'Actualizado exitosamente', 'alert-type' => 'success']);         
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('inventories.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function listItem(){
        $search = request('q');
        // Log::info('Datos recibidos en el request:', $search);

        $data = Inventory::with(['item', 'features'])
            ->Where(function($query) use ($search){
                if($search){
                    $query->OrwhereHas('item', function($query) use($search){
                        $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                    })
                    ->OrWhereRaw($search ? "id like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "code like '%$search%'" : 1);
                }
            })
            ->where('stock', '>', 0)
            ->get();        
        return response()->json($data);
    }



}
