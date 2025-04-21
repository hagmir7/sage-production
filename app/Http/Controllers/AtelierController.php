<?php

namespace App\Http\Controllers;

use App\Models\Atelier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtelierController extends Controller
{
    public function index()
    {
        return Atelier::orderByDesc("DH_CREATION")->get();
    }

    public function show($id)
    {
        return Atelier::with(['machines' => function($query) {
            $query->select('CODE_ATELIER', 'LIBELLE_MACHINE', 'CODE_MACHINE', 'CODE_ZONE'); 
        }])->find($id);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'CODE_SOCIETE' => 'required|string|max:3',
            'CODE_ATELIER' => 'required|string|max:10|unique:ateliers',
            'LIBELLE_ATELIER' => 'required|string|max:50',
            'REMARQUE' => 'nullable|string|max:255',
            // 'CODE_ENREG' => 'required|string|max:10'
        ]);

        $atelier = Atelier::create([
            ...$validated,
            'DH_CREATION' => Carbon::now()->format('Y-m-d H:i:s'),
            'CODE_ENREG' => 0
        ]);

        return response()->json($atelier, 201);
    }

    public function update(Request $request, $code_atelier)
    {
        // Find by CODE_ATELIER instead of id
        DB::statement("SET DATEFORMAT mdy");
        DB::statement("SET LANGUAGE ENGLISH;"); 


        $atelier = Atelier::where('CODE_ATELIER', $code_atelier)->first();
        
        if(!$atelier){
            return response()->json(["error" => "Atelier does not exist"], 404);
        }
    
        $validated = $request->validate([
            'CODE_SOCIETE' => 'sometimes|string|max:3',
            'CODE_ATELIER' => 'sometimes|string|max:10|unique:T_ATELIER,CODE_ATELIER,'.$code_atelier.',CODE_ATELIER',
            'LIBELLE_ATELIER' => 'sometimes|string|max:50',
            'REMARQUE' => 'nullable|string|max:255',
        ]);
    
        $atelier->update($validated);
    
        return response()->json($atelier);
    }
}
