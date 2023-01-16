<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Profile;
use App\Repositories\ProfileRepository;
use App\StorageAsset;

class CleanOrphanedAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mgs:clean-orphaned-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Locates and removes orphaned assets';

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
        $this->info('Starting '.$this->signature);

        $files = [];        
        $this->getRecursiveFiles(env('STORAGE_PATH'),  function($path, &$results) {
            $source = strtolower(str_replace("\\", "/",  $path));
            array_push($results, $source);
        }, $files);

        if(!$files) {
            $files = [];
        }

        $assets = StorageAsset::get()->pluck('url');       
        
        $storageRoot = str_replace("\\", "/",  env('STORAGE_PATH'));
        if(!$this->endsWith($storageRoot, "/")) {
            $storageRoot = $storageRoot . '/';
        }

        if(!$assets || count($assets) == 0) {
            // If we get an empty list of assets from database, then we can assume the site it getting setup.
            $files = [];
        }

        $filesCountBefore = count($files);

        foreach ($assets as $asset)        
        {
            if(!$this->startsWith($asset, '/assets/storage/')) {                
                continue;
            }

            $path = strtolower(str_replace('/assets/storage/', $storageRoot, $asset));
            
            if (($key = array_search($path, $files)) !== false) {                
                unset($files[$key]);
            }            
        }

       

        $this->comment('');
        if(count($files) == 0) {
            $this->comment('NO ORPHAN ASSET FILES WERE FOUND');
        } else if(count($files) == $filesCountBefore) {
            $this->comment('ALL '.count($files).' FILES APPEAR TO BE ORPHANED - MIGHT BE POINTING AT WRONG FOLDER, NO CLEAN-UP WILL BE PERFORMED');            
        } else {
            $this->comment('FOUND '.count($files).' ORPHANED ASSET FILE(S)');

            $totalSize = 0;
            $deletedFolderCount = 0;

            foreach ($files as $file)        
            {
                $size = filesize($file);

                $this->info($file);

                try {
                    unlink($file);
                    $totalSize += $size;
                } catch(\Exception $ex) { 
                    $this->error("Unable to delete " . $file . " - " . $ex->getMessage());
                    continue;
                } 
            }

            $this->comment('');
            $this->comment('DELETING EMPTY DIRECTORIES');
            $deletedFolderCount = $this->removeUnusedDirectories(env('STORAGE_PATH'));

            $this->comment('');
            $this->comment('DELETED - ' . count($files). ' FILES | '.$deletedFolderCount.' DIRECTORIES | '. number_format(($totalSize/(1024*1024*1024)), 3, '.', '') .'GB');        
        }
           
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

    function removeUnusedDirectories($path, $counter = 0) {
        $dirs = glob($path . "/*", GLOB_ONLYDIR);

        if(!$dirs || count($dirs) == 0) {
            return;
        }

        foreach($dirs as $dir) {
            if($dir == '.git' || !file_exists($dir)) {
                continue;
            }
            $files = glob($dir . "/*");
            $innerDirs = glob($dir . "/*", GLOB_ONLYDIR);
            if(empty($files)) {
                try { 
                    if(!rmdir($dir))
                        $this->error("Unable to delete " . $dir);
                    else {
                        $this->info($dir);
                        $counter++;
                    }
                } 
                catch(\Exception $ex) { 
                    $this->error("Unable to delete " . $dir . " - " . $ex->getMessage());
                    continue;
                } 
            } elseif(!empty($innerDirs)) {
                $oldCount = $counter;
                $counter = $this->removeUnusedDirectories($dir, $counter);

                // After removing children, recheck this directory, it might be empty and can be removed.
                if($oldCount != $counter) {
                    $counter = $this->removeUnusedDirectories($path, $counter);
                }
            }
        }

        return $counter;

    }
}
