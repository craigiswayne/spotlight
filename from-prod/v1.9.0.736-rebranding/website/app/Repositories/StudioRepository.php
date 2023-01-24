<?php namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class StudioRepository
{

	public function delete($studioId) {
		DB::statement("EXEC slt.pr_Studio_Delete @StudioId = ?", array($studioId));
	}

	
}