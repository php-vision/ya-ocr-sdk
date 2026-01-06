<?php

declare(strict_types=1);

namespace PhpVision\YandexVision;

use PhpVision\YandexVision\Ocr\OcrService;

final readonly class YandexVisionClient
{
    public function __construct(private OcrService $ocrService)
    {
    }

    public function ocr(): OcrService
    {
        return $this->ocrService;
    }
}
