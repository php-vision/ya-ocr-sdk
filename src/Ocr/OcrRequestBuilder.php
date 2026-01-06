<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Auth\CredentialProviderInterface;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\Enum\LanguageCode;

final readonly class OcrRequestBuilder
{
    public function __construct(private CredentialProviderInterface $credentials)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRecognizePayload(string $bytes, string $mime, ?OcrOptions $options): array
    {
        $payload = [
            'content' => base64_encode($bytes),
            'mimeType' => $mime,
        ];

        $languageCodes = $options?->getLanguageCodes() ?? [];
        if ($languageCodes !== []) {
            $payload['languageCodes'] = array_map(
                static fn (LanguageCode $code): string => $code->value,
                $languageCodes
            );
        }

        $model = $options?->getModel();
        if ($model instanceof \PhpVision\YandexVision\Ocr\Enum\OcrModel) {
            $payload['model'] = $model->value;
        }

        return $payload;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function readFilePayload(string $path, ?OcrOptions $options): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new ValidationException('File is not readable: ' . $path);
        }

        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new ValidationException('Unable to read file: ' . $path);
        }

        $mime = $options?->getMimeType() ?? '';
        if ($mime === '') {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($path) ?: '';
        }

        if ($mime === '') {
            throw new ValidationException('Unable to detect mime type for file: ' . $path);
        }

        return [$bytes, $mime];
    }

    /**
     * @return array<string, string>
     */
    public function buildHeaders(?OcrOptions $options, bool $withContentType): array
    {
        $headers = [
            'Authorization' => $this->credentials->getAuthorizationHeader(),
        ];

        if ($withContentType) {
            $headers['Content-Type'] = 'application/json';
        }

        $folderId = $options?->getFolderId();
        if ($folderId !== null) {
            $headers['x-folder-id'] = $folderId;
        }

        $requestId = $options?->getRequestId();
        if ($requestId !== null) {
            $headers['x-request-id'] = $requestId;
        }

        return $headers;
    }
}
