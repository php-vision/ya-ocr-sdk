<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

final readonly class OperationHandle
{
    public function __construct(private string $operationId, private ?string $requestId = null)
    {
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
