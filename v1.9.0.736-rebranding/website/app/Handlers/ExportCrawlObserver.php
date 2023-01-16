<?php

namespace App\Handlers;

use Spatie\Export;
use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Spatie\Export\Concerns\Messenger;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Filesystem\Filesystem;
use STS\ZipStream\ZipStream;

class ExportCrawlObserver extends CrawlObserver
{

    /** @var string */
    protected $entry;

      /** @var \STS\ZipStream\ZipStream */
      protected $stream;

    public function __construct(Zipstream $stream, string $entry)
    {
        $this->entry = $entry;
        $this->stream = $stream;        
    }

    public function getAssets() {
        return $this->assets;
    }
    
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $isFile = ! preg_match('/^((.+\.[\d]+[^\w]*)|((?:(?!\.).)*|))$/', $url->getPath());

        $targetPath = $isFile
            ? '/'.ltrim($url->getPath(), '/')
            : '/'.ltrim($url->getPath().'/index.html', '/');
        
        $contents = str_replace($this->entry.'/', '/', $response->getBody());
        $contents = str_replace($this->entry, '/', $contents);
      
        $this->stream->addRaw( $contents, $targetPath); 
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        throw $requestException;
    }
}
