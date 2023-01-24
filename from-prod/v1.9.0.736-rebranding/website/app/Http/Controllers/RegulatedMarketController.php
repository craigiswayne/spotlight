<?php

namespace App\Http\Controllers;

use App\RegulatedMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegulatedMarketController extends Controller
{

    protected $logoResourceType = 'regulated-market-logos';

    public function addLogo(Request $request) {
        $resource = new ResourceBaseController();
        $resource->changeType($this->logoResourceType);
        return $resource->create($request);
    }

    public function deleteLogo($id) {
        $resource = new ResourceBaseController();
        $resource->changeType($this->logoResourceType);
        return $resource->delete($id);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $markets = RegulatedMarket::orderBy('country_name')->get();

        $countries = collect(json_decode(Storage::get('/assets/country_list.json'), true));

        return view('admin.regulated-markets.index', compact(['markets', 'countries']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $market = RegulatedMarket::create($request->validate([
            'country_name' => ['required'],
            'country_code' => ['required', 'size:2', 'unique:vw_RegulatedMarket,country_code'],
            'slot_games' => ['nullable', 'integer'],
            'launch_date' => ['nullable', 'integer'],
            'info_text' => ['nullable', 'string'],
        ]));

        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/markets/'.$market->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $regulatedMarket = RegulatedMarket::findOrFail($id);

        $countries = collect(json_decode(Storage::get('/assets/country_list.json'), true));
        
        return view('admin.regulated-markets.show', compact(['regulatedMarket', 'countries']));
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
        $regulatedMarket = RegulatedMarket::findOrFail($id);

        $regulatedMarket->update($request->validate([
            'country_name' => ['required'],
            'country_code' => ['required', 'size:2', 'unique:vw_regulatedmarket,country_code,'.$regulatedMarket->id],
            'slot_games' => ['nullable', 'integer'],
            'launch_date' => ['nullable', 'integer'],
            'info_text' => ['nullable', 'string'],
        ]));

        if(request()->ajax()) return json_encode(['success' => true]);

        return back()->with('success', 'Regulated market updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $regulatedMarket = RegulatedMarket::findOrFail($id);

        $regulatedMarket->delete();

        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/markets')->with('success', 'Market deleted successfully');
    }
}
