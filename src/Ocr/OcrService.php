<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Auth\CredentialProviderInterface;
use PhpVision\YandexVision\Concurrency\RunnerInterface;
use PhpVision\YandexVision\Concurrency\SequentialRunner;
use PhpVision\YandexVision\Dto\OcrResponse;
use PhpVision\YandexVision\Dto\OperationStatus;
use PhpVision\YandexVision\Ocr\Command\GetOperationCommand;
use PhpVision\YandexVision\Ocr\Command\GetRecognitionCommand;
use PhpVision\YandexVision\Ocr\Command\RecognizeTextCommand;
use PhpVision\YandexVision\Ocr\Command\StartTextRecognitionCommand;
use PhpVision\YandexVision\Ocr\Command\WaitCommand;
use PhpVision\YandexVision\Transports\TransportInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class OcrService
{
    private RecognizeTextCommand $recognizeTextCommand;
    private StartTextRecognitionCommand $startTextRecognitionCommand;
    private GetOperationCommand $getOperationCommand;
    private GetRecognitionCommand $getRecognitionCommand;
    private WaitCommand $waitCommand;
    private OcrRequestBuilder $requestBuilder;

    public function __construct(
        TransportInterface $transport,
        CredentialProviderInterface $credentials,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->requestBuilder = new OcrRequestBuilder($credentials);
        $httpClient = new OcrHttpClient($transport, $requestFactory, $streamFactory);

        $this->recognizeTextCommand = new RecognizeTextCommand($httpClient, $this->requestBuilder);
        $this->startTextRecognitionCommand = new StartTextRecognitionCommand($httpClient, $this->requestBuilder);
        $this->getOperationCommand = new GetOperationCommand($httpClient, $this->requestBuilder);
        $this->getRecognitionCommand = new GetRecognitionCommand($httpClient, $this->requestBuilder);
        $this->waitCommand = new WaitCommand($this->getOperationCommand, $this->getRecognitionCommand);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function recognizeText(string $bytes, string $mime, array $options = []): OcrResponse
    {
        return $this->recognizeTextCommand->execute($bytes, $mime, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function recognizeTextFromFile(string $path, array $options = []): OcrResponse
    {
        [$bytes, $mime] = $this->requestBuilder->readFilePayload($path, $options);

        return $this->recognizeTextCommand->execute($bytes, $mime, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function startTextRecognition(string $bytes, string $mime, array $options = []): OperationHandle
    {
        return $this->startTextRecognitionCommand->execute($bytes, $mime, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function startTextRecognitionFromFile(string $path, array $options = []): OperationHandle
    {
        [$bytes, $mime] = $this->requestBuilder->readFilePayload($path, $options);

        return $this->startTextRecognitionCommand->execute($bytes, $mime, $options);
    }

    public function getOperation(string $operationId): OperationStatus
    {
        return $this->getOperationCommand->execute($operationId);
    }

    public function getRecognition(string $operationId): OcrResponse
    {
        return $this->getRecognitionCommand->execute($operationId);
    }

    public function wait(string $operationId, int $timeoutSeconds = 60, ?BackoffPolicy $backoff = null): OcrResponse
    {
        return $this->waitCommand->execute($operationId, $timeoutSeconds, $backoff);
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

}
