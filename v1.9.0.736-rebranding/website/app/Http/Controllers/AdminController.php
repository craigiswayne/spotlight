<?php

namespace App\Http\Controllers;

use App\User;
use App\Helpers\SecureHelper;
use Illuminate\Http\Request;

class AdminController extends Controller
{       
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('/admin/pages'); 
    }

}
