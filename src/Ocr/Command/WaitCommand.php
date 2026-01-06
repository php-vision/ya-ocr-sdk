<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Command;

use PhpVision\YandexVision\Dto\OcrResponse;
use PhpVision\YandexVision\Exception\ApiException;
use PhpVision\YandexVision\Exception\TimeoutException;
use PhpVision\YandexVision\Ocr\BackoffPolicy;

final readonly class WaitCommand
{
    public function __construct(
        private GetOperationCommand $getOperationCommand,
        private GetRecognitionCommand $getRecognitionCommand
    ) {
    }

    public function execute(string $operationId, int $timeoutSeconds = 60, ?BackoffPolicy $backoff = null): OcrResponse
    {
        $backoff = $backoff ?? new BackoffPolicy();
        $deadline = time() + max(0, $timeoutSeconds);
        $attempt = 0;

        while (true) {
            $status = $this->getOperationCommand->execute($operationId);
            $payload = $status->getPayload();

            if ($status->isDone()) {
                $errorMessage = $this->extractOperationError($payload);
                if ($errorMessage !== null) {
                    throw new ApiException($errorMessage);
                }

                $response = $payload['response'] ?? null;
                if (is_array($response)) {
                    return new OcrResponse($response, $status->getMeta());
                }

                return $this->getRecognitionCommand->execute($operationId);
            }

            $errorMessage = $this->extractOperationError($payload);
            if ($errorMessage !== null) {
                throw new ApiException($errorMessage);
            }

            $now = time();
            if ($now >= $deadline) {
                throw new TimeoutException('OCR operation timed out.');
            }

            $delay = $backoff->getDelayForAttempt($attempt);
            $remaining = $deadline - $now;
            $sleepSeconds = min($delay, $remaining);
            if ($sleepSeconds > 0) {
                sleep($sleepSeconds);
            }
            $attempt++;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractOperationError(array $payload): ?string
    {
        $error = $payload['error'] ?? null;
        if (!is_array($error)) {
            return null;
        }

        $message = $error['message'] ?? null;
        if (is_string($message) && $message !== '') {
            return $message;
        }

        return 'OCR operation failed.';
    }
}
