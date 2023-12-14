<?php

declare(strict_types=1);

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ScraPHP\ScraPHP;
use ScraPHP\ProcessPage;
use ScraPHP\Writers\Writer;
use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\HttpClient;

test('bind scraphp methods to instance', function () {

    $pp = new class () extends ProcessPage {
        public function process(Page $page): void
        {
        }
    };

    $httpClient = Mockery::mock(HttpClient::class);
    $scraphp = new ScraPHP(
        httpClient: $httpClient,
        logger: Mockery::mock(LoggerInterface::class),
        writer: Mockery::mock(Writer::class),
    );
    
    
    $httpClient->shouldReceive('get')
        ->with('http://localhost:8000/hello-world.php')
        ->once()
        ->andReturn(Mockery::mock(Page::class));
    

    $pp->withScraPHP($scraphp);

    $pp->go('http://localhost:8000/hello-world.php', function (Page $page) {

    });

});
