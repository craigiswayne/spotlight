<?php

namespace App\Http\Controllers;

use App\Role;
use App\RoleBase;
use Illuminate\Http\Request;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{    
    protected $repo;

    public function __construct(Request $request)
    {
        $this->repo = new RoleRepository($request);
    }


     /**
     * Checks if a provided user attribute is avilable for use.
     *
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {
        if(!$request->has('name')) {
            return response()->json(false);
        }

        $whereClause = [];
        array_push($whereClause , ['deleted', '=', 0]);
        if($request->has('name')) {
            array_push($whereClause , ['name', '=', $request->name]);
        }
        if($request->has('id')) {
            array_push($whereClause , ['id', '!=', $request->id]);
        }
        
        return response()->json((RoleBase::where($whereClause)->count() == 0));        
    }

    public function get()
    {                        
        $roles = RoleBase::with(['type'])->where([['deleted', '=' , 0],['typeId', '<=',  auth()->user()->role->typeId]])->orderBy('name')->get();               
        return $roles;
    }

   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {        
        $roles = Role::with(['type', 'profile'])->where([['deleted', '=' , 0],['typeId', '<=',  auth()->user()->role->typeId]])->orderBy('system', 'DESC')->orderBy('name')->get();      
        return view('admin.security.roles.index', compact('roles'));
    }
   
    /**
     * Create a new role in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return $this->upsert($request, null);
    }

    /**
     * Edits an existing role in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if(!is_numeric($id)) {
            return response()->json("This request requires an id", 400);
        }

        return $this->upsert($request, $id);
    }

    /**
     * Create a new profile in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function upsert(Request $request, $id)
    {        
        $data = $request->all();       
        if(!$data) {
            return response()->json("The request was empty", 400);
        }
        
        $role = null;
        if($id != null) {
            $role = RoleBase::where([['id', $id],['deleted', '=' , 0]])->first();
            if($role == null) {
                return response()->json("Invalid role", 400);
            }

            if($role->typeId > auth()->user()->role->typeId) {
                return response()->json("Insufficient access", 400);
            }
        }

        $profileId = $request->input('profileId');

        if($role && $role->system == 1) {
            $validator = Validator::make($data, [
                'profileId' => ['required', Rule::exists('vw_profile', 'id')->where(function ($query) use($profileId) {
                    $query->where([['id', $profileId],['public', true]]);
                  })]
            ], [                  
                'profileId.required' => 'Profile selection is required',    
                'profileId.exists' => 'Invalid profile',    
            ]);   
        }
        else {            
            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'min:5', 'max:128'],
                'description' => ['required', 'string', 'min:5', 'max:255'],

                'typeId' => ['required', 'integer', 'exists:vw_roletype,id'],
                'profileId' => ['required',Rule::exists('vw_profile', 'id')->where(function ($query) use($profileId) {
                                                        $query->where([['id', $profileId],['public', true]]);
                                                      })],

                'actions' =>['sometimes', 'array']
            ], [                  
                'name.required' => 'Name of role is required',
                'name.min' => 'Name cannot be less than five characters',
                'name.max' => 'Name cannot be more than 128 characters',

                'description.min' => 'Description cannot be less than five characters',
                'description.max' => 'Description cannot be more than 128 characters',    

                'profileId.required' => 'Profile selection is required',    
                'profileId.exists' => 'Invalid profile',    
                
                'actions.required' => 'Securable actions are required in the request'          
            ]);  
            
            // If they changing the type to a rank higher than their own
            if($request->input('typeId')  > auth()->user()->role->typeId) {
                return response()->json("Insufficient access", 400);
            }      
        } 
        
        if($validator->fails()) {            
            return response()->json($validator->errors()->first(), 400);
        }
        
        if($id != null && $role->system == 1) {
            $role->update(['profileId' => $request->input('profileId')]);
        } else {
            $this->repo->upsert($request, $id);
        }
    }

     /**
     * Deletes an existing role in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if(!is_numeric($id)) {
            return response()->json("This request requires an id", 400);
        }

        $role = RoleBase::where([['id', $id],['deleted', '=' , 0]])->first();

        if($role == null || $role->system == 1) {
            return response()->json("You are not allowed to delete a system role", 400);
        }

        if($role->typeId > auth()->user()->role->typeId) {
            return response()->json("Insufficient access", 400);
        }

        return $this->repo->delete($id);
    }

   /**
    * Upsert for users assigned to a role.
    *
    * @return \Illuminate\Http\Response
    */
    public function upsertUsers(Request $request, $id) {
        if(!is_numeric($id)) {
            return response()->json("This request requires an id", 400);
        }

        $data = $request->all();       
        
        if(!$data) {
            return response()->json("The request was empty", 400);
        }

        $data = [ 'data' => $data ];
        $validator = Validator::make($data, [
            'data' => ['required', 'array'],            
        ], [                  
            'data.required' => 'An array of user ids were expected'            
        ]);    
        
        if($validator->fails()) {            
            return response()->json($validator->errors()->first(), 400);
        }

        $role = RoleBase::where([['id', $id],['deleted', '=' , 0]])->first();

        if($role->typeId > auth()->user()->role->typeId) {
            return response()->json("Insufficient access", 400);
        }

        $this->repo->upsertUsers($request, $id);
    }
}

?>