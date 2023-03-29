<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Support\Facades\DB;

class AgendaController  extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllAgenda(){
       return $agenda = DB::table('evento')->get();

    }

}
