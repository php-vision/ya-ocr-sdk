<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests;

use PhpVision\YandexVision\Exception\ApiException;
use PhpVision\YandexVision\Exception\TimeoutException;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\Enum\LanguageCode;
use PhpVision\YandexVision\Ocr\Enum\OcrModel;
use PhpVision\YandexVision\Ocr\OcrOptions;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\Tests\Support\FakeTransport;
use PhpVision\YandexVision\Tests\Support\SimpleRequestFactory;
use PhpVision\YandexVision\Tests\Support\SimpleResponse;
use PhpVision\YandexVision\Tests\Support\SimpleStreamFactory;
use PhpVision\YandexVision\Tests\Support\StaticCredentialProvider;
use PHPUnit\Framework\TestCase;

final class OcrServiceTest extends TestCase
{
    public function testRecognizeTextBuildsPayloadAndHeaders(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode(['textAnnotation' => ['fullText' => 'ok']], JSON_THROW_ON_ERROR), [
                'x-request-id' => 'req-1',
            ]),
        ]);

        $service = $this->createService($transport);

        $bytes = 'image-bytes';
        $options = OcrOptions::create()
            ->withLanguageCodes(LanguageCode::RU, LanguageCode::EN)
            ->withModel(OcrModel::Page)
            ->withRequestId('req-override');

        $response = $service->recognizeText($bytes, 'image/png', $options);

        self::assertSame(['textAnnotation' => ['fullText' => 'ok']], $response->getPayload());
        self::assertSame('req-1', $response->getMeta()['request_id']);

        $request = $transport->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/ocr/v1/recognizeText', $request->getUri()->getPath());
        self::assertSame('Api-Key test', $request->getHeaderLine('Authorization'));
        self::assertSame('req-override', $request->getHeaderLine('x-request-id'));

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(base64_encode($bytes), $body['content'] ?? null);
        self::assertSame('image/png', $body['mimeType'] ?? null);
        self::assertSame(['ru', 'en'], $body['languageCodes'] ?? null);
        self::assertSame('page', $body['model'] ?? null);
    }

    public function testRecognizeTextFromFileUsesDetectedMime(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode(['textAnnotation' => ['fullText' => 'ok']], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);

        $file = tempnam(sys_get_temp_dir(), 'ocr');
        self::assertIsString($file);
        file_put_contents($file, "%PDF-1.4\n%EOF\n");

        try {
            $service->recognizeTextFromFile($file);
            $request = $transport->getLastRequest();
            self::assertNotNull($request);
            $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
            self::assertSame('application/pdf', $body['mimeType'] ?? null);
        } finally {
            unlink($file);
        }
    }

    public function testStartTextRecognitionReturnsOperationHandle(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode(['id' => 'op-1'], JSON_THROW_ON_ERROR), [
                'x-request-id' => 'req-op',
            ]),
        ]);

        $service = $this->createService($transport);
        $handle = $service->startTextRecognition('bytes', 'image/png');

        self::assertSame('op-1', $handle->getOperationId());
        self::assertSame('req-op', $handle->getRequestId());

        $request = $transport->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/ocr/v1/recognizeTextAsync', $request->getUri()->getPath());
    }

    public function testGetOperationRequiresId(): void
    {
        $service = $this->createService(new FakeTransport());

        $this->expectException(ValidationException::class);
        $service->getOperation('   ');
    }

    public function testGetRecognitionRequiresId(): void
    {
        $service = $this->createService(new FakeTransport());

        $this->expectException(ValidationException::class);
        $service->getRecognition('   ');
    }

    public function testWaitReturnsResponseFromOperation(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode([
                'id' => 'op-1',
                'done' => true,
                'response' => ['textAnnotation' => ['fullText' => 'done']],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);
        $response = $service->wait('op-1', 5);

        self::assertSame(['textAnnotation' => ['fullText' => 'done']], $response->getPayload());
    }

    public function testWaitFallsBackToGetRecognition(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode([
                'id' => 'op-1',
                'done' => true,
            ], JSON_THROW_ON_ERROR)),
            new SimpleResponse(200, json_encode([
                'textAnnotation' => ['fullText' => 'async'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);
        $response = $service->wait('op-1', 5);

        self::assertSame(['textAnnotation' => ['fullText' => 'async']], $response->getPayload());
    }

    public function testWaitThrowsOnApiError(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode([
                'id' => 'op-1',
                'done' => true,
                'error' => ['message' => 'boom'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);

        $this->expectException(ApiException::class);
        $service->wait('op-1', 5);
    }

    public function testWaitTimeoutThrowsException(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode([
                'id' => 'op-1',
                'done' => false,
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);

        $this->expectException(TimeoutException::class);
        $service->wait('op-1', 0);
    }

    public function testWaitManyUsesSequentialRunner(): void
    {
        $transport = new FakeTransport([
            new SimpleResponse(200, json_encode([
                'id' => 'op-1',
                'done' => true,
                'response' => ['textAnnotation' => ['fullText' => 'one']],
            ], JSON_THROW_ON_ERROR)),
            new SimpleResponse(200, json_encode([
                'id' => 'op-2',
                'done' => true,
                'response' => ['textAnnotation' => ['fullText' => 'two']],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $service = $this->createService($transport);
        $responses = $service->waitMany(['op-1', 'op-2'], 5);

        self::assertSame('one', $responses[0]->getPayload()['textAnnotation']['fullText'] ?? null);
        self::assertSame('two', $responses[1]->getPayload()['textAnnotation']['fullText'] ?? null);
    }

    private function createService(FakeTransport $transport): OcrService
    {
        return new OcrService(
            $transport,
            new StaticCredentialProvider('Api-Key test'),
            new SimpleRequestFactory(),
            new SimpleStreamFactory()
        );
    }
}
