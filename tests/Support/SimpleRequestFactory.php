<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class SimpleRequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new SimpleRequest($method, new SimpleUri((string) $uri));
    }
}
