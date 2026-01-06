<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Command;

use PhpVision\YandexVision\Dto\OcrResponse;
use PhpVision\YandexVision\Ocr\OcrEndpoints;
use PhpVision\YandexVision\Ocr\OcrHttpClient;
use PhpVision\YandexVision\Ocr\OcrRequestBuilder;

final readonly class RecognizeTextCommand
{
    public function __construct(
        private OcrHttpClient $httpClient,
        private OcrRequestBuilder $requestBuilder
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function execute(string $bytes, string $mime, array $options = []): OcrResponse
    {
        $payload = $this->requestBuilder->buildRecognizePayload($bytes, $mime, $options);

        [$data, $meta] = $this->httpClient->sendJsonRequest(
            'POST',
            OcrEndpoints::OCR_BASE_URI . '/recognizeText',
            $this->requestBuilder->buildHeaders($options, true),
            $payload
        );

        return new OcrResponse($data, $meta);
    }
}
