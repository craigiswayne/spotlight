<?php

namespace App\Http\Controllers;

use App\Page;
use App\Product;
use App\ProductFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;

class ProductController extends Controller
{
    protected $category = 'products';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('name')->with('features')->get();

        $page = Page::where('category','products')->first();

        return view('admin.products.index', compact('products','page'));
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
            'name' => ['required','string'],
            'thumbnail' => 'sometimes|required|mimes:jpg,jpeg,png,svg'
        ]);

        $product = Product::create([
            'name' => $request->name,
        ]);
      
        if($request->thumbnail){
            $product->update([
                'thumbnail' => AssetHelper::ToUrl($request->file('thumbnail')->store('products', 'physical-storage'))
            ]);
        }

        $product->features = [];

        if(request()->ajax()) return json_encode(['success' => true, 'message' => 'New product added successfully', 'product' => $product]);

        return redirect('/admin/products')->with('success', 'Product added successfully');
    }

    public function pageUpdate(Request $request)
    {
        $resource = new PageBaseController();
        $resource->changeCategory($this->category);

        $page = Page::where('category', $this->category)->first();

        return $resource->update($request, $page->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if( $request->file('thumbnail') ){
            $request->validate([
                'thumbnail' => ['sometimes','required','mimes:jpg,jpeg,png,svg'],
            ]);

            Storage::disk('physical-storage')->delete($product->thumbnailStoragePath());

            $product->update([
                'thumbnail' => AssetHelper::ToUrl($request->file('thumbnail')->store('products', 'physical-storage'))
            ]);
        }

        $product->update($request->validate([
            'name' => ['sometimes']
        ]));

        if(request()->ajax()) return json_encode(['success' => true, 'image_path' => $product->thumbnail]);

        return redirect('/admin/products')->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        ProductFeature::where('product_id', $id)->each(function($feature) {
            Storage::disk('physical-storage')->delete(  AssetHelper::FromUrl($feature->icon) );
            $feature->delete();
        });

        Storage::disk('physical-storage')->delete( $product->thumbnailStoragePath() );

        $product->delete();

        if(request()->ajax()) return json_encode(['success' => true, 'message' => ucfirst($product->name).' deleted successfully']);

        return redirect('/admin/products')->with('success', 'Product deleted successfully');
    }
}
