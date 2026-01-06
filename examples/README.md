# Examples

These snippets show how to wire the client with PSR-18 and PSR-17 implementations.
You need to install your preferred HTTP client and PSR-7/PSR-17 factories (for example, Guzzle + Nyholm PSR-7).

Notes:
- If you use an IAM token, pass `folderId` in options (for example, `['folderId' => '...']`).
- `requestId` is optional and forwarded as `x-request-id`.

## Sync OCR (bytes)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpVision\YandexVision\Auth\ApiKeyCredentialProvider;
use PhpVision\YandexVision\Transports\HttpTransport;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\YandexVisionClient;

$httpClient = new GuzzleClient();
$psr17Factory = new Psr17Factory();

$transport = new HttpTransport($httpClient);
$credentials = new ApiKeyCredentialProvider('YOUR_API_KEY');

$ocrService = new OcrService($transport, $credentials, $psr17Factory, $psr17Factory);
$client = new YandexVisionClient($ocrService);

$bytes = file_get_contents(__DIR__ . '/image.png');
$response = $client->ocr()->recognizeText($bytes, 'image/png', [
    'languageCodes' => ['ru', 'en'],
    // 'folderId' => 'YOUR_FOLDER_ID',
]);

var_dump($response->getPayload());
```

## Async OCR (start + wait)

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpVision\YandexVision\Auth\ApiKeyCredentialProvider;
use PhpVision\YandexVision\Transports\HttpTransport;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\YandexVisionClient;

$httpClient = new GuzzleClient();
$psr17Factory = new Psr17Factory();

$transport = new HttpTransport($httpClient);
$credentials = new ApiKeyCredentialProvider('YOUR_API_KEY');

$ocrService = new OcrService($transport, $credentials, $psr17Factory, $psr17Factory);
$client = new YandexVisionClient($ocrService);

$bytes = file_get_contents(__DIR__ . '/image.png');
$handle = $client->ocr()->startTextRecognition($bytes, 'image/png', [
    // 'folderId' => 'YOUR_FOLDER_ID',
]);
$result = $client->ocr()->wait($handle->getOperationId(), 60);

var_dump($result->getPayload());
```
