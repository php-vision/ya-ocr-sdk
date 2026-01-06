# Yandex Cloud Vision OCR PHP Client

HTTP client for **Yandex Cloud Vision OCR** (sync + async). Designed as a small, predictable, PSR-friendly Composer library with clear errors, DTOs, and optional concurrency via a runner interface.

<p align="center">
<a href="https://github.com/php-vision/ya-ocr-sdk/actions"><img src="https://github.com/php-vision/ya-ocr-sdk/actions/workflows/ci-test.yml/badge.svg" alt="Tests status"></a>
<a href="https://packagist.org/packages/php-vision/ya-ocr-sdk"><img src="https://img.shields.io/packagist/v/php-vision/ya-ocr-sdk.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/php-vision/ya-ocr-sdk"><img src="https://img.shields.io/packagist/l/php-vision/ya-ocr-sdk.svg" alt="License"></a>
</p>

## Features

- Sync OCR (`recognizeText`)
- Async OCR (`startTextRecognition` + polling with `wait` / `waitMany`)
- PSR-18 transport + PSR-17 factories
- Deterministic DTOs with raw payload/meta
- Typed exceptions
- No hard dependency on event loops (optional concurrency via runner)

## Requirements

- PHP 8.4+
- PSR-18 HTTP client
- PSR-17 factories

## Installation

```bash
composer require php-vision/ya-ocr-vision-client
```

You must also install a PSR-18 client and PSR-17 factories, for example:

```bash
composer require guzzlehttp/guzzle nyholm/psr7
```

## Authentication

Two options:

- **IAM Token**  
  Use `Authorization: Bearer <IAM_TOKEN>` and pass `folderId` in request options.
- **API Key**  
  Use `Authorization: Api-Key <API_KEY>` (no `folderId` header).

This library only signs requests and forwards headers; it does not call IAM endpoints.

## Quick Start (Sync)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpVision\YandexVision\Auth\ApiKeyCredentialProvider;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\Transports\HttpTransport;
use PhpVision\YandexVision\YandexVisionClient;

require __DIR__ . '/vendor/autoload.php';

$httpClient = new GuzzleClient();
$psr17Factory = new Psr17Factory();

$transport = new HttpTransport($httpClient);
$credentials = new ApiKeyCredentialProvider('YOUR_API_KEY');

$ocr = new OcrService($transport, $credentials, $psr17Factory, $psr17Factory);
$client = new YandexVisionClient($ocr);

$bytes = file_get_contents(__DIR__ . '/image.png');
$response = $client->ocr()->recognizeText($bytes, 'image/png', [
    'languageCodes' => ['ru', 'en'],
]);

var_dump($response->getPayload());
```

## Async OCR (Start + Poll)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpVision\YandexVision\Auth\ApiKeyCredentialProvider;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\Transports\HttpTransport;
use PhpVision\YandexVision\YandexVisionClient;

require __DIR__ . '/vendor/autoload.php';

$httpClient = new GuzzleClient();
$psr17Factory = new Psr17Factory();

$transport = new HttpTransport($httpClient);
$credentials = new ApiKeyCredentialProvider('YOUR_API_KEY');

$ocr = new OcrService($transport, $credentials, $psr17Factory, $psr17Factory);
$client = new YandexVisionClient($ocr);

$handle = $client->ocr()->startTextRecognitionFromFile(__DIR__ . '/image.png');
$result = $client->ocr()->wait($handle->getOperationId(), 60);

var_dump($result->getPayload());
```

## Options

Every OCR call accepts an `$options` array:

- `languageCodes` (array of ISO 639-1 codes)
- `model` (string, optional)
- `folderId` (string, only for IAM token auth)
- `requestId` (string, forwarded as `x-request-id`)

## Concurrency (waitMany)

`waitMany()` uses a runner to execute waits. Default is sequential:

```php
$results = $client->ocr()->waitMany(['op-1', 'op-2'], 60);
```

You can provide a custom runner that performs concurrent execution.

## Error Handling

Exceptions are thrown for common failures:

- `ApiException` — non-2xx responses or operation errors
- `HttpException` — transport failures
- `ValidationException` — invalid input or JSON
- `TimeoutException` — async polling timed out

## License

This project is licensed under the Apache License 2.0 - see the LICENSE file for details.
