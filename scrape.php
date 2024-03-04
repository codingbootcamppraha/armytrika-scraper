<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use DiDom\Document;

// make a client (representing our fake browser)
$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://www.armytrika.cz',
    // You can set any number of default request options.
    'timeout'  => 2.0,
    'cookies'  => new \GuzzleHttp\Cookie\FileCookieJar(__DIR__.'/cookies/cookies.txt', true)
]);

$search_cache_file = __DIR__ . '/cache/search-response.html';

if (!file_exists($search_cache_file)) {
    // make a post request for the search
    $response = $client->post('/vyhledavani/', [
        'form_params' => [
            'vyhledavani_produktu' => 'resident evil'
        ]
    ]);

    file_put_contents('cache/search-response.html', (string)$response->getBody());
}

$html = file_get_contents($search_cache_file);

$document = new Document($html);

$items = $document->find('.produkt_nahled');

$products = [];

foreach ($items as $item) {
    $product = (object)[
        'name' => null,
        'price' => null,
        'img' => null
    ];

    $product->name = $item->first('h2')->text();
    $price_text = $item->first('.cena')->text();
    if (preg_match('#^([\s\d]+),\- Kč$#', $price_text, $m)) {
        $product->price = preg_replace('#\s+#', '', $m[1]);
    }
    $product->img = $item->first('img')->attr('src');

    $products[] = $product;
}

var_dump($products); die();