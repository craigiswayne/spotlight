<?php

namespace App\Http\Controllers;

use App\GameBase;
use App\GameNew;
use App\GameAsset;
use App\GameStatInfo;
use App\GameStat;
use App\GameStatFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\Validator;
use App\Repositories\GameRepository;

class GameController extends Controller
{
     // space that we can use the repository from
    protected $repo;

    public function __construct(Request $request)
    {
        $this->repo = new GameRepository();
    }

   /**
    * Displays the view game index.
    *
    * @return \Illuminate\Http\Response
    */
	public function index($returnJson = false)
	{
		$games = $this->getGameJson();
		return view('admin.games.index', compact(['games']));
	}


   /**
    * Gets the games grouped by their New and Featured statuses and returns them as a single array
    *
    * @return Array
    */
	private function getGameJson() {
		$games = (object) [];
		$games->newFeaturedGames = GameNew::where([['deleted', 0], ['new', 1], ['featured', 1]])->orderBy('name')->orderBy('rowIndex')->orderBy('columnIndex')->get();
		$games->newNonFeaturedGames = GameNew::where([['deleted', 0], ['new', 1], ['featured', 0]])->orderBy('name')->orderBy('rowIndex')->orderBy('columnIndex')->get();
		$games->notNewFeaturedGames = GameNew::where([['deleted', 0], ['new', 0], ['featured', 1]])->orderBy('name')->orderBy('rowIndex')->orderBy('columnIndex')->get();
		$games->notNewNonFeaturedGames = GameNew::where([['deleted', 0], ['new', 0], ['featured', 0]])->orderBy('name')->orderBy('rowIndex')->orderBy('columnIndex')->get();
		return $games;
	}

    /**
     * Get a list of games.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $new = null;
        if($request->has('new')) {
            $new = ($request->new == 'true') ? 1 : 0;
        }

        $featured = null;
        if($request->has('featured')) {
            $featured = ($request->featured == 'true') ? 1 : 0;
        }

        $studios = [];

        if($request->has('studios')) {
            $studios = explode(',', $request->studios);
        }

        $include = true;
        if($request->has('include') && $request->include == 'false') {
            $include = false;
        }

		$goLiveFilter = null;
		if ($request->has("goLiveFilterStart")) {
			$goLiveFilter["start"] = $request->goLiveFilterStart;
		}
		if ($request->has("goLiveFilterEnd")) {
			$goLiveFilter["end"] = $request->goLiveFilterEnd;
		}

		$sortByMonth = false;
		if ($request->has("sortingType") && $request->sortingType == "Go Live Month") {
			$sortByMonth = true;
		}

        $whereClause = ["deleted = 0"];

        if(isset($new)) {
            array_push($whereClause , "new = {$new}");
        }

        if(isset($featured)) {
            array_push($whereClause , "featured = {$featured}");
        }

        if(count($studios) > 0) {
            $studioList = join(', ', $studios);
            $operator = ($include ? 'IN' : 'NOT IN');
            array_push($whereClause, "studioId {$operator} ({$studioList})");
        }

		if (isset($goLiveFilter)) {
			if (isset($goLiveFilter["start"]) && !isset($goLiveFilter["end"])) {
				array_push($whereClause, "FORMAT (goLiveMonth, 'yyyy-MM') = '" . $goLiveFilter["start"] . "'");
			}
			else if (isset($goLiveFilter["start"]) && isset($goLiveFilter["end"])) {
				array_push($whereClause, "FORMAT (goLiveMonth, 'yyyy-MM') BETWEEN '" . $goLiveFilter["start"] . "' AND '" . $goLiveFilter["end"] . "'");
			}
		}

        if(count($whereClause) > 0) {
			if ($sortByMonth) {
				return GameBase::orderBy('goLiveMonth', 'asc')->orderBy('name')->orderBy('columnIndex')->whereRaw(join(' AND ', $whereClause))->get();
			}
            return GameBase::orderBy('name')->orderBy('columnIndex')->whereRaw(join(' AND ', $whereClause))->get();
        } else {
			if ($sortByMonth) {
				return GameBase::orderBy('goLiveMonth', 'asc')->orderBy('name')->orderBy('columnIndex')->get();
			}
            return GameBase::orderBy('name')->orderBy('columnIndex')->get();
        }
    }

    /**
     * Display the edit game blade.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $game = GameBase::with(['studio', 'thumbnails', 'symbols', 'portraits', 'maths', 'trailers'])->findOrFail($id);

        $maths = (object) [];
        $maths->info =  GameStatInfo::where('gameId', $id)->first();
        $maths->stats =  GameStat::getStats($id);
        $maths->features = GameStatFeature::where('gameId', $id)->orderBy('position')->get();

        $nextGame =  GameBase::getOffetGame($id, 1);
        $previousGame =  GameBase::getOffetGame($id, -1);

        $features = $game->features();

        return view('admin.games.show', compact(['game', 'nextGame', 'previousGame', 'features', 'maths']));
    }

   /**
    * Displays the add game index.
    *
    * @return \Illuminate\Http\Response
    */
    public function addIndex()
    {
        $features = GameBase::newGameFeatures();
        return view('admin.games.add', compact(['features']));
    }

    private function isThumbnailNewActive(Request $request, $assetId) {
        if(!isset($request->thumbnails["active"])) {
            return false;
        }

        return $request->thumbnails["active"] == $assetId;
    }

    private function setThumbnailActive($gameId, ?GameAsset $asset) {
        $currentActiveGameAsset = GameAsset::where([['gameId', $gameId], ['active', 1]]);
        if($currentActiveGameAsset != null) {
            $currentActiveGameAsset->update([
                'active' => false
            ]);
        }

        if($asset != null) {
            $asset->update([
                'active' => true
            ]);
        }
    }

    private function getThumbnailPosition(Request $request, $assetId) {
        if(!isset($request->thumbnails["position"])) {
            return null;
        }

        if(!array_key_exists($assetId, $request->thumbnails["position"])) {
            return null;
        }

        return $request->thumbnails["position"][$assetId];
    }

     /**
     * Creates a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $game = $this->upsert($request, null);
        return json_encode(['id' => $game->id]);
    }

	/**
	 * Update multiple games' IsNew and IsFeatured statuses
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param integer $ids
	 * @return \Illuminate\Http\Response
	 */
	public function updateStatus(Request $request, $ids) {
		$this->repo->updateGameStatus($request, $ids);
		return response()->json(['success' => true, 'games' => $this->getGameJson()]);
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
        $this->upsert($request, $id);
        return;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    private function upsert(Request $request, $id)
    {

        $request->validate([
            'thumbnails' => 'sometimes|required|array',
            'thumbnails.add.*' => 'sometimes|required|mimes:jpg,jpeg,png,svg',
            'thumbnails.update.*' => 'sometimes|required|mimes:jpg,jpeg,png,svg',
            'thumbnails.delete.*' => 'sometimes|required|integer',
            'background' => 'sometimes|required|mimes:jpg,jpeg,png,svg',
            'logo' => 'sometimes|required|mimes:jpg,jpeg,png,svg',
            'character' => 'sometimes|required|mimes:jpg,jpeg,png,svg',
            'featured' => 'sometimes|required|boolean',
            'new' => 'sometimes|required|boolean',
            'mirrorCharacter' => 'sometimes|required|boolean',
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'goLiveMonth' => 'sometimes|date',
        ]);

        if($id == null) {
            $game = GameBase::create([
                'name' => isset($request->name) ? $request->name : null,
                'new' => isset($request->new) ? $request->new : false,
                'featured' => isset($request->featured) ? $request->featured : false,
                'mirrorCharacter' => isset($request->mirrorCharacter) ? $request->mirrorCharacter : false,
                'studioId' =>  isset($request->studioId) ? $request->studioId : null
            ]);

            $id = $game->id;
        } else {
            $game = GameBase::findOrFail($id);
        }


        if($request->has('name')) {
            $game->update(['name' => $request->name]);
        }

        if($request->has('new')) {
            boolval($request->new) ? $game->update(['new' => true]) : $game->update(['new' => false]);
        }

        if($request->has('featured')) {
            boolval($request->featured) ? $game->update(['featured' => true]) : $game->update(['featured' => false]);
        }

        if($request->has('mirrorCharacter')) {
            boolval($request->mirrorCharacter) ? $game->update(['mirrorCharacter' => true]) : $game->update(['mirrorCharacter' => false]);
        }

        if($request->thumbnails) {

            if(!isset($request->thumbnails["add"]) && !isset($request->thumbnails["update"]) && isset($request->thumbnails["active"])) {
                $asset = GameAsset::findOrFail($request->thumbnails["active"]);
                $this->setThumbnailActive($id, $asset);
            }

            if(isset($request->thumbnails["add"])) {
                foreach ($request->thumbnails["add"] as $gameAssetId => $file)
                {

                    if($this->isThumbnailNewActive($request, $gameAssetId)) {
                        $this->setThumbnailActive($id, null);
                    }

                    $newAsset = new GameAsset();
                    $newAsset->gameId = $game->id;
                    $newAsset->assetTypeId = 1;
                    $newAsset->active = $this->isThumbnailNewActive($request, $gameAssetId);
                    $newAsset->url = AssetHelper::ToUrl($file->store('games', 'physical-storage'));
                    $newAsset->position = $this->getThumbnailPosition($request, $gameAssetId) ?? 0;
                    $newAsset->save();
                }
            }

            if(isset($request->thumbnails["update"])) {
                foreach ($request->thumbnails["update"] as $gameAssetId => $file)
                {
                    $asset = GameAsset::findOrFail($gameAssetId);
                    if($asset->gameId != $id) {
                        return response()->json("Invalid thumbnail, cannot update", 400);
                    }

                    Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($asset->url));

                    $query = [];
                    $query['url'] = AssetHelper::ToUrl($file->store('games', 'physical-storage'));

                    $newPosition = $this->getThumbnailPosition($request, $gameAssetId);
                    if($newPosition != null) {
                        $query['position'] = $newPosition;
                    }

                    $asset->update($query);

                    if($this->isThumbnailNewActive($request, $gameAssetId)) {
                        $this->setThumbnailActive($id, $asset);
                    }
                }
            }

            if(isset($request->thumbnails["delete"])) {
                foreach ($request->thumbnails["delete"] as $gameAssetId)
                {
                    $asset = GameAsset::findOrFail($gameAssetId);
                    if($asset->gameId != $id) {
                        return response()->json("Invalid thumbnail, cannot delete", 400);
                    }

                    Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($asset->url));
                    $asset->delete();
                }
            }

            if(isset($request->thumbnails["position"])) {
                foreach ($request->thumbnails["position"] as $gameAssetId => $position)
                {
                    if($gameAssetId < 1) {
                        continue;
                    }

                    $asset = GameAsset::findOrFail($gameAssetId);
                    if($asset->gameId != $id) {
                        return response()->json("Invalid thumbnail, cannot change position", 400);
                    }

                    $asset->update([
                        'position' => $position
                    ]);
                }
            }
        }

        if($request->file('background') ){
            if($game->background != null) {
                Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->background));
            }

            $game->update([
                'background' => AssetHelper::ToUrl($request->file('background')->store('games', 'physical-storage'))
            ]);
        }

        if( $request->file('logo') ){
            if($game->logo != null) {
                Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->logo));
            }

            $game->update([
                'logo' => AssetHelper::ToUrl($request->file('logo')->store('games', 'physical-storage'))
            ]);
        }

        if( $request->file('character') ){
            if($game->character != null) {
                Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->character));
            }

            $game->update([
                'character' => AssetHelper::ToUrl($request->file('character')->store('games', 'physical-storage'))
            ]);
        }

        if($request->studioId != null){
            $request->validate([
                'studioId' => 'integer|exists:vw_Studio,id'
            ]);

            $game->update([
                'studioId' => $request->studioId
            ]);
        }

		if($request->has('goLiveMonth')) {
			$game->update(['goLiveMonth' => $request->goLiveMonth]);
		}

        return $game;


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($ids)
    {
		$this->repo->deleteGames($ids);

		$gameIds = explode(",", $ids);
		for ($i=0; $i < sizeof($gameIds); $i++) {
			$id = $gameIds[$i];
			$game = GameBase::findOrFail($id);

			if($game->logo != null) {
				Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->logo));
			}

			if($game->character != null) {
				Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->character));
			}

			if($game->background != null) {
				Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($game->background));
			}

			GameAsset::where('gameId', $id)->each(function($asset) {
				Storage::disk('physical-storage')->delete(AssetHelper::FromUrl($asset->url) );
				$asset->delete();
			});
		}

        if(request()->ajax()) return json_encode(['success' => true, 'games' => $this->getGameJson()]);
    }

     /**
     * Update layout of games
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function layout(Request $request)
    {
        $data = ['items' => $request->all()];

        $validator = Validator::make($data, [
            'items' => 'required|array'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->first(), 400);
        }

        foreach($request->all() as $key => $value) {
            GameBase::findOrFail($key)->update([
                'columnIndex' => $value['columnIndex'],
                'rowIndex' => $value['rowIndex'],
                'height' => $value['height'],
                'width' => $value['width']
           ]);
        }

        return json_encode(['success' => true]);
    }

    /**
     * Adds a game feature asset
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String  $type
     * @return \Illuminate\Http\Response
     */
    public function addFeatureAsset(Request $request, $type)
    {
        $resource = new ResourceBaseController();
        $resource->changeType($type);
        return $resource->create($request);
    }

    /**
     * Updates a game feature asset
     *
     * @param \Illuminate\Http\Request  $request
     * @param String $type
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */
    public function updateFeatureAsset(Request $request, $type, $id)
    {
        $resource = new ResourceBaseController();
        $resource->changeType($type);
        return $resource->update($request, $id);
    }

     /**
     * Reorders a game's feature assets
     *
     * @param \Illuminate\Http\Request  $request
     * @param String $type
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */
    public function reorderFeatureAssets(Request $request, $type)
    {
        $resource = new ResourceBaseController();
        $resource->changeType($type);
        return $resource->reorder($request);
    }



    /**
     * Deletes a game feature asset
     *
     * @param String $type
     * @param Integer $id
     * @return \Illuminate\Http\Response
     */
    public function deleteFeatureAsset($type, $id)
    {
        $resource = new ResourceBaseController();
        $resource->changeType($type);
        return $resource->delete($id);
    }

    /**
     * Adds a game feature
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addFeature(Request $request, $id)
    {
        $game = GameBase::findOrFail($id);

        $request->validate([
            'feature' => ['regex:/^[a-zA-Z ]*$/']
        ]);

        $feature = str_replace(' ', '-', strtolower($request->feature));

        if( cache('game-features') ){
            $features = json_decode(cache('game-features'));

            if(in_array($feature, $features)) {
                return response()->json("Feature already exists", 400);
            }

            $features[] = $feature;

            cache(['game-features' => json_encode($features)], now()->addMinutes(1));
        }
        else {
            cache(['game-features' => json_encode([$feature])], now()->addMinutes(1));
        }

        return $game->features();
    }

    public function updateNewMaths(Request $request, $id)
    {
        $game = GameBase::findOrFail($id);

        $request->validate([
            'value' => 'required|boolean'
        ]);

        $game->update([
            'newMaths' => $request->value
        ]);
    }

    public function updateStats(Request $request, $id)
    {
        $game = GameBase::findOrFail($id);
        $this->repo->updateGameStats($request, $game);

        $maths = (object) [];
        $maths->info =  GameStatInfo::where('gameId', $id)->first();
        $maths->stats =  GameStat::getStats($id);
        $maths->features = GameStatFeature::where('gameId', $id)->orderBy('position')->get();

        return json_encode($maths);
    }
}
