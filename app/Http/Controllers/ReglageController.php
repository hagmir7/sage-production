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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReglageController extends Controller
{

    public function debut(Request $request)
    {
        // Step 1: Validate input with existence checks

        $validator = Validator::make($request->all(), [
            "code_machine"    => "required|exists:T_MACHINE,CODE_MACHINE",
            "code_personnel"  => "required|exists:T_PERSONNEL,CODE_PERS",
            "code_of"         => "required|exists:T_ORDREFAB,CODE_OF"
        ]);

        // dd($request->all());

        
        if($validator->fails()){
            return ["errors" => $validator->errors()];
        }
    
        // Step 2: Retrieve models
        $machine = Machine::find($request->code_machine);
        $personnel = Personnel::find($request->code_personnel);
        $of = OrderFabrication::find($request->code_of);
    
        // Step 3: Check if a MachineEventControle already exists
        $machineEventExists = MachineEventControle::where("CODE_MACHINE", $request->code_machine)->exists();
    
        if ((!$machine )|| $machineEventExists) {
            return response()->json([
                "errors" => ["machine" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]
            ]);
        }
    
        // Step 4: Create MachineEventControle
        $now = Carbon::now();

        $operation = \App\Models\Operation::where("CODE_OF", $request->code_of)->first();
    
        if ($operation) {
            $operation->update([
                "QTE_REALISEE" => 0,
                "ETAT_OP" => "REGL",
            ]);
        }
    
        $machine_event_controller = MachineEventControle::create([
            'CODE_SOCIETE'         => '100',
            'CODE_MACHINE'         => $request->code_machine,
            'CODE_PERS'            => $request->code_personnel,
            'CODE_OF'              => $request->code_of,
            'CODE_OP'              =>  $operation->CODE_OP,
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
        ]);


        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()]);
        }




        $codeOf = $request->code_of;



        // dump($request->all());
        $eventControl = MachineEventControle::where('CODE_OF', $codeOf)
            ->where("CODE_MACHINE", $request->code_machine)->first();
    

        // dd($eventControl);
        
        if (!$eventControl) {
            return response()->json(['errors' => ["machine_evetn_control" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]]);
        }
    
        $now = Carbon::now();
        $timezone = 'Europe/Paris';
        $dateFormat = 'Y-d-m H:i:s.v';
        

        Operation::where('CODE_OF', $codeOf)->update(['DH_FIN_REEL' => $now]);
        

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


    public function fin_machines($code)
    {
        $machine = Machine::find($code);
        if (!$machine) {
            return response()->json(["errors" => "Machine non trouvée"], 404);
        }

        $events = $machine->current_events()
            ->where('CODE_ACTIVITE_PREC', 'REGL')
            ->select('CODE_MACHINE')
            ->get();

        return response()->json([
            'machine' => $machine->LIBELLE_MACHINE,
            'events' => $events
        ]);
    }
    

}