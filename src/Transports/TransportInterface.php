<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Transports;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface TransportInterface
{
    public function send(RequestInterface $request): ResponseInterface;
}
