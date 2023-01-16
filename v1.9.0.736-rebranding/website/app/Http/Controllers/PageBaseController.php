<?php

namespace App\Http\Controllers;

use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;
use App\Resource;

class PageBaseController extends Controller
{

    protected $allowed_mimes = ['jpg','jpeg','png','svg'];
    protected $category = null;

    public function changeCategory($category) {
        $this->category = $category;
    }

    /**
     * Display a listing of the resource by category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::where('category', $this->category)->orderBy('position')->get();

        if($pages->count() == 0) {
            return redirect('/admin/pages')->with('warning', 'The '.$this->category. ' category does not exist, please select a new page category.');
        }
        
        return view('admin.pages.index-category', compact('pages'));
    }

    /**
     * Create a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:vw_page,title',            
            'content' => ['sometimes','required'],
            'thumbnail' => 'required|mimes:'.implode (",", $this->allowed_mimes),
        ]);

        $path = $request->file('thumbnail')->store('pages', 'physical-storage');

        Page::create([
            'thumbnail' => AssetHelper::ToUrl($path),
            'title' => $request->title,
            'category' => $this->category,
        ]);

        return back()->with('success', 'New page has been created successfully');
    }

     /**
     * Creates a page asset.
     *
     * @param \Illuminate\Http\Request $request
     * @param String $type
     * @return \Illuminate\Http\Response
     */
    public function createAsset(Request $request, $type) 
    {
        $page = Page::where('title', $type)->first();
        if($page->category != $this->category) {
            return abort(500);
        }

        $resource = new ResourceBaseController();
        $resource->changeType($this->category);
        return $resource->create($request);
    }

    /**
     * Updates a page asset.
     *
     * @param \Illuminate\Http\Request $request
     * @param String $type
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */
    public function updateAsset(Request $request, $type, $id)
    {
        $page = Page::where('title', $type)->first();
        if($page->category != $this->category) {
            return abort(500);
        }

        $resource = Resource::findOrFail($id);
        if($resource->belongs_to != $page->id) {
            return abort(500);
        }

        $resource = new ResourceBaseController();
        $resource->changeType($this->category);
        return $resource->update($request, $id);
    }

    /**
     * Deletes a page asset.
     *     
     * @param String $type
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */
    public function deleteAsset($type, $id) 
    {
        $page = Page::where('title', $type)->first();
        if($page->category != $this->category) {
            return abort(500);
        }

        $resource = Resource::findOrFail($id);
        if($resource->belongs_to != $page->id) {
            return abort(500);
        }

        $resource = new ResourceBaseController();
        $resource->changeType($this->category);
        return $resource->delete($id);
    }

     /**
     * Reorders page assets.
     *     
     * @param String $type     
     * @return \Illuminate\Http\Response
     */
    public function reorderAsset(Request $request, $type) 
    {
        $page = Page::where('title', $type)->first();
        if($page->category != $this->category) {
            return abort(500);
        }

        $resource = new ResourceBaseController();
        $resource->changeType($this->category);
        return $resource->reorder($request);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        if($page->category != $this->category) {
            return abort(500);
        }

        if( $request->file('thumbnail') ){
            $request->validate([
                'thumbnail' => 'sometimes|required|mimes:'.implode (",", $this->allowed_mimes),
            ]);

            Storage::disk('physical-storage')->delete($page->thumbnailStoragePath());

            $page->update([
                'thumbnail' => AssetHelper::ToUrl($request->file('thumbnail')->store('pages', 'physical-storage'))
            ]);
        }

        $page->update($request->validate([
            'title' => 'sometimes|required|unique:vw_page,title,'.$page->id,
            'content' => 'sometimes|required'
        ]));

        if(request()->ajax()) return json_encode(['success' => true, 'message' => 'Page has been updated successfully']);

        return back()->with('success', $request->title.' page has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $page = Page::findOrFail($id);

        if($page->category != $this->category) {
            return abort(500);
        }

        Storage::disk('physical-storage')->delete( $page->thumbnailStoragePath() );

        $page->delete();

        if(request()->ajax()) return json_encode(['success' => true]);

        return back()->with('success', 'Page deleted successfully');
    }

    /**
     * Reorders the pages.
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
            $page = Page::findOrFail($value);
            if($page->category != $this->category) {
                continue;
            }

            $page->update([
                'position' => $key
           ]);
        }

        return json_encode(['success' => true]);
    }
}
