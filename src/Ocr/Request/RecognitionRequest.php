<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Request;

use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\OcrEndpoints;

final readonly class RecognitionRequest implements OcrRequestInterface
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
        $query = http_build_query(['operationId' => $this->operationId]);

        return OcrEndpoints::OCR_BASE_URI . '/getRecognition?' . $query;
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
}
