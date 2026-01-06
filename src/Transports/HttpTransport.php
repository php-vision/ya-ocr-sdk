<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Transports;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

readonly class HttpTransport implements TransportInterface
{
    public function __construct(private ClientInterface $client)
    {
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
