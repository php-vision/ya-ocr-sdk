<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Auth\CredentialProviderInterface;
use PhpVision\YandexVision\Exception\ValidationException;

final readonly class OcrRequestBuilder
{
    public function __construct(private CredentialProviderInterface $credentials)
    {
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function buildRecognizePayload(string $bytes, string $mime, array $options): array
    {
        $payload = [
            'content' => base64_encode($bytes),
            'mimeType' => $mime,
        ];

        if (isset($options['languageCodes'])) {
            $languageCodes = $options['languageCodes'];
            if (!is_array($languageCodes)) {
                throw new ValidationException('languageCodes must be an array of strings.');
            }
            $payload['languageCodes'] = array_values($languageCodes);
        }

        if (isset($options['model'])) {
            if (!is_string($options['model']) || $options['model'] === '') {
                throw new ValidationException('model must be a non-empty string.');
            }
            $payload['model'] = $options['model'];
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{0: string, 1: string}
     */
    public function readFilePayload(string $path, array $options): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new ValidationException('File is not readable: ' . $path);
        }

        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new ValidationException('Unable to read file: ' . $path);
        }

        $mime = $options['mimeType'] ?? $options['mime'] ?? '';
        $mime = is_string($mime) ? $mime : '';
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
     * @param array<string, mixed> $options
     * @return array<string, string>
     */
    public function buildHeaders(array $options, bool $withContentType): array
    {
        $headers = [
            'Authorization' => $this->credentials->getAuthorizationHeader(),
        ];

        if ($withContentType) {
            $headers['Content-Type'] = 'application/json';
        }

        $folderId = $options['folderId'] ?? null;
        if (is_string($folderId) && $folderId !== '') {
            $headers['x-folder-id'] = $folderId;
        }

        $requestId = $options['requestId'] ?? null;
        if (is_string($requestId) && $requestId !== '') {
            $headers['x-request-id'] = $requestId;
        }

        return $headers;
    }
}
