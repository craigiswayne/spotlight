<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleRepository
{    
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function upsert(Request $request, $id)
    {        
        DB::select("EXEC slt.pr_Role_Upsert
                                 @Id = ?
                                ,@Name = ?
                                ,@Description = ?

                                ,@RoleTypeId = ?                                
                                ,@ProfileId = ?    

                                ,@ActionIds = ?
                                
                                ,@LoggedInUserId = ?",
                                
                            array($id
                                ,$request->input('name')
                                ,$request->input('description')

                                ,$request->input('typeId')                                                                    
                                ,$request->input('profileId')

                                ,implode(', ', $request->input('actions'))
                                                                
                                ,auth()->user()->id
                            )
                );
    }

    public function delete($id)
    {        
        DB::statement("EXEC slt.pr_Role_Delete
                                 @Id = ?                                
                                ,@LoggedInUserId = ?",
                                
                            array($id                                                                                               
                                ,auth()->user()->id
                            )
                );
    }

    public function upsertUsers(Request $request, $id)
    {               
        DB::statement("EXEC slt.pr_Role_AssignUsers
                                 @Id = ? 
                                ,@UserIds = ?                               
                                ,@LoggedInUserId = ?",
                                
                            array($id
                                ,implode(', ', $request->all())
                                ,auth()->user()->id
                            )
                );
    }
}