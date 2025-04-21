<?php

namespace App\Http\Controllers;

use App\Models\Nomenclature;
use App\Models\OrderFabrication;
use Illuminate\Http\Request;

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
}
