<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class SimpleRequest implements RequestInterface
{
    /** @var array<string, array<int, string>> */
    private array $headers = [];
    private StreamInterface $body;
    private string $protocolVersion = '1.1';

    public function __construct(private string $method, private UriInterface $uri)
    {
        $this->body = new SimpleStream('');
    }

    public function getRequestTarget(): string
    {
        $target = $this->uri->getPath();
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target === '' ? '/' : $target;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = new SimpleUri($requestTarget);

        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost && $uri->getHost() !== '') {
            $clone->headers['Host'] = [$uri->getHost()];
        }

        return $clone;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): RequestInterface
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

    public function withHeader(string $name, $value): RequestInterface
    {
        $clone = clone $this;
        $clone->headers[$this->normalizeHeader($name)] = $this->normalizeHeaderValues($value);

        return $clone;
    }

    public function withAddedHeader(string $name, $value): RequestInterface
    {
        $clone = clone $this;
        $normalized = $this->normalizeHeader($name);
        $clone->headers[$normalized] = array_merge(
            $clone->headers[$normalized] ?? [],
            $this->normalizeHeaderValues($value)
        );

        return $clone;
    }

    public function withoutHeader(string $name): RequestInterface
    {
        $clone = clone $this;
        unset($clone->headers[$this->normalizeHeader($name)]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): RequestInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
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
