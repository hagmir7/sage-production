<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineEvent;
use App\Models\MachineEventControle;
use App\Models\Operation;
use App\Models\OrderFabrication;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ReglageController extends Controller
{

    public function debut(Request $request)
    {
        // ✅ Step 1: Validate input with existence checks
        $request->validate([
            "code_machine"    => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
            "code_of"         => "required|exists:T_ORDREFAB,CODE_OF"
        ]);
    
        // ✅ Step 2: Retrieve models
        $machine = Machine::find($request->code_machine);
        $personnel = Personnel::find($request->code_personnel);
        $of = OrderFabrication::find($request->code_of);
    
        // ✅ Step 3: Check if a MachineEventControle already exists
        $machineEventExists = MachineEventControle::where("CODE_MACHINE", $request->code_machine)->exists();
    
        if (!$machine || $machineEventExists) {
            return response()->json([
                "error" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."
            ], 400);
        }
    
        // ✅ Step 4: Create MachineEventControle
        $now = Carbon::now();
    
        $machine_event_controller = MachineEventControle::create([
            'CODE_SOCIETE'         => '100',
            'CODE_MACHINE'         => $request->code_machine,
            'CODE_PERS'            => $request->code_personnel,
            'CODE_OF'              => $request->code_of,
            'CODE_OP'              => '10',
            'DH_DEBUT'             => $now->format('Y-m-d H:i:s'),
            'DATE_REF'             => $now->format('Y-m-d H:i:s'),
            'REF_ARTICLE'          => $of->REF_ARTICLE,
            'NO_MOIS'              => $now->format('Ym'),
            'NO_SEMAINE'           => $now->year . $now->weekOfYear,
            'COEFFICIENT'          => '1',
            'REPARTITION'          => '1.000000',
            'CODE_ACTIVITE'        => 'REGL',
            'CODE_ALEA'            => null,
            'QTE_COMPTE'           => '0.000000',
            'QTE_BONNE'            => '0.000000',
            'QTE_REBUT'            => '0.000000',
            'CODE_REBUT'           => null,
            'QTE_AUTRE'            => '0.000000',
            'CODE_ACTIVITE_PREC'   => 'REGL',
            'TPS_OUVERTURE'        => '0.000000',
            'CODE_LIBRE'           => null,
            'ANOMALIE'             => 'N',
            'CODE_OUTILLAGE'       => null,
            'CODE_ENREG'           => '0',
            'DH_CREATION'          => $now->format('Y-m-d H:i:s'),
            'DH_MODIF'             => $now->format('Y-m-d H:i:s'),
            'CODE_UTILISATEUR'     => 'FZ',
        ]);
    
        $of->update([
            "DH_DEBUT_REEL" => $now->format('Y-m-d H:i:s'),
            "ETAT_OF" => "LANC"
        ]);
    
        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of)->first();
    
        if ($operation) {
            $operation->update([
                "QTE_REALISEE" => 0,
                "ETAT_OP" => "REGL",
            ]);
        }
    
        // ✅ Step 7: Return success or failure
        if ($machine_event_controller) {
            return response()->json([
                "message" => "Machine event controller created successfully."
            ]);
        }
    
        return response()->json([
            "error" => "Erreur lors de la création de l'événement machine."
        ], 500);
    }



    public function fin(Request $request)
    {

        $now = Carbon::now();
        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of)->first();

        if ($operation) {
            $operation->update([
                "DH_FIN_REEL" => $now->format('Y-m-d H:i:s'),
            ]);
        }

        $machine_event_controller  = MachineEventControle::where("CODE_OF", $request->code_of)->first();
        if($machine_event_controller){
            $machine_event_controller->delete();
        }


        MachineEvent::create([
            'CODE_SOCIETE' => '100',
            'CODE_MACHINE' => $request->code_emachine,
            'CODE_PERS' =>  $request->code_personnel,
            'CODE_OF' => $request->code_of,
            'CODE_OP' => '10',
            'DH_DEBUT' => '2025-04-18 15:26:25.000',
            'DH_FIN' => '2025-04-18 16:31:14.000',
            'DATE_REF' => '2025-04-18 15:26:25.000',
            'REF_ARTICLE' => 'CAB009502',
            'NO_MOIS' => '202504',
            'NO_SEMAINE' => '202516',
            'COEFFICIENT' => 1,
            'REPARTITION' => 1.000000,
            'CODE_ACTIVITE' => 'REGL',
            'QTE_COMPTE' => 0.000000,
            'QTE_BONNE' => 0.000000,
            'QTE_REBUT' => 0.000000,
            'QTE_AUTRE' => 0.000000,
            'TPS_OUVERTURE' => 0.000000,
            'ANOMALIE' => 'N',
            'PR_TPS_REGL_ALLOUE' => 0.000000,
            'PR_QTE_BONNE_LISSE' => 0.000000,
            'PR_PRORATA_MA' => 0.000000,
            'PR_PRORATA_MO' => 0.000000,
            'PR_CALCUL_OK' => 'N',
            'TAUX_HORAIRE' => 0,
            'CODE_ENREG' => 0,
            'DH_CREATION' => $now->format('Y-m-d H:i:s'),
            'DH_MODIF' => $now->format('Y-m-d H:i:s'),
            'CODE_UTILISATEUR' => 'FZ'
        ]);
    }
}
