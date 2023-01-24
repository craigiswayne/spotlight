<?php

namespace App\Http\Controllers;

use App\Page;
use App\Http\Controllers\PageBaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;

class PlayItForwardController extends PageBaseController
{
    public function __construct()
    {
        $this->category = 'play-it-forward';      
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

    public function reorder(Request $request)
    {
        return parent::reorder($request);
    }
}
