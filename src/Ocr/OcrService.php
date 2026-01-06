<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Auth\CredentialProviderInterface;
use PhpVision\YandexVision\Concurrency\RunnerInterface;
use PhpVision\YandexVision\Concurrency\SequentialRunner;
use PhpVision\YandexVision\Ocr\Dto\OcrResponse;
use PhpVision\YandexVision\Ocr\Dto\OperationStatus;
use PhpVision\YandexVision\Exception\ApiException;
use PhpVision\YandexVision\Exception\TimeoutException;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\Request\OperationRequest;
use PhpVision\YandexVision\Ocr\Request\RecognitionRequest;
use PhpVision\YandexVision\Ocr\Request\TextRecognitionAsyncRequest;
use PhpVision\YandexVision\Ocr\Request\TextRecognitionRequest;
use PhpVision\YandexVision\Transports\TransportInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class OcrService
{
    private OcrHttpClient $httpClient;
    private OcrRequestBuilder $requestBuilder;

    public function __construct(
        TransportInterface $transport,
        CredentialProviderInterface $credentials,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->requestBuilder = new OcrRequestBuilder($credentials);
        $this->httpClient = new OcrHttpClient($transport, $requestFactory, $streamFactory);
    }

    public function recognizeText(string $bytes, string $mime, ?OcrOptions $options = null): OcrResponse
    {
        $payload = $this->requestBuilder->buildRecognizePayload($bytes, $mime, $options);
        $headers = $this->requestBuilder->buildHeaders($options, true);

        [$data, $meta] = $this->httpClient->send(new TextRecognitionRequest($payload, $headers));

        return new OcrResponse($data, $meta);
    }

    public function recognizeTextFromFile(string $path, ?OcrOptions $options = null): OcrResponse
    {
        [$bytes, $mime] = $this->requestBuilder->readFilePayload($path, $options);

        return $this->recognizeText($bytes, $mime, $options);
    }

    public function startTextRecognition(string $bytes, string $mime, ?OcrOptions $options = null): OperationHandle
    {
        $payload = $this->requestBuilder->buildRecognizePayload($bytes, $mime, $options);
        $headers = $this->requestBuilder->buildHeaders($options, true);

        [$data, $meta] = $this->httpClient->send(new TextRecognitionAsyncRequest($payload, $headers));

        $operationId = $data['id'] ?? null;
        if (!is_string($operationId) || $operationId === '') {
            throw new ValidationException('Missing operation id in async recognition response.');
        }

        return new OperationHandle($operationId, $meta['request_id'] ?? null);
    }

    public function startTextRecognitionFromFile(string $path, ?OcrOptions $options = null): OperationHandle
    {
        [$bytes, $mime] = $this->requestBuilder->readFilePayload($path, $options);

        return $this->startTextRecognition($bytes, $mime, $options);
    }

    public function getOperation(string $operationId): OperationStatus
    {
        $headers = $this->requestBuilder->buildHeaders(null, false);
        $request = new OperationRequest($operationId, $headers);

        [$data, $meta] = $this->httpClient->send($request);

        return new OperationStatus($request->getOperationId(), (bool) ($data['done'] ?? false), $data, $meta);
    }

    public function getRecognition(string $operationId): OcrResponse
    {
        $headers = $this->requestBuilder->buildHeaders(null, false);
        $request = new RecognitionRequest($operationId, $headers);

        [$data, $meta] = $this->httpClient->send($request);

        return new OcrResponse($data, $meta);
    }

    public function wait(string $operationId, int $timeoutSeconds = 60, ?BackoffPolicy $backoff = null): OcrResponse
    {
        $backoff = $backoff ?? new BackoffPolicy();
        $deadline = time() + max(0, $timeoutSeconds);
        $attempt = 0;

        while (true) {
            $status = $this->getOperation($operationId);
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

                return $this->getRecognition($operationId);
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
     * @param array<int, string> $operationIds
     * @return array<int, OcrResponse>
     */
    public function waitMany(
        array $operationIds,
        int $timeoutSeconds = 60,
        ?BackoffPolicy $backoff = null,
        ?RunnerInterface $runner = null
    ): array {
        $runner = $runner ?? new SequentialRunner();

        $tasks = [];
        foreach ($operationIds as $operationId) {
            $tasks[] = fn (): OcrResponse => $this->wait($operationId, $timeoutSeconds, $backoff);
        }

        return $runner->run($tasks);
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
