<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutillageController extends Controller
{
    public function debut(Request $request){

        $validator = Validator::make($request->all(), [
            "code_machine" => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
            "code_outillage"    => "required|exists:T_OUTILLAGE,CODE_OUTILLAGE",
        ]);
    
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()]);
        }


        $now = Carbon::now();


        $machine = Machine::find($request->code_machine);

        if($machine->CODE_OUTILLAGE_EC != null){
            return response()->json(["errors" => ['machine' => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]]);
        } 


        $machine->update([
            'CODE_OUTILLAGE_EC' => $request->code_outillage,
            'DH_MODIF' => $now->format('Y-d-m H:i:s.v'),
        ]);


        

        $machine_event = MachineEvent::create([
            'CODE_SOCIETE' => 100,
            'CODE_MACHINE' => $request->code_machine,
            'CODE_PERS' => $request->code_personnel,
            'CODE_OF' => 0,
            'CODE_OP' => 0,
            'DH_DEBUT' => $now->format('Y-d-m H:i:s.v'),
            'DATE_REF' => $now->format('Y-d-m H:i:s.v'),
            'REF_ARTICLE' => null,
            'NO_MOIS' => $now->format('Ym'),
            'NO_SEMAINE' => $now->year . $now->weekOfYear,
            'COEFFICIENT' => 1,
            'REPARTITION' => 1.000000,
            'CODE_ACTIVITE' => 'REGL',
            'CODE_ALEA' => null,
            'QTE_COMPTE' => 0.000000,
            'QTE_BONNE' => 0.000000,
            'QTE_REBUT' => 0.000000,
            'CODE_REBUT' => null,
            'QTE_AUTRE' => 0.000000,
            'TPS_OUVERTURE' => 0.000000,
            'ANOMALIE' => 'N',
            'CODE_LIBRE' => 'MONTAGE_OUTIL',
            'DH_TRANSFERT' => null,
            'CODE_TYPE' => null,
            'PR_TPS_REGL_ALLOUE' => 0.000000,
            'PR_QTE_BONNE_LISSE' => 0.000000,
            'PR_PRORATA_MA' => 0.000000,
            'PR_PRORATA_MO' => 0.000000,
            'PR_CALCUL_OK' => 'N',
            'CODE_OUTILLAGE' => 'LAM_RAIN',
            'TAUX_HORAIRE' => 0.00,
            'CODE_ENREG' => 0,
            'DH_CREATION' => $now->format('Y-d-m H:i:s.v'),
            'DH_MODIF' => $now->format('Y-m-d H:i:s'),
            'CODE_UTILISATEUR' => 'SYSTEM',
        ]);

        if ($machine_event) {
            return response()->json([
                "message" => "Machine event created successfully."
            ]);
        }
        
    }


    public function fin(Request $request){

        $validator = Validator::make($request->all(), [
            "code_machine" => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()]);
        }

        $now = Carbon::now();


        $machine = Machine::find($request->code_machine);

        if($machine->CODE_OUTILLAGE_EC == null){
            return response()->json(["errors" => ['machine' => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]]);
        }
; 
        $machine->update([
            'CODE_OUTILLAGE_EC' => null,
            'DH_MODIF' => $now->format('Y-d-m H:i:s.v'),
        ]);


        $machine_event = MachineEvent::create([
            'CODE_SOCIETE' => 100,
            'CODE_MACHINE' => $request->code_machine,
            'CODE_PERS' => $request->code_personnel,
            'CODE_OF' => 0,
            'CODE_OP' => 0,
            'DH_DEBUT' => $now->format('Y-d-m H:i:s.v'),
            'DATE_REF' => $now->format('Y-d-m H:i:s.v'),
            'REF_ARTICLE' => null,
            'NO_MOIS' => $now->format('Ym'),
            'NO_SEMAINE' => $now->year . $now->weekOfYear,
            'COEFFICIENT' => 1,
            'REPARTITION' => 1.000000,
            'CODE_ACTIVITE' => 'REGL',
            'CODE_ALEA' => null,
            'QTE_COMPTE' => 0.000000,
            'QTE_BONNE' => 0.000000,
            'QTE_REBUT' => 0.000000,
            'CODE_REBUT' => null,
            'QTE_AUTRE' => 0.000000,
            'TPS_OUVERTURE' => 0.000000,
            'ANOMALIE' => 'N',
            'CODE_LIBRE' => 'DEMONTAGE_OUTIL',
            'DH_TRANSFERT' => null,
            'CODE_TYPE' => null,
            'PR_TPS_REGL_ALLOUE' => 0.000000,
            'PR_QTE_BONNE_LISSE' => 0.000000,
            'PR_PRORATA_MA' => 0.000000,
            'PR_PRORATA_MO' => 0.000000,
            'PR_CALCUL_OK' => 'N',
            'CODE_OUTILLAGE' => 'LAM_RAIN',
            'TAUX_HORAIRE' => 0.00,
            'CODE_ENREG' => 0,
            'DH_CREATION' => $now->format('Y-d-m H:i:s.v'),
            'DH_MODIF' => $now->format('Y-m-d H:i:s'),
            'CODE_UTILISATEUR' => 'SYSTEM',
        ]);

        if ($machine_event) {
            return response()->json([
                "message" => "Machine event controller created successfully."
            ]);
        }
    }





}
