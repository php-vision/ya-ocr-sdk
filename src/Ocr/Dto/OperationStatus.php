<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Dto;

final readonly class OperationStatus
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    public function __construct(private string $operationId, private bool $done, private array $payload, private array $meta = [])
    {
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function isDone(): bool
    {
        return $this->done;
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
