<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Dto;

final readonly class OcrResponse
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    public function __construct(private array $payload, private array $meta = [])
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
