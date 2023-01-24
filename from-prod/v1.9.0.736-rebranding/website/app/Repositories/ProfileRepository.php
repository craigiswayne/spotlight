<?php namespace App\Repositories;

use App\RoleBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
Use Exception;

class ProfileRepository
{    
 
    public function getConfig($id)
    {   
        return DB::select("EXEC slt.pr_Profile_GetJson
                             @Id = ?
                            ,@LoggedInUserId = ?"
                    ,array($id, auth()->user()->id))[0]->profile; 
    }

    // Get all instances of model
    public function assets($profileId)
    {
        $result =  DB::select("EXEC slt.pr_Profile_Assets_Get
                                    @ProfileId = ?",
                            array($profileId));

       return array_map(function($item) { return $item->Url; }, $result);
    }
    
    // Get all instances of model
    public function upsert(Request $request, $id)
    {        
            DB::select("EXEC slt.pr_Profile_Upsert
                                     @Id = ?
                                    ,@Name = ?
                                    ,@Description = ?
                                    ,@IsPublic = ?
                                    ,@PublicWriteAccess = ?
                                    
                                    ,@StudioFilterTypeId = ?                               
                                    ,@StudioIds = ?
                                    
                                    ,@GameFilterTypeId = ?                                                                
                                    ,@GameIsNew = ?
                                    ,@GameIsFeatured = ?
                                    ,@GameIds = ?
                                    
                                    ,@NavigationJson = ?
                                    ,@SettingsJson = ?
                                    
                                    ,@LoggedInUserId = ?",
                                    
                                array($id
                                    ,$request->input('info.name')
                                    ,$request->input('info.description')
                                    ,$request->input('info.visibility') ? 1 : 0
                                    ,$request->input('info.writeAccess') ? 1 : 0
                                                                        
                                    ,$request->input('studio.type')
                                    ,implode(', ', $request->input('studio.items'))
                                    
                                    ,$request->input('game.type')
                                    ,$request->input('game.attributes.new')
                                    ,$request->input('game.attributes.featured')
                                    ,implode(', ', $request->input('game.items'))

                                    ,json_encode($request->input('navigation'))
                                    ,json_encode($request->input('settings'))

                                    ,auth()->user()->id
                                )                                
                            
                );

 

    }
 
     public function assignRoles(Request $request, $profileId)
     {
         DB::statement("EXEC slt.pr_Profile_Roles_Assign
                                      @ProfileId = ?
                                     ,@LoggedInUserId = ?
                                     ,@RoleIds = ?",
                                     
                                 array($profileId
                                     ,auth()->user()->id
                                     ,implode(', ', $request->json()->all())
         )); 
     }

     public function delete($id)
     {
        DB::statement("EXEC slt.pr_Profile_Delete
                     @Id = ?
                    ,@LoggedInUserId = ?"
                    ,array($id, auth()->user()->id));
     }

     public function overrideUserProfileId($id)
     {
        DB::statement("EXEC slt.pr_User_ProfileUpsert
                      @UserId = ?
                     ,@ProfileId = ?"
                ,array(auth()->user()->id, $id));  
     }

}