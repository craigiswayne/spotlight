<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShowreelController extends ResourceBaseController
{    
    public function __construct()
    {
        $this->type = 'showreel';
        $this->allowed_mimes = ['mp4'];
        $this->asset_type = 'video'; 
        $this->securable = 'Showreel';
    }

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
