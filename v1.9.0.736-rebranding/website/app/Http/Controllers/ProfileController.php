<?php

namespace App\Http\Controllers;

use App\Navigation;
use App\Profile;
use App\ProfileSettingType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use STS\ZipStream\ZipStream;
use Illuminate\Support\Facades\Config;
use App\Resource;
use App\Repositories\ProfileRepository;
use ZipStream\Option\File as FileOptions;
use ZipStream\Option\Archive as ArchiveOptions;
use App\Jobs\ProcessExport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
Use Exception;
use App\User;
use App\RoleBase;
use App\Helpers\SecureHelper;

class ProfileController extends Controller
{
   // space that we can use the repository from
   protected $repo;

   public function __construct(Request $request)
   {
       $this->repo = new ProfileRepository($request);
   }

    /**
     * Checks if a provided profile name is avilable for use.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkName(Request $request)
    {
        if(!$request->has('name')) {
            $this->notAvailable();
        }

        if($request->has('id')) {

            if(Profile::where([['deleted', '=', 0],['name', '=', $request->name], ['id', '!=', $request->id]])->count() > 0)
            {
                return  $this->notAvailable();
            }

        } else {

            if(Profile::where([['deleted', '=', 0],['name', '=', $request->name]])->count() > 0)
            {
                return  $this->notAvailable();
            }

        }

        return $this->available();
    }

    private function notAvailable() {
        return response()->json(false);
    }

    private function available() {
        return response()->json(true);
    }

    public function get()
    {
        return Profile::with(['owner', 'roles' => function($query) {
            $query->where([['typeId', '<=',  auth()->user()->role->typeId], ['deleted', '=', 0]]);
            $query->orderBy('system', 'DESC')->orderBy('name');
        }])->where([['deleted', '=', 0], ['public', '=', true]])->get();
    }

    public function index()
    {
        $profiles = Profile::with(['owner', 'roles' => function($query) {
            $query->where([['typeId', '<=',  auth()->user()->role->typeId], ['deleted', '=', 0]]);
            $query->orderBy('system', 'DESC')->orderBy('name');
        }])->where('deleted', '=', 0)->where(function($query) {
            $query->where('createdByUserId', '=', auth()->user()->id)
            ->orWhere('public', '=', true);
        })->orderBy('createdUtcDate')->get();

        $user = auth()->user();

        return view('admin.profile.index',  compact('profiles', 'user'));
    }

    public function add()
    {
        $navigations = Navigation::where('deleted', 0)->orderBy('position', 'asc')->get();
        $settings = ProfileSettingType::get();
        $user = auth()->user();
        return view('admin.profile.add', compact('navigations', 'settings', 'user'));
    }

    public function edit($id)
    {
        $config =  $this->repo->getConfig($id);

        $profile = Profile::with('owner')->where([['deleted', 0], ['id', $id]])->where(function($query) {
                    $query->where('createdByUserId', '=', auth()->user()->id)
                        ->orWhere(function($orQuery) {
                            $orQuery->where('public', 'true')
                                    ->where('writeAccess', 'true');
                    });
                })->first();

        if($profile == null) {
            return abort(404);
        }

        $navigations = Navigation::where('deleted', 0)->orderBy('position', 'asc')->get();
        $settings = ProfileSettingType::get();
        $user = auth()->user();

        return view('admin.profile.edit',  compact('id', 'config', 'profile', 'navigations', 'settings', 'user'));
    }

    public function clone($id)
    {
        $config =  $this->repo->getConfig($id);

        $navigations = Navigation::where('deleted', 0)->orderBy('position', 'asc')->get();
        $settings = ProfileSettingType::get();
        $user = auth()->user();

        return view('admin.profile.clone',  compact('config', 'navigations', 'settings', 'user'));
    }

    public function setUserOverride($id)
    {
        $profile = Profile::where([['deleted', 0], ['id', $id]])->where(function($query) {
            $query->where('createdByUserId', '=', auth()->user()->id)
            ->orWhere('public', '=', true);
        })->first();

        if($profile == null) {
            return response()->json("Invalid profile", 400);
        }

        auth()->user()->overrideProfileId = $id;
        auth()->user()->save();
        return auth()->user();
    }

    public function clearUserOverride()
    {
        auth()->user()->overrideProfileId = null;
        auth()->user()->save();
        return auth()->user();
    }

    public function delete($id)
    {
        $profile = Profile::where([['deleted', 0], ['id', $id]])->where('createdByUserId', '=', auth()->user()->id)->first();

        if($profile == null) {
            return response()->json("Invalid profile", 400);
        }

        if($profile->createdByUserId == null) {
            return response()->json("Default profile cannot be deleted", 400);
        }

        $this->repo->delete($id);
    }

    public function currentDownload(ArchiveOptions $archiveOptions,  FileOptions $fileOptions)
    {
        return $this->download(auth()->user()->profile->id, $archiveOptions, $fileOptions);
    }

    public function download($profileId, ArchiveOptions $archiveOptions,  FileOptions $fileOptions)
    {
        $profile = Profile::where([['deleted', '=', 0],['id', '=', $profileId]])->where(function($query) { $query->where('createdByUserId', '=', auth()->user()->id)->orWhere('public', '=', 1);})->first();
        abort_if(($profile == null), 403);

        $stream = new ZipStream($archiveOptions, $fileOptions);


        $core = Config::get('download.include');
        $assets = $this->repo->assets($profileId, auth()->user()->id);
        $coreAssets = array_merge($assets, $core);

        foreach ($coreAssets as $item)
        {
            $this->getRecursiveFiles($item,  function($path, &$results) {
                $source = str_replace("\\", "/",  $path);
                $storageRoot = str_replace("\\", "/",  env('STORAGE_PATH'));
                $webRoot = str_replace("\\", "/",  $_SERVER['DOCUMENT_ROOT']);

                if($this->endsWith($storageRoot, "/")) {
                    $storageRoot = substr($source, 0, strlen($storageRoot)-1);
                }

                if($this->endsWith($webRoot, "/")) {
                    $webRoot = substr($source, 0, strlen($webRoot)-1);
                }

                if($this->startsWith($source, $storageRoot)) {
                    $target = "/assets/storage".substr($source, strlen($storageRoot));
                } else if($this->startsWith($source, $webRoot)) {
                    $target = substr($source, strlen($webRoot));
                } else {
                    $target = $source;
                }


                $results[$source] = $target;

            }, $files);
        }

        $exportedFileName = 'spotlight-'.WEBSITE_VERSION.'_'.urlencode(str_replace(' ', '_', $profile->name)).'.zip';


        $stream = $stream->create($exportedFileName,  $files);
        ProcessExport::dispatchNow($stream, $profileId, auth()->user()->id);

        return $stream->response();
    }

    function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    private function getRecursiveFiles($dir, $prepareCallback, &$results = array()) {

        if($this->startsWith($dir, "/assets/storage/")) {
            $dir = str_replace("/assets/storage", str_replace("\\", "/", env('STORAGE_PATH')) , $dir);
        }

        $dir = ltrim($dir, '/');

        if (pathinfo($dir, PATHINFO_EXTENSION))
        {
            if(is_file($dir)) {
                $prepareCallback($dir, $results);

            }
            return;
        }

        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . '/' . $value);
            if (!is_dir($path)) {
                $prepareCallback($path, $results);
            } else if ($value != "." && $value != "..") {
                $this->getRecursiveFiles($path, $prepareCallback, $results);
            }
        }

        return $results;
    }

     /**
     * Create a new profile in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return $this->upsert($request, null);
    }

     /**
     * Create a new profile in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->upsert($request, $id);
    }

    /**
     * Create a new profile in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function upsert(Request $request, $id)
    {
        $data = $request->all();
        if(!$data) {
            return response()->json("The request was empty", 400);
        }

        $validator = Validator::make($data, [
            'info' => ['required'],
            'info.name' => ['required', 'string', 'min:5', 'max:128'],

            'studio' => ['required'],

            'game' => ['required'],

            'navigation' => ['required'],
            'settings' => ['required'],
        ], [
            'info.required' => 'Request is missing the info contract',
            'info.name.required' => 'Name of profile is required',
            'info.name.min' => 'Name cannot be less than five characters',
            'info.name.max' => 'Name cannot be more than 128 characters',

            'studio.required' => 'Request is missing the studio contract',

            'game.required' => 'Request is missing the game contract',

            'navigation.required' => 'Request is missing the navigation contract',
            'navigation.*.id.required' => 'Navigation contract item is missing an id',
            'navigation.*.value.required' => 'Navigation contract item is missing a value',

            'settings.required' => 'Request is missing the settings contract',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        if($request->input('studio.type') != null && ((!$request->filled('studio.items') || count($request->input('studio.items')) == 0))) {
            return response()->json("Studio list required when activating the filter", 400);
        }

        if($request->input('game.type') != null && ((!$request->filled('game.items') || count($request->input('game.items')) == 0))) {
            return response()->json("Game list required when activating the filter", 400);
        }

        if($id != null) {
            $profile = Profile::where([['deleted', 0], ['id', $id]])->where(function($query) {
                $query->where('createdByUserId', '=', auth()->user()->id)
                      ->orWhere(function($orQuery) {
                          $orQuery->where('public', 'true')
                                  ->where('writeAccess', 'true');
               });
            })->first();

            if($profile == null) {
                return response()->json("Invalid profile", 400);
            }

            if($profile->createdByUserId == null) {
                return response()->json("Default profile cannot be edited", 400);
            }

            if($request->input('info.visibility') != $profile->public && !SecureHelper::hasAdminAccess('Profiles|Visibility')) {
                return response()->json("You are not able to change the visibility of a profile", 400);
            }
        } else {
            if($request->input('info.visibility') && !SecureHelper::hasAdminAccess('Profiles|Visibility')) {
                return response()->json("You are not allowed to mark a profile as public", 400);
            }
        }

        $this->repo->upsert($request, $id);

        return;
    }

    public function roles($id)
    {
        $profile = Profile::where([['deleted', 0], ['id', $id]])->where(function($query) {
            $query->where('createdByUserId', '=', auth()->user()->id)
            ->orWhere('public', '=', true);
        })->first();

        if($profile == null) {
            return response()->json("Invalid profile", 400);
        }

        return RoleBase::where([['deleted', '=' , 0],['profileId', $id],['typeId', '<=',  auth()->user()->role->typeId]])->get();
    }

    public function assign($id, Request $request)
    {
        $profile = Profile::where([['deleted', 0], ['id', $id]])->where(function($query) {
            $query->where('createdByUserId', '=', auth()->user()->id)
            ->orWhere('public', '=', true);
        })->first();

        if($profile == null) {
            return response()->json("Invalid profile", 400);
        }

        $data = $request->all();
        if(!$data) {
            return response()->json("The request was empty", 400);
        }
        $data = ['data' => $data];

        $validator = Validator::make($data, [
            'data' => 'required|array',
            'data.*' => 'exists:vw_role,id'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        $users = $this->repo->assignRoles($request, $id);
        return $users;
    }
}
