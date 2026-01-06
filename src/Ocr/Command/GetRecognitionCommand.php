<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Command;

use PhpVision\YandexVision\Dto\OcrResponse;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\OcrEndpoints;
use PhpVision\YandexVision\Ocr\OcrHttpClient;
use PhpVision\YandexVision\Ocr\OcrRequestBuilder;

final readonly class GetRecognitionCommand
{
    public function __construct(
        private OcrHttpClient $httpClient,
        private OcrRequestBuilder $requestBuilder
    ) {
    }

    public function execute(string $operationId): OcrResponse
    {
        $operationId = trim($operationId);
        if ($operationId === '') {
            throw new ValidationException('Operation id must be a non-empty string.');
        }

        $query = http_build_query(['operationId' => $operationId]);

        [$data, $meta] = $this->httpClient->sendJsonRequest(
            'GET',
            OcrEndpoints::OCR_BASE_URI . '/getRecognition?' . $query,
            $this->requestBuilder->buildHeaders([], false),
            null
        );

        return new OcrResponse($data, $meta);
    }
}
