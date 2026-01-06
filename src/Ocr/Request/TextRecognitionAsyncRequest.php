<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Request;

use PhpVision\YandexVision\Ocr\OcrEndpoints;

final readonly class TextRecognitionAsyncRequest implements OcrRequestInterface
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function __construct(private array $payload, private array $headers)
    {
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getUrl(): string
    {
        return OcrEndpoints::OCR_BASE_URI . '/recognizeTextAsync';
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBody(): array
    {
        return $this->payload;
    }
}
