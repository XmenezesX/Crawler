<?php

require_once __DIR__ . 
  '/../vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProfiles\CrawlProfile;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

Crawler::create()
  ->setCrawlProfile(new class extends CrawlProfile{
      public function shouldCrawl(UriInterface $url): bool
      {
         return $url->getHost() === 'santo.cancaonova.com' && (str_starts_with($url->getPath(), '/'));
      }
  })
    ->setCrawlObserver(new class extends CrawlObserver{
        public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
        {
            if($url->getPath() ==='/' || $url->getPath() ==='/santos/' || $url->getQuery() !== '' || $response->getStatusCode() !== 200){
                return; 
            }
            $domCrawler = new DomCrawler((string) $response->getBody());
            $nomeSanto = $domCrawler->filter('h1[class="entry-title"]>span')->first()->text();
            $conteudos = $domCrawler->filter('div[class="entry-content content-santo"]')->each(function(DomCrawler $node){
                return $node->text();
            });
            $imagens = $domCrawler->filter('div[class="entry-content content-santo"] >p >a >img')->extract(['src']);
            // echo $nomeSanto.'---------->'. PHP_EOL;
            //  foreach ($conteudos as $conteudo) {
            //      echo $conteudo . "\n";
            //  }
            echo $imagens[0] . PHP_EOL;
        }
        public function crawlFailed(UriInterface $url,RequestException $requestException, ?UriInterface $foundOnUrl = null)
        {
            echo $requestException->getMessage() . PHP_EOL;
        }
    })
    ->setDelayBetweenRequests(100)
    ->startCrawling('https://santo.cancaonova.com/santos/');