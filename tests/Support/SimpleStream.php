<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use Psr\Http\Message\StreamInterface;

final class SimpleStream implements StreamInterface
{
    private int $position = 0;

    public function __construct(private string $content = '')
    {
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void
    {
        $this->position = 0;
    }

    public function detach()
    {
        $this->position = 0;

        return null;
    }

    /** @phpstan-ignore-next-line */
    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $length = strlen($this->content);
        if ($whence === SEEK_SET) {
            $this->position = max(0, min($length, $offset));
        } elseif ($whence === SEEK_CUR) {
            $this->position = max(0, min($length, $this->position + $offset));
        } elseif ($whence === SEEK_END) {
            $this->position = max(0, min($length, $length + $offset));
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write(string $string): int
    {
        $before = substr($this->content, 0, $this->position);
        $after = substr($this->content, $this->position + strlen($string));
        $this->content = $before . $string . $after;
        $this->position += strlen($string);

        return strlen($string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        $chunk = substr($this->content, $this->position, $length);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function getContents(): string
    {
        $chunk = substr($this->content, $this->position);
        $this->position = strlen($this->content);

        return $chunk;
    }

    public function getMetadata(?string $key = null): mixed
    {
        return $key === null ? [] : null;
    }
}
