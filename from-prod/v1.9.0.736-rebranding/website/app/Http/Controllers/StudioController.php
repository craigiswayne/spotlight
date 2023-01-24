<?php

namespace App\Http\Controllers;

use App\Studio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;
use App\Http\Requests\StudioUpdateRequest;
use App\Repositories\StudioRepository;

class StudioController extends Controller
{
    protected $repo;
    public function __construct(Request $request)
    {
        $this->repo = new StudioRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //dd('test');
        $studios = Studio::orderBy('position')->paginate(250);

        return view('admin.studios.index', compact('studios'));
    }

     /**
     * Returns a list of studios.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        return Studio::orderByRaw('CASE WHEN name IS NULL THEN 1 ELSE 0 END, name')->orderBy('position')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:jpg,jpeg,png,svg',
        ]);

        Studio::create([
            'image' => AssetHelper::ToUrl($request->file('image')->store('studios', 'physical-storage')),
        ]);

        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/studios')->with('success', 'Studio added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function show(Studio $studio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function edit(Studio $studio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(StudioUpdateRequest $request, $id)
    {
       
        $studio = Studio::findOrFail($id);

        if($request->image){
            Storage::disk('physical-storage')->delete($studio->imageStoragePath());

            $studio->update([
                'image' => AssetHelper::ToUrl($request->file('image')->store('studios', 'physical-storage'))
            ]);
        }

        if($request->video){
            Storage::disk('physical-storage')->delete($studio->videoStoragePath());

            $studio->update([
                'video' => AssetHelper::ToUrl($request->file('video')->store('studios', 'physical-storage'))
            ]);
        }

		if ($request->studio_name) {
			$studio->update([
				'name' => $request->studio_name
			]);
		}

        if(request()->ajax()) return json_encode(['success' => true, 'image_path' => $studio->image, 'video_path' => $studio->video, 'studio_name' => $studio->name]);

        return back()->with('success', 'Studio updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $studio = Studio::findOrFail($id);

        $this->repo->delete($id);

        Storage::disk('physical-storage')->delete($studio->imageStoragePath());

        if($studio->video != null) {
            Storage::disk('physical-storage')->delete($studio->videoStoragePath());
        }
               
        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/studios')->with('success', 'Studio deleted successfully');
    }


    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'array'
        ]);

        foreach ( $request->items as $key => $value) {
           Studio::findOrFail($value)->update([
                'position' => $key
           ]);
        }

        return json_encode(['success' => true]);
    }
}
