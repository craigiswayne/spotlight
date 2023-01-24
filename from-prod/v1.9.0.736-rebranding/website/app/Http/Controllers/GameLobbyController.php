<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GameLobbyController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client =  new Client([
			'verify' => false
        ]);
    }
    
    public function getGames(Request $request, $year)
    {        
        if(!$request->ajax()) {
            return;
        }

        $response = $this->client->post('https://microgaming.co.uk/api/sitecore/GameSearchApi/GetGames',[
            "json" => [
                "id" => "{84B4F7B8-E1BE-4A2A-84DB-AC164A3FF2C9}",
                'pageSize' => 200,
                "filters" => [[
                    "Key" => "release_date_year_month",
                    "Value" => $year,
                ]]
            ]
        ]);
        
        return $response->getBody()->getContents();

    }

    public function getDemo(Request $request, $id)
    {       
        if(!$request->ajax()) {
            return;
        }

        $response = $this->client->get('https://microgaming.co.uk/api/sitecore/GameSearchApi/GetDemoPlay?id='.$id);

        return $response->getBody()->getContents();
    }

    public function search(Request $request)
    {        
        if(!$request->ajax()) {
            return;
        }

        $request->validate([
            'term' => ['required', 'string', 'min:1', 'max:50'],
        ]);

        $response = $this->client->post('https://microgaming.co.uk/api/sitecore/GameSearchApi/GetGames',[
            "json" => [
                "id" => "{84B4F7B8-E1BE-4A2A-84DB-AC164A3FF2C9}",
                "pageSize" => 200,
                "searchTerm" => $request->term               
            ]
        ]);
        
        return $response->getBody()->getContents();

    }



}
