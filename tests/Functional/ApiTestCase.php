<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;

abstract class ApiTestCase extends KernelTestCase
{
    // use HasBrowser trait and import method browser, renamed it to baseKernelBrowser
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    // Globally sending header:
    // Create protected method browser used by my test
    // this method use baseKernelBrowser/browser method (from Zenstruck\Browser\Test\HasBrowser trait)
    // and define header key "Accept" with default value "application/ld+json"
    // Now all request return JSON-LD
    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(HttpOptions::create()->withHeader('Accept', 'application/ld+json'));
    }
}