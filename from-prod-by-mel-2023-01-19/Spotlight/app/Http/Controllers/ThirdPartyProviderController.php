<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThirdPartyProviderController extends ResourceBaseController
{    
    public function __construct()
    {
        $this->type = 'third-party-providers';
        $this->allowed_mimes = ['jpg','jpeg','png','svg','mp4'];
        $this->asset_type = 'image';       
        $this->securable = 'Third Party Providers';      
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return parent::index();        
    }

    public function create(Request $request)
    {
        return parent::create($request);
    }

    public function delete($id)
    {
        return parent::delete($id);
    }

    public function update(Request $request, $id)
    { 
        return parent::delete($request, $id);
    }

    public function reorder(Request $request)
    {
        return parent::reorder($request);
    }
}
