<?php

namespace App\Jobs\Export;

use Spatie\Export\InternalClient;
use App\Handlers\ExportCrawlObserver;
use Spatie\Crawler\Crawler;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Export\Concerns\Messenger;
use Illuminate\Contracts\Filesystem\Filesystem;
use STS\ZipStream\ZipStream;


class Exporter
{    
    /** @var \STS\ZipStream\ZipStream */
    protected $stream;

    /** @var \Spatie\Crawler\Crawler */
    protected $crawler;

    public function __construct(ZipStream $stream)
    {
        $this->stream = $stream;    

        // Crawling itself (ownn website) - so no need to verify SSL
        $options = [];    
        $options['verify'] = false;
        $this->crawler = (new Crawler(new \GuzzleHttp\Client($options)));
    }

    public function export($urls): void
    {        
        $entries = array_map(function (string $url) {
            return url($url);
        }, $urls);

        $this->exportEntries($entries);      
    }

    protected function exportEntries($entries)
    {        
      
        foreach ($entries as $entry) {
            $crawlObserver = new ExportCrawlObserver($this->stream, $entry);
            
            $this->crawler
                ->setCrawlObserver($crawlObserver)
                ->setCrawlProfile(new CrawlInternalUrls($entry))
                ->startCrawling($entry);            
        }
        
    }
}
