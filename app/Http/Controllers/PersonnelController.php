<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\Personnel;
use App\Models\PersonnelCategory;
use App\Models\Service;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index()
    {
        return Personnel::orderByDesc("DH_CREATION")->get();
    }

    public function category()
    {
        return PersonnelCategory::all();
    }

    public function personnel_category($id)
    {
        $category = PersonnelCategory::with(['personnel'])->find($id);
        return $category;
    }



    public function equipe()
    {
        return Equipe::all();
    }


    public function personnel_equipe($id)
    {
        $equip = Equipe::with(['personnel'])->find($id);
        return $equip;
    }


    public function service(){
        return Service::all();
    }

    public function personnel_service($id)
    {
        $service = Service::with(['personnel'])->find($id);


        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        return response()->json($service);
    }
}
