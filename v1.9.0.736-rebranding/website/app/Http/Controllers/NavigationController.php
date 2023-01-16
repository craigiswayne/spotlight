<?php

namespace App\Http\Controllers;

use App\Navigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;
use App\Helpers\SecureHelper;

class NavigationController extends Controller
{   
    protected $allowed_mimes = ['jpg','jpeg','png','svg','mp4'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $navigations = Navigation::where('deleted', 0)->orderBy('position')->get();
        return view('admin.navigation.index',  compact('navigations'));
    }

     /**
     * Reorders the resource.
     *
     * @param  \Illuminate\Http\Request  $request     
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'array'
        ]);
        
        foreach ( $request->items as $key => $value) {
           Navigation::findOrFail($value)->update([
                'position' => $key
           ]);
        }

        return json_encode(['success' => true]);
    }

    /**
     * Deletes a resource
     *           
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */    
    public function delete($id)
    {   
        $resource = Navigation::findOrFail($id);
       
        $old = AssetHelper::FromUrl($resource->thumbnail);
        session()->flash('asset', $old);
        Storage::disk('physical-storage')->delete($old);
        $resource->update(['deleted' => true]);

        if(request()->ajax()) return json_encode(['success' => true]);
        return back()->with('success', 'Navigation deleted successfully');
    }

    /**
     * Update the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $resource = Navigation::findOrFail($id);

        $request->validate([
            'asset' => 'sometimes|required|mimes:'.implode (",", $this->allowed_mimes),
            'href' => 'nullable|string',
        ]);
        
        if( $request->file('asset') ){
            
            $old = AssetHelper::FromUrl($resource->thumbnail);
            Storage::disk('physical-storage')->delete($old);
           
            $path = $request->file('asset')->store('navigation-cards', 'physical-storage');
           
            $url = AssetHelper::ToUrl($path);
            session()->flash('asset', $url);
            $resource->thumbnail = $url;
           
        }
             
        $resource->save();

        return back()->with('success', ucwords(str_replace('-', ' ', $resource->type)).' updated successfully');
    }

}
