<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Command;

use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\OcrEndpoints;
use PhpVision\YandexVision\Ocr\OcrHttpClient;
use PhpVision\YandexVision\Ocr\OcrRequestBuilder;
use PhpVision\YandexVision\Ocr\OperationHandle;

final readonly class StartTextRecognitionCommand
{
    public function __construct(
        private OcrHttpClient $httpClient,
        private OcrRequestBuilder $requestBuilder
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function execute(string $bytes, string $mime, array $options = []): OperationHandle
    {
        $payload = $this->requestBuilder->buildRecognizePayload($bytes, $mime, $options);

        [$data, $meta] = $this->httpClient->sendJsonRequest(
            'POST',
            OcrEndpoints::OCR_BASE_URI . '/recognizeTextAsync',
            $this->requestBuilder->buildHeaders($options, true),
            $payload
        );

        $operationId = $data['id'] ?? null;
        if (!is_string($operationId) || $operationId === '') {
            throw new ValidationException('Missing operation id in async recognition response.');
        }

        return new OperationHandle($operationId, $meta['request_id'] ?? null);
    }
}
