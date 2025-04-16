<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineEvent;
use App\Models\MachineEventControle;
use App\Models\OrderFabrication;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ReglageController extends Controller
{







    public function debut(Request $request)
    {
        $machine = Machine::find($request->code_machine);

        if (count(MachineEventControle::where("CODE_MACHINE", $request->code_machine)->get())) {
            return response()->json(["error" => "La machine n'existe pas ou son état ne permet pas d'effectuer l'opération."]);
        }

        $personnel = Personnel::find($request->code_personnel);

        $of = OrderFabrication::find($request->code_of);

        $now = Carbon::now();

        $machine_event_controller = MachineEventControle::create([
            'CODE_SOCIETE'         => '100', // Code Componet
            'CODE_MACHINE'         => $request->code_machine, // Code machine
            'CODE_PERS'            => $request->code_personnel, // Code Personnel
            'CODE_OF'              => $request->code_of,  // Code Order Fabrication
            'CODE_OP'              => '10', // Code operation (Emballage...)
            'DH_DEBUT'             => $now->format('Y-m-d H:i:s'), // Debute de evant
            'DATE_REF'             => $now->format('Y-m-d H:i:s'),
            'REF_ARTICLE'          => $of->REF_ARTICLE, // Referance article
            'NO_MOIS'              => $now->format('Ym'), // Year & Month
            'NO_SEMAINE'           => $now->year . $now->weekOfYear, // Year & Week
            'COEFFICIENT'          => '1',
            'REPARTITION'          => '1.000000',
            'CODE_ACTIVITE'        => 'REGL', // Event Type
            'CODE_ALEA'            => null, // null in debute de reglage
            'QTE_COMPTE'           => '0.000000',
            'QTE_BONNE'            => '0.000000',
            'QTE_REBUT'            => '0.000000',
            'CODE_REBUT'           => null,
            'QTE_AUTRE'            => '0.000000',
            'CODE_ACTIVITE_PREC'   => 'REGL', // Event Type
            'TPS_OUVERTURE'        => '0.000000',
            'CODE_LIBRE'           => null, // Code libre (code machine lowercase)
            'ANOMALIE'             => 'N',
            'CODE_OUTILLAGE'       => null,
            'CODE_ENREG'           => '0',
            'DH_CREATION'          => $now->format('Y-m-d H:i:s'), // Created at
            'DH_MODIF'             => $now->format('Y-m-d H:i:s'), // Update date
            'CODE_UTILISATEUR'     => 'FZ', // Created by
        ]);

        if ($machine_event_controller) {
            return response()->json(["message" => "Machin event controler created successfully."]);
        }
    }
}
