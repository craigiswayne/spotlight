<?php

namespace App\Http\Controllers;

use App\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;

class ResourceBaseController extends Controller
{
    protected $allowed_mimes = ['jpg','jpeg','png','svg','mp4'];
    protected $asset_type = 'image';
    protected $type = null;
    protected $securable = null;

    public function changeType($type) {
        $this->type = $type;
    }

    public function index()
    {
        $type = $this->type;
        if($type == null) {
            return abort(500);
        }

        abort_unless(in_array($type, Resource::types()), 404);

        $resources = Resource::where('type', $type)->orderBy('position', 'asc')->get();
        
        $file_types = '.'.implode (",.", $this->allowed_mimes);

        $asset_type = $this->asset_type;

        $thumbnail_size = null;

        $type == 'showreel' ? $thumbnail_size = '100%' : null;
                
        if ($type == 'third-party-providers'){
            $providers = $resources->groupBy('belongs_to')->sortKeysDesc();
            
            return view('admin.resource-third-party-providers', compact(['providers','type','file_types','asset_type', 'thumbnail_size']));
        }

        $securable = $this->securable;
        return view('admin.resource-default', compact(['securable', 'resources','type','file_types','asset_type', 'thumbnail_size']));
    }

    public function create(Request $request)
    {
        $type = $this->type;
        if($type == null) {
            return abort(500);
        }
        
        abort_unless(in_array($type, Resource::types()), 404);

        $request->validate([
            'asset.*' => 'required|mimes:'.implode (",", $this->allowed_mimes),
            'href' => 'nullable|string',
            'belongs_to' => 'sometimes|required',
        ]);
        
        $lastPosition = Resource::where([['type', $type], ['belongs_to', $request->belongs_to]])->max('position');
        if(!$lastPosition) {
            $lastPosition = 0;
        }
        
        $resources = [];
        foreach ($request->asset as $file)
        {
            $lastPosition += 1;
            $path = $file->store($type, 'physical-storage');
            $url = AssetHelper::ToUrl($path);           
            session()->flash('asset', $url);    
			
			/*
            if($type == 'video') {				
				$width => null;
                $height => null;
			}
			else {
				$dimensions = getimagesize($file);
				$width => $dimensions[0];
                $height => $dimensions[1];
			}	
	       */
		   
			$dimensions = getimagesize($file);
            $resource = Resource::create([
                'asset_path' => $url,
                'type' => $type,
                'href' => $request->href,
                'belongs_to' => $request->belongs_to ?? null,
                'position' => $lastPosition,
                'size' => $file->getSize(),
                'width' => null,                     //$dimensions[0],
                'height' => null                   //$dimensions[1]
            ]);      
            array_push($resources, $resource);     
        }

        if(request()->ajax()) {
            return json_encode(['success' => true, 'items' => $resources]);
        }

        return back()->with('success', 'New '.ucwords(str_replace('-', ' ', $type)).' created successfully');
    }


    public function delete($id)
    {
        $type = $this->type;
        if($type == null) {
            return abort(500);
        }

        $resource = Resource::findOrFail($id);
        if($resource->type != $type) {
            return abort(500);
        }

        $old = AssetHelper::FromUrl($resource->asset_path);

        session()->flash('asset', $old);

        Storage::disk('physical-storage')->delete($old);

        $resource->delete();

        if(request()->ajax()) return json_encode(['success' => true]);

        return back()->with('success', ucwords(str_replace('-', ' ', $resource->type)).' deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $type = $this->type;
        if($type == null) {
            return abort(500);
        }

        $resource = Resource::findOrFail($id);
        if($resource->type != $type) {
            return abort(500);
        }

        $request->validate([
            'asset' => 'sometimes|required|mimes:'.implode (",", $this->allowed_mimes),
            'href' => 'nullable|string',
        ]);


        if($request->file('asset')){
            $old = AssetHelper::FromUrl($resource->asset_path);
            Storage::disk('physical-storage')->delete($old);
                        
            $path = $request->file('asset')->store($resource->type, 'physical-storage');
      
            $dimensions = getimagesize($request->file('asset'));

            $url = AssetHelper::ToUrl($path);            
            session()->flash('asset', $url);
                        
            $resource->update([
                'asset_path' => $url,
                'size' => $request->file('asset')->getSize(),
                'width' => $dimensions[0],
                'height' => $dimensions[1]
           ]);           

        }

        $resource->href = $request->href;
        
        $resource->save();

        return back()->with('success', ucwords(str_replace('-', ' ', $resource->type)).' updated successfully');
    }

    public function reorder(Request $request)
    {
        $type = $this->type;
        if($type == null) {
            return abort(500);
        }

        $request->validate([
            'items' => 'array'
        ]);

        foreach ( $request->items as $key => $value) {
            $resource = Resource::findOrFail($value);
            if($resource->type != $type) {
                continue;
            }
            
            $resource->update([
                 'position' => $key
            ]);
         }


        return json_encode(['success' => true]);
    }
}
