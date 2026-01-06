<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Request;

interface OcrRequestInterface
{
    public function getMethod(): string;

    public function getUrl(): string;

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function getBody(): ?array;
}
