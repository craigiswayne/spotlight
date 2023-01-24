<?php

namespace App\Http\Controllers;

use App\Securable;
use App\RoleType;

use Illuminate\Http\Request;
use App\Repositories\RoleRepository;

class SecurableController extends Controller
{    
    public function get(Request $request, $type = null)
    {               
        $type = 1; 
        if($request->has('type')) {
            $type = $request->type;
        }

        RoleType::findOrFail($type);
        
        $securables = Securable::with(['actions' => function ($query) {
            $query->orderBy('position');
        }])->with('roleType')->where([['deleted', 0], ['roleTypeId', '<=', $type]])->orderBy('position')->get();
           
        return $securables->groupBy('roleType.name');
    }
}

?>