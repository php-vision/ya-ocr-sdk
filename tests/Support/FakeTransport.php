<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use PhpVision\YandexVision\Transports\TransportInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeTransport implements TransportInterface
{
    /** @var array<int, RequestInterface> */
    private array $requests = [];

    /**
     * @param array<int, ResponseInterface> $queue
     */
    public function __construct(private array $queue = [])
    {
    }

    public function enqueue(ResponseInterface $response): void
    {
        $this->queue[] = $response;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->queue === []) {
            throw new \RuntimeException('No queued responses in FakeTransport.');
        }

        return array_shift($this->queue);
    }

    /**
     * @return array<int, RequestInterface>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getLastRequest(): ?RequestInterface
    {
        if ($this->requests === []) {
            return null;
        }

        return $this->requests[count($this->requests) - 1];
    }
}
