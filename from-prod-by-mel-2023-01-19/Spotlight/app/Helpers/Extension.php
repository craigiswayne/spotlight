<?php
    
    function export_key()
    {
        return 'export-profile-id';
    }

    function start_export($id) {        
        session([export_key() => $id]);       
    }

    function export_profile_id() {        
        return session(export_key());
    }

    function end_export($id) {        
        if(export_profile_id() != $id) {
            return;
        }

        session([export_key() => null]);       
    }

    function is_export() {        
        return (export_profile_id() != null);
    }   

    function is_allowed(Illuminate\Http\Request $request) 
    {
        if(App\Navigation::query()->where('url', '=', $request->getPathInfo())->count() == 0)
        {
            abort(404);
        }
    }
?>