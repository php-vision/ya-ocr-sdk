<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpVision\YandexVision\Auth\ApiKeyCredentialProvider;
use PhpVision\YandexVision\Transports\HttpTransport;
use PhpVision\YandexVision\Ocr\OcrService;
use PhpVision\YandexVision\YandexVisionClient;

require_once __DIR__ . '/../vendor/autoload.php';

$imagePath = __DIR__ . '/image.png';

$httpClient = new GuzzleClient();
$psr17Factory = new Psr17Factory();

$transport = new HttpTransport($httpClient);
$credentials = new ApiKeyCredentialProvider('YOUR_API_KEY');

$ocrService = new OcrService($transport, $credentials, $psr17Factory, $psr17Factory);
$client = new YandexVisionClient($ocrService);

$response = $client->ocr()->recognizeTextFromFile($imagePath, [
    'languageCodes' => ['ru', 'en'],
    // 'folderId' => 'YOUR_FOLDER_ID',
]);

var_dump($response->getPayload());
