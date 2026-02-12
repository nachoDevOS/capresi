<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\ItemType;
use App\Models\ItemCategory;

class ItemTypesController extends Controller
{
    public function store(Request $request){
        try {
            $newType = ItemType::create([
                'item_category_id' => is_numeric($request->item_category_id) ? $request->item_category_id : ItemCategory::create(['name' => $request->item_category_id])->id,
                'name' => $request->name,
                'unit' => $request->unit,
                'price' => $request->price,
                'max_price' => $request->max_price,
                'description' => $request->description
            ]);
            // Obtener el nuevo tipo con la relación de la categoría (sino van sin la relación)
            $type = ItemType::with(['category.features'])->where('id', $newType->id)->first();
            return response()->json(['success' => 1, 'type' => $type]);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json(['error' => 1]);
        }
    }
}
