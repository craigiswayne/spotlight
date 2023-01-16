<?php

namespace App\Jobs;

use App\Podcast;
use App\AudioProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\Export\Exporter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use STS\ZipStream\ZipStream;
use App\Navigation;

class ProcessExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $profileId;
    private $userId;

    /** @var App\Helpers\Exporter */
    private $exporter;
     /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ZipStream $stream, $profileId, int $userId)
    {        
        $this->exporter = new Exporter($stream);
        $this->profileId = $profileId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @param Exporter $exporter
     * @return void
     */
    public function handle()
    {                             
        Auth::loginUsingId($this->userId);      
        start_export($this->profileId);

        $entries = Navigation::query()->pluck('url')->toArray();
        array_push($entries, "/");

        $this->exporter->export($entries);

        end_export($this->profileId);
    }   
}

?>