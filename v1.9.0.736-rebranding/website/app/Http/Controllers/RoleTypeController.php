<?php

namespace App\Http\Controllers;

use App\RoleType;
use Illuminate\Http\Request;
use App\Repositories\RoleRepository;

class RoleTypeController extends Controller
{    
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function get()
    {        
        //return RoleType::where('id', '<=' , auth()->user()->role->typeId)->orderBy('id')->get();        
        return RoleType::orderBy('id')->get();        
    }
}

?>