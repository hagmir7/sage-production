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
use Illuminate\Support\Facades\DB;

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

        
        // Wrap everything in a transaction
        return DB::transaction(function () use ($request) {

            $validator = Validator::make($request->all(), [
                "code_machine" => "required|exists:T_MACHINE,CODE_MACHINE",
                "code_of"      => "required|exists:T_ORDREFAB,CODE_OF",
                "qte_bonne"    => "required|integer|min:0",
                "qte_rebut"    => "required|integer|min:0", 
                "code_of_change" => "required|exists:T_ORDREFAB,CODE_OF",
                "status"       => "required|integer|min:0|max:1"
            ]);
    
            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()]);
            }
    


            $now = Carbon::now();
            $timezone = 'Europe/Paris';
            $dateFormat = 'Y-d-m H:i:s.v';


            // Get necessary data
            $order_fabrication = OrderFabrication::find($request->code_of);
            $new_of = OrderFabrication::find($request->code_of_change);
            
            if (!$order_fabrication) {
                return response()->json([
                    'errors' => ["order_fabrication" => "Order fabrication not found."]
                ]);
            }
            
            if (!$new_of) {
                return response()->json([
                    'errors' => ["new_of" => "New order fabrication not found."]
                ]);
            }
    
            // Check event control
            $eventControl = MachineEventControle::where('CODE_OF', $request->code_of)
                ->where("CODE_MACHINE", $request->code_machine)
                ->first();
    
            if (!$eventControl) {
                return response()->json([
                    "errors" => ["machine" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]
                ]);
            }
    
            // Check operation
            $operation = Operation::where('CODE_OF', $request->code_of)
                ->where("CODE_MACHINE", $request->code_machine)
                ->whereIn("ETAT_OP", ["PROD"])
                ->first();
                
            if (!$operation) {
                return response()->json([
                    'errors' => ["operation" => "L'opération n'existe pas."]
                ]);
            }
    
            // Check new operation
            $new_operation = \App\Models\Operation::where("CODE_OF", $request->code_of_change)
                ->where("CODE_MACHINE", $request->code_machine)
                ->whereIn("ETAT_OP", ["PROD", "REGL", "ATTENTE"])
                ->first();
    
            if (!$new_operation) {
                return response()->json([
                    "errors" => ["of" => "Order de fabrication n'existe pas ou son état ne permet pas d'effectuer l'opération."]
                ]);
            }
    
            // Get current time and update quantities
            $now = Carbon::now();
            $new_qte_bonne = intval($request->qte_bonne) + intval($operation->QTE_BONNE);

            
            // Update operation
            $operation->update([
                "QTE_BONNE" => $new_qte_bonne,
                "QTE_REBUT" => intval($request->qte_rebut),
                "MACHINE_FIXE" => "O"
            ]);
    
            // Update original OF
            $order_fabrication->update([
                'QTE_BONNE' => $new_qte_bonne,
                'QTE_REBUT' => $request->qte_rebut,
                'DH_FIN_REEL' => intval($request->status) ? $now->format("Y-d-m H:i:s.v") : null,
                'ETAT_OF' => intval($request->status) ? "FINI" : $order_fabrication->ETAT_OF
            ]);
            
            
            // Copy event data and create a new machine event
            $eventData = $eventControl->toArray();
            unset($eventData['CODE_ACTIVITE_PREC'], $eventData['id']);
            
            // Set dates properly
            foreach (['DH_CREATION', 'DH_DEBUT', 'DATE_REF'] as $dateField) {
                if (!empty($eventData[$dateField])) {
                    $eventData[$dateField] = Carbon::parse($eventData[$dateField])
                        ->timezone($timezone)
                        ->format($dateFormat);
                }
            }
            
            $eventData['DH_FIN'] = $now->timezone($timezone)->format($dateFormat);
            $eventData['DH_MODIF'] = $now->format('Y-m-d H:i:s');
            $eventData['CODE_UTILISATEUR'] = auth()->user() ? auth()->user()->username : 'SYSTEM';
            $eventData['CODE_MACHINE'] = $request->code_machine;
            
            // Create new event record
            MachineEvent::create($eventData);
            $eventControl->delete();
    
            // Update new operation
            $new_operation->update([
                "DH_DEBUT_REEL" => $new_operation->DH_DEBUT_REEL ?: $now->format('Y-d-m H:i:s.v'),
                "QTE_REALISEE" => 0,
                "ETAT_OP" => "PROD",
                "DH_MODIF" => $now->format('Y-d-m H:i:s.v')
            ]);
    
            // Create new control event
            $machine_event_controller = MachineEventControle::create([
                'CODE_SOCIETE'         => $eventData['CODE_SOCIETE'] ?? '100',
                'CODE_MACHINE'         => $request->code_machine,
                'CODE_PERS'            => $new_of->CODE_PERS,
                'CODE_OF'              => $request->code_of_change,
                'CODE_OP'              => $new_operation->CODE_OP,
                'DH_DEBUT'             => $now,
                'DATE_REF'             => $now,
                'REF_ARTICLE'          => $new_of->REF_ARTICLE,
                'NO_MOIS'              => $now->format('Ym'),
                'NO_SEMAINE'           => $now->year . $now->week,
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
                'DH_CREATION'          => $now,
                'DH_MODIF'             => $now,
                'CODE_UTILISATEUR'     => auth()->user() ? auth()->user()->username : 'SYSTEM',
            ]);
    
            // Update new OF
            $new_of->update([
                "DH_DEBUT_REEL" => $new_of->DH_DEBUT_REEL ?? $now->format('Y-m-d H:i:s.v'),
                "ETAT_OF" => "LANC"
            ]);
            
            
            if ($machine_event_controller) {
                return response()->json([
                    "message" => "Machine event controller created successfully."
                ]);
            }
            
            return response()->json([
                "errors" => "Erreur lors de la création de l'événement machine."
            ], 500);
        });
    }
    
}
