<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Command;

use PhpVision\YandexVision\Dto\OperationStatus;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\OcrEndpoints;
use PhpVision\YandexVision\Ocr\OcrHttpClient;
use PhpVision\YandexVision\Ocr\OcrRequestBuilder;

final readonly class GetOperationCommand
{
    public function __construct(
        private OcrHttpClient $httpClient,
        private OcrRequestBuilder $requestBuilder
    ) {
    }

    public function execute(string $operationId): OperationStatus
    {
        $operationId = trim($operationId);
        if ($operationId === '') {
            throw new ValidationException('Operation id must be a non-empty string.');
        }

        [$data, $meta] = $this->httpClient->sendJsonRequest(
            'GET',
            OcrEndpoints::OPERATION_BASE_URI . '/' . rawurlencode($operationId),
            $this->requestBuilder->buildHeaders([], false),
            null
        );

        return new OperationStatus($operationId, (bool) ($data['done'] ?? false), $data, $meta);
    }
}
