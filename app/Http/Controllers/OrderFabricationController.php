<?php

namespace App\Http\Controllers;

use App\Models\Nomenclature;
use App\Models\OrderFabrication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderFabricationController extends Controller
{
    public function index()
    {
        return OrderFabrication::orderByDesc('DH_CREATION')->paginate(20);
    }


    public function show($id)
    {
        $order = OrderFabrication::find($id);
        return $order;
    }

    public function nomenclature($id)
    {
        $order = OrderFabrication::find($id);
        return $order->nomenclatures;
    }

    public function filter(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'status' => "required"
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => ["status" => "Status is required"]
            ]);
        }
        $order = OrderFabrication::where("ETAT_OF", $request->status)
            ->orderByDesc('DH_CREATION')
            ->get();
        return $order;
    }
}
