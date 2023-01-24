<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;

class ProductFeatureController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product)
    {
        $feature = $product->features()->create($request->validate([
            'name' => ['required','string']
        ]));

        if(request()->ajax()) return json_encode(['success' => true, 'message' => ucfirst($feature->name).' feature created successfully', 'feature' => $feature]);

        return redirect('/admin/products')->with('success', 'Feature created successfully');
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
        $productFeature = ProductFeature::findOrFail($id);

        if( $request->file('icon') ){
            $request->validate([
                'icon' => ['required','mimes:jpg,jpeg,png,svg'],
            ]);

            Storage::disk('physical-storage')->delete($productFeature->iconStoragePath());

            $productFeature->update([
                'icon' => AssetHelper::ToUrl($request->file('icon')->store('product-features', 'physical-storage'))
            ]);
        }

        $productFeature->update($request->validate([
            'name' => ['sometimes', 'required', 'string'],
            'content' => ['sometimes', 'required', 'string'],
        ]));

        if(request()->ajax()) return json_encode(['success' => true, 'message' => ucfirst($productFeature->name).' feature updated successfully', 'image_path' => $productFeature->icon]);

        return redirect('/admin/products')->with('success', 'Feature updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $productFeature = ProductFeature::findOrFail($id);

        Storage::disk('physical-storage')->delete( $productFeature->iconStoragePath() );

        $productFeature->delete();

        if(request()->ajax()) return json_encode(['success' => true, 'message' => ucfirst($productFeature->name).' feature deleted successfully']);

        return redirect('/admin/products')->with('success', 'Feature deleted successfully');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'array'
        ]);

        foreach ( $request->items as $key => $value) {
           ProductFeature::findOrFail($value)->update([
                'position' => $key
           ]);
        }

        return json_encode(['success' => true]);
    }
}
