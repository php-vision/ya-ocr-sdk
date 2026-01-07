<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Ocr\Enum\LanguageCode;
use PhpVision\YandexVision\Ocr\Enum\OcrModel;

final readonly class OcrOptions
{
    private ?string $folderId;
    private ?string $requestId;
    private ?string $mimeType;

    /**
     * @param array<int, LanguageCode> $languageCodes
     */
    private function __construct(
        private array $languageCodes = [],
        private ?OcrModel $model = null,
        ?string $folderId = null,
        ?string $requestId = null,
        ?string $mimeType = null
    ) {
        if ($folderId !== null && trim($folderId) === '') {
            throw new ValidationException('folderId must be a non-empty string.');
        }
        if ($requestId !== null && trim($requestId) === '') {
            throw new ValidationException('requestId must be a non-empty string.');
        }
        if ($mimeType !== null && trim($mimeType) === '') {
            throw new ValidationException('mimeType must be a non-empty string.');
        }
        $this->folderId = $folderId;
        $this->requestId = $requestId;
        $this->mimeType = $mimeType;
    }

    public static function create(): self
    {
        return new self();
    }

    public function withLanguageCodes(LanguageCode ...$codes): self
    {
        return new self(array_values($codes), $this->model, $this->folderId, $this->requestId, $this->mimeType);
    }

    public function withModel(OcrModel $model): self
    {
        return new self($this->languageCodes, $model, $this->folderId, $this->requestId, $this->mimeType);
    }

    public function withoutModel(): self
    {
        return new self($this->languageCodes, null, $this->folderId, $this->requestId, $this->mimeType);
    }

    public function withFolderId(string $folderId): self
    {
        return new self($this->languageCodes, $this->model, $folderId, $this->requestId, $this->mimeType);
    }

    public function withoutFolderId(): self
    {
        return new self($this->languageCodes, $this->model, null, $this->requestId, $this->mimeType);
    }

    public function withRequestId(string $requestId): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, $requestId, $this->mimeType);
    }

    public function withoutRequestId(): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, null, $this->mimeType);
    }

    public function withMimeType(string $mimeType): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, $this->requestId, $mimeType);
    }

    public function withoutMimeType(): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, $this->requestId, null);
    }

    /**
     * @return array<int, LanguageCode>
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    public function getModel(): ?OcrModel
    {
        return $this->model;
    }

    public function getFolderId(): ?string
    {
        return $this->folderId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
}
