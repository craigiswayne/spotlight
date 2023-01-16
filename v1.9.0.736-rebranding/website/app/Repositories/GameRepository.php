<?php namespace App\Repositories;

use App\RoleBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
Use Exception;

class GameRepository
{

    public function updateGameStats(Request $request, $game)
    {
            DB::statement("EXEC slt.pr_GameStats_Upsert
                                     @Id = ?
                                    ,@StatsJson = ?",

                                array($game->id
                                    ,json_encode($request->all())
                                )
                );
    }

	public function deleteGames($gameIds) {
		DB::statement("EXEC slt.pr_Game_Delete @GameIds = ?", array($gameIds));
	}

	public function updateGameStatus(Request $request, $gameIds) {
		DB::statement("EXEC slt.pr_Game_Status_Update
									 @Ids = ?
									,@IsNew = ?
									,@IsFeatured = ?",
								array(
									 $gameIds
									,$request->new
									,$request->featured)
		);
	}

}