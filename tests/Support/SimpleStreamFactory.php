<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class SimpleStreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return new SimpleStream($content);
    }

    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
    {
        $contents = file_get_contents($file);
        if ($contents === false) {
            $contents = '';
        }

        return new SimpleStream($contents);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        $contents = '';
        if (is_resource($resource)) {
            $contents = stream_get_contents($resource) ?: '';
        }

        return new SimpleStream($contents);
    }
}
