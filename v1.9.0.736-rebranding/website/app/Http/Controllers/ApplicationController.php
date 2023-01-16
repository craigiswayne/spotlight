<?php

namespace App\Http\Controllers;

use App\GameNew;
use App\GameBase;
use App\Page;
use App\Studio;
use App\Product;
use App\Resource;
use App\Navigation;
use App\ProfileSetting;

use App\RegulatedMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{       
    public function index()
    {
        $navigations = Navigation::query();                      
        return view('app', compact('navigations'));
    }
    
    public function showreel(Request $request)
    { 
        is_allowed($request);     

        $videos = Resource::where('type', 'showreel')->orderBy('position')->get();
        return view('showreel', compact('videos'));
    }


    public function thirdPartyProviders(Request $request)
    {
        is_allowed($request);

        $resources = Resource::where('type', 'third-party-providers')->orderBy('position')->get();
        $providers = $resources->groupBy('belongs_to')->sortKeysDesc();

        return view('third-party-providers', compact('providers'));
    }


    public function allGames(Request $request)
    {
        is_allowed($request);

        $studios = Studio::query();
        $games = GameBase::gamesJson(null, null) ?? '[]';

        return view('all-games', compact('games', 'studios'));
    }


    public function gamesNew(Request $request)
    {        
        is_allowed($request);

        $newFeaturedGames = GameNew::gamesJson(true, true) ?? '[]';
        $newNonFeaturedGames = GameNew::gamesJson(true, false) ?? '[]';
        $featuredGames = GameNew::gamesJson(false, true) ?? '[]';

        return view('games-new', compact('newFeaturedGames', 'newNonFeaturedGames', 'featuredGames'));
    }

    public function playItForward(Request $request)
    {
        is_allowed($request);

        $pages = Page::where('category', 'play-it-forward')->orderBy('position')->get();

        $pages = $pages->filter(function ($page) {
            $page->assets = $page->resources();
            return $page;
        });

        return view('play-it-forward', compact('pages'));
    }


    public function regulatedMarkets(Request $request)
    {
        is_allowed($request);

        $markets = RegulatedMarket::all();

        $countries = [];

        $details = [];

        foreach ($markets as $market) {
            $countries[$market->country_code] = $market->launch_date == null ? 0 : 1;
            $details[$market->country_code] = [
                'slot_games' => $market->slot_games,
                'launch_date' => $market->launch_date,
                'info_text' => $market->info_text,
                'logos' => $market->logos
            ];
        }

        $countries = collect($countries);

        $details = collect($details);
        
        return view('regulated-markets', compact('countries','details'));
    }


    public function products(Request $request)
    {
       is_allowed($request);

        $products = Product::with('features')->get();
        
        $page = Page::where('category','products')->first();

        return view('products', compact('products', 'page'));
    }


    public function gamesLobby(Request $request)
    {
        is_allowed($request);
        
        return view('games-lobby');
    }    
}
