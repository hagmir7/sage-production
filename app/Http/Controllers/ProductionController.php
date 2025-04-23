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

        // Step 2: Retrieve models
        $machine = Machine::find($request->code_machine);
        $personnel = Personnel::find($request->code_personnel);
        $of = OrderFabrication::find($request->code_of);

        $machineEventExists = MachineEventControle::where("CODE_MACHINE", $request->code_machine)->exists();
    
        if ((!$machine )|| $machineEventExists) {
            return response()->json([
                "errors" => ["machine" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }

        $now = Carbon::now();


        //  Start with Operation
        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of)->first();
    
        if ($operation) {
            $operation->update([
                "DH_DEBUT_REEL" => $now->format('Y-d-m H:i:s.v'),
                "QTE_REALISEE" => 0,
                "ETAT_OP" => "PROD",
                "DH_MODIF" => $now->format('Y-d-m H:i:s.v')
            ]);
        }


    
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
            "code_machine"    => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_of"         => "required|exists:T_ORDREFAB,CODE_OF",

            "qte_bonne" => "required|min:0",
            "qte_rebut" => "required|min:0",
            "qte_retouche" => "nullable|min:0",
            "status" => "required|min:0|max:1|integer"
        ]);


        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()]);
        }


        $codeOf = $request->code_of;


        $eventControl = MachineEventControle::where('CODE_OF', $codeOf)
            ->where("CODE_MACHINE", $request->code_machine)
            ->first();
    

        
        if (!$eventControl) {
            return response()->json(['errors' => ["machine_evetn_control" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]]);
        }
    
        $now = Carbon::now();
        $timezone = 'Europe/Paris';
        $dateFormat = 'Y-d-m H:i:s.v';
        

        $operation = Operation::where('CODE_OF', $codeOf)
            ->where("CODE_MACHINE", $request->code_machine)
            ->first();


        if(!$operation){
            return response()->json(['errors' => ["operation" => "Operation is not exists"]]);
        }



        $operation->update([
            'QTE_BONNE' => $operation->QTE_BONNE + $request->qte_bonne,
            'QTE_REBUT' => $request->qte_rebut,
            'QTE_AUTRE' => intval($operation->QTE_LANCEE) - (intval($request->qte_rebut))
        ]);



        // Order fabrication
    
        $order_fabrication = OrderFabrication::find($request->code_of);
        $order_fabrication->update([
            'QTE_BONNE' => $operation->QTE_BONNE + $request->qte_bonne,
            'QTE_REBUT' => $request->qte_rebut,
            // If fin
            'DH_FIN_REEL' => intval($request->status) ? $now->format("Y-d-m H:i:s.v") : null,
            'ETAT_OF' => intval($request->status) ? "FINI" : $order_fabrication->ETAT_OF

        ]);

        $eventData = $eventControl->toArray();

        unset($eventData['CODE_ACTIVITE_PREC'], $eventData['id']);
        

        foreach (['DH_CREATION', 'DH_DEBUT', 'DATE_REF'] as $dateField) {
            $eventData[$dateField] = Carbon::parse($eventData[$dateField])
                ->timezone($timezone)
                ->format($dateFormat);
        }

        
        $eventData['DH_FIN'] = $now->timezone($timezone)->format($dateFormat);
        $eventData['DH_MODIF'] = $now->format('Y-m-d H:i:s');
        $eventData['CODE_UTILISATEUR'] = auth()->user()->username ?? 'SYSTEM';
        $eventData['CODE_MACHINE'] = $request->code_machine;
    

        MachineEvent::create($eventData);
        $eventControl->delete();
        
        return response()->json(['message' => 'Event controller successfully finalized'], 200);
    }
}
