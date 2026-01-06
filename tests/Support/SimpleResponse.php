<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class SimpleResponse implements ResponseInterface
{
    /** @var array<string, array<int, string>> */
    private array $headers = [];
    private StreamInterface $body;
    private string $protocolVersion = '1.1';
    private string $reasonPhrase = '';

    /**
     * @param array<string, string|array<int, string>> $headers
     */
    public function __construct(private int $statusCode, string $body = '', array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->headers[$this->normalizeHeader($name)] = $this->normalizeHeaderValues($value);
        }
        $this->body = new SimpleStream($body);
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): ResponseInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$this->normalizeHeader($name)]);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[$this->normalizeHeader($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[$this->normalizeHeader($name)] = $this->normalizeHeaderValues($value);

        return $clone;
    }

    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $normalized = $this->normalizeHeader($name);
        $clone->headers[$normalized] = array_merge(
            $clone->headers[$normalized] ?? [],
            $this->normalizeHeaderValues($value)
        );

        return $clone;
    }

    public function withoutHeader(string $name): ResponseInterface
    {
        $clone = clone $this;
        unset($clone->headers[$this->normalizeHeader($name)]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private function normalizeHeader(string $name): string
    {
        return strtolower($name);
    }

    /**
     * @param string|array<int, string> $value
     * @return array<int, string>
     */
    private function normalizeHeaderValues($value): array
    {
        if (is_array($value)) {
            return array_values(array_map('strval', $value));
        }

        return [(string) $value];
    }
}
