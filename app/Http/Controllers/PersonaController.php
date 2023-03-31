<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllPersona(){
       $persona = DB::table('persona')->get();
       return response()->json([
        'message' => 'Respuesta Ok',
        'pesona' => $persona
        ], 201);
    }
}
