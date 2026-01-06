<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Request;

use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\OcrEndpoints;

final readonly class OperationRequest implements OcrRequestInterface
{
    private string $operationId;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $operationId, private array $headers)
    {
        $operationId = trim($operationId);
        if ($operationId === '') {
            throw new ValidationException('Operation id must be a non-empty string.');
        }

        $this->operationId = $operationId;
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function getUrl(): string
    {
        return OcrEndpoints::OPERATION_BASE_URI . '/' . rawurlencode($this->operationId);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?array
    {
        return null;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }
}
