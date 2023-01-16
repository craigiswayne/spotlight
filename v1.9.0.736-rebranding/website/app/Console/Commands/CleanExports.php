<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mgs:clean-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently deletes all application exports older than a day while keeping the latest 4 files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = Storage::disk('export-packages')->files();

        if(sizeof($files) <= 4) return;

        foreach (Storage::disk('export-packages')->files() as $key => $file) {
            if(now()->subDays(1)->timestamp > Storage::disk('export-packages')->lastModified($file)){
                if (Storage::disk('export-packages')->delete($file)){
                    $this->info("$file deleted");
                }
                else $this->error("$file was unable to be deleted");
            }
        }
    }
}
