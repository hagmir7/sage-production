<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineEventControle;
use App\Models\Operation;
use App\Models\MachineEvent;
use App\Models\OrderFabrication;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function debut(Request $request){

        $validator = Validator::make($request->all(), [
            "code_machine"    => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
            "code_of"         => "required|exists:T_ORDREFAB,CODE_OF"
        ]);

        if($validator->fails()){
            return ["errors" => $validator->errors()];
        }

        $of = OrderFabrication::find($request->code_of);

        $machineEventExists = MachineEventControle::where("CODE_MACHINE", $request->code_machine)->exists();

        if ($machineEventExists) {
            return response()->json([
                "errors" => ["machine" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }

        $now = Carbon::now();


        //  Start with Operation
        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of)
            ->where("CODE_MACHINE", $request->code_machine)
            ->whereIn("ETAT_OP", ["PROD", "REGL", "ATTENTE"])
            ->first();
    
        if (!$operation) {
            return response()->json([
                "errors" => ["of" => "Order de fabrication n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }

        $operation->update([
            "DH_DEBUT_REEL" => $now->format('Y-d-m H:i:s.v'),
            "QTE_REALISEE" => 0,
            "ETAT_OP" => "PROD",
            "DH_MODIF" => $now->format('Y-d-m H:i:s.v')
        ]);


    
        // Step 4: Create MachineEventControle
    
        $machine_event_controller = MachineEventControle::create([
            'CODE_SOCIETE'         => '100',
            'CODE_MACHINE'         => $request->code_machine,
            'CODE_PERS'            => $request->code_personnel,
            'CODE_OF'              => $request->code_of,
            'CODE_OP'              => $operation->CODE_OP,
            'DH_DEBUT'             => $now->format('Y-m-d H:i:s'),
            'DATE_REF'             => $now->format('Y-m-d H:i:s'),
            'REF_ARTICLE'          => $of->REF_ARTICLE,
            'NO_MOIS'              => $now->format('Ym'),
            'NO_SEMAINE'           => $now->year . $now->weekOfYear,
            'COEFFICIENT'          => '1',
            'REPARTITION'          => '1.000000',
            'CODE_ACTIVITE'        => 'PROD',
            'CODE_ALEA'            => null,
            'QTE_COMPTE'           => '0.000000',
            'QTE_BONNE'            => '0.000000',
            'QTE_REBUT'            => '0.000000',
            'CODE_REBUT'           => null,
            'QTE_AUTRE'            => '0.000000',
            'CODE_ACTIVITE_PREC'   => 'PROD',
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
        $validator = Validator::make($request->all(), [
            "code_machine" => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_of"      => "required|exists:T_ORDREFAB,CODE_OF",
            "qte_bonne"    => "required|integer|min:0",
            "qte_rebut"    => "required|integer|min:0",
            "qte_retouche" => "nullable|integer|min:0",
            "status"       => "required|integer|min:0|max:1"
        ]);
    
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()]);
        }
    
        $codeOf = $request->code_of;
    
        $eventControl = MachineEventControle::where('CODE_OF', $codeOf)
            ->where("CODE_MACHINE", $request->code_machine)
            ->first();
    
        if (!$eventControl) {
            return response()->json([
                'errors' => [
                    "machine_event_control" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."
                ]
            ]);
        }
    

        $now = Carbon::now();
        $timezone = 'Europe/Paris';
        $dateFormat = 'Y-d-m H:i:s.v';
 
    
        $operation = Operation::where('CODE_OF', $codeOf)
            ->where("CODE_MACHINE", $request->code_machine)
            ->whereIn("ETAT_OP", ["PROD"])
            ->first();
    
        if (!$operation) {
            return response()->json([
                'errors' => ["operation" => "L'opération n'existe pas."]
            ]);
        }


        $order_fabrication = OrderFabrication::find($request->code_of);
    
        if (!$order_fabrication) {
            return response()->json([
                'errors' => ["order_fabrication" => "Order fabrication not found."]
            ]);
        }


        $new_qte_bonne = intval($request->qte_bonne) + intval($operation->QTE_BONNE);
    
        $operation->update([
            "QTE_BONNE" => $new_qte_bonne,
            "QTE_AUTRE" => intval($request->qte_retouche),
            "QTE_REBUT" => intval($request->qte_rebut),
        ]);
    
        

        $order_fabrication->update([
            'QTE_BONNE' => $new_qte_bonne,
            'QTE_REBUT' => $request->qte_rebut,
            'DH_FIN_REEL' => intval($request->status) ? $now->format("Y-d-m H:i:s.v") : null,
            'ETAT_OF' => intval($request->status) ? "FINI" : $order_fabrication->ETAT_OF
        ]);
    
        $eventData = $eventControl->toArray();
        unset($eventData['CODE_ACTIVITE_PREC'], $eventData['id']);
    
        foreach (['DH_CREATION', 'DH_DEBUT', 'DATE_REF'] as $dateField) {
            if (!empty($eventData[$dateField])) {
                $eventData[$dateField] = Carbon::parse($eventData[$dateField])
                    ->timezone($timezone)
                    ->format($dateFormat);
            }
        }
    
        $eventData['DH_FIN'] = $now->timezone($timezone)->format($dateFormat);
        $eventData['DH_MODIF'] = $now->format('Y-m-d H:i:s');
        $eventData['CODE_UTILISATEUR'] = optional(auth()->user())->username ?? 'SYSTEM';
        $eventData['CODE_MACHINE'] = $request->code_machine;
    
        MachineEvent::create($eventData);
        $eventControl->delete();
    
        return response()->json(['message' => 'Event controller successfully finalized'], 200);
    }



    
    public function change(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "code_machine" => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
            "code_of"      => "required|exists:T_ORDREFAB,CODE_OF",
            "qte_bonne"    => "required|integer|min:0",
            "qte_rebut"    => "required|integer|min:0",
            "code_of_change" => "required|exists:T_ORDREFAB,CODE_OF",
            "status"       => "required|integer|min:0|max:1"
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()]);
        }

        $eventControl = MachineEventControle::where('CODE_OF', $request->code_of)
            ->where("CODE_MACHINE", $request->code_machine)
            ->first();

    
        if (!$eventControl) {
            return response()->json([
                'errors' => [
                    "machine_event_control" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."
                ]
            ]);
        }

        $operation = Operation::where('CODE_OF', $request->code_of)
            ->where("CODE_MACHINE", $request->code_machine)
            ->whereIn("ETAT_OP", ["PROD"])
            ->first();

        
        if (!$operation) {
            return response()->json([
                'errors' => ["operation" => "L'opération n'existe pas."]
            ]);
        }

        

        $order_fabrication = OrderFabrication::find($request->code_of);
    
        if (!$order_fabrication) {
            return response()->json([
                'errors' => ["order_fabrication" => "Order fabrication not found."]
            ]);
        }

        $new_qte_bonne = intval($request->qte_bonne) + intval($operation->QTE_BONNE);
    
        $operation->update([
            "QTE_BONNE" => $new_qte_bonne,
            "QTE_REBUT" => intval($request->qte_rebut),
            "MACHINE_FIXE" => "O"
        ]);

        $now = Carbon::now();
        $timezone = 'Europe/Paris';
        $dateFormat = 'Y-d-m H:i:s.v';


        $order_fabrication->update([
            'QTE_BONNE' => $new_qte_bonne,
            'QTE_REBUT' => $request->qte_rebut,
            'DH_FIN_REEL' => intval($request->status) ? $now->format("Y-d-m H:i:s.v") : null,
            'ETAT_OF' => intval($request->status) ? "FINI" : $order_fabrication->ETAT_OF
        ]);
    
        $eventData = $eventControl->toArray();
        unset($eventData['CODE_ACTIVITE_PREC'], $eventData['id']);
    
        foreach (['DH_CREATION', 'DH_DEBUT', 'DATE_REF'] as $dateField) {
            if (!empty($eventData[$dateField])) {
                $eventData[$dateField] = Carbon::parse($eventData[$dateField])
                    ->timezone($timezone)
                    ->format($dateFormat);
            }
        }
    
        $eventData['DH_FIN'] = $now->timezone($timezone)->format($dateFormat);
        $eventData['DH_MODIF'] = $now->format('Y-m-d H:i:s');
        $eventData['CODE_UTILISATEUR'] = optional(auth()->user())->username ?? 'SYSTEM';
        $eventData['CODE_MACHINE'] = $request->code_machine;
    
        MachineEvent::create($eventData);
        $eventControl->delete();

        // Create New Operation
        $new_of = OrderFabrication::find($request->code_of_change);

        $machineEventExists = MachineEventControle::where("CODE_MACHINE", $request->code_machine)->exists();

        if ($machineEventExists) {
            return response()->json([
                "errors" => ["machine" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }

        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of_change)
            ->where("CODE_MACHINE", $request->code_machine)
            ->whereIn("ETAT_OP", ["PROD", "REGL", "ATTENTE"])
            ->first();

        if (!$operation) {
            return response()->json([
                "errors" => ["of" => "Order de fabrication n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }

        $operation->update([
           "DH_DEBUT_REEL" => $operation->DH_DEBUT_REEL ?: $now->format('Y-d-m H:i:s.v'),
            "QTE_REALISEE" => 0,
            "ETAT_OP" => "PROD",
            "DH_MODIF" => $now->format('Y-m-d H:i:s')
        ]);

        $machine_event_controller = MachineEventControle::create([
            'CODE_SOCIETE'         => '100',
            'CODE_MACHINE'         => $request->code_machine,
            'CODE_PERS'            => $request->code_personnel,
            'CODE_OF'              => $request->code_of_change,
            'CODE_OP'              => $operation->CODE_OP,
            'DH_DEBUT'             => $now->format('Y-m-d H:i:s'),
            'DATE_REF'             => $now->format('Y-m-d H:i:s'),
            'REF_ARTICLE'          => $new_of->REF_ARTICLE,
            'NO_MOIS'              => $now->format('Ym'),
            'NO_SEMAINE'           => $now->year . $now->weekOfYear,
            'COEFFICIENT'          => '1',
            'REPARTITION'          => '1.000000',
            'CODE_ACTIVITE'        => 'PROD',
            'CODE_ALEA'            => null,
            'QTE_COMPTE'           => '0.000000',
            'QTE_BONNE'            => '0.000000',
            'QTE_REBUT'            => '0.000000',
            'CODE_REBUT'           => null,
            'QTE_AUTRE'            => '0.000000',
            'CODE_ACTIVITE_PREC'   => 'PROD',
            'TPS_OUVERTURE'        => '0.000000',
            'CODE_LIBRE'           => null,
            'ANOMALIE'             => 'N',
            'CODE_OUTILLAGE'       => null,
            'CODE_ENREG'           => '0',
            'DH_CREATION'          => $now->format('Y-m-d H:i:s'),
            'DH_MODIF'             => $now->format('Y-m-d H:i:s'),
            'CODE_UTILISATEUR'     => 'FZ',
        ]);


        $new_of->update([
            "DH_DEBUT_REEL" => $operation->DH_DEBUT_REEL ?: $now->format('Y-d-m H:i:s.v'),
            "ETAT_OF" => "LANC"
        ]);


        if ($machine_event_controller) {
            return response()->json([
                "message" => "Machine event controller created successfully."
            ]);
        }
    
        return response()->json([
            "error" => "Erreur lors de la création de l'événement machine."
        ], 500);


    }
    
}
