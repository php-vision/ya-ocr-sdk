<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Ocr\Enum\LanguageCode;
use PhpVision\YandexVision\Ocr\Enum\OcrModel;

final readonly class OcrOptions
{
    /**
     * @param array<int, LanguageCode> $languageCodes
     */
    private function __construct(private array $languageCodes = [], private OcrModel $model = OcrModel::Page, private ?string $folderId = null, private ?string $mimeType = null, private ?string $requestId = null)
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withLanguageCodes(LanguageCode ...$codes): self
    {
        return new self(array_values($codes), $this->model, $this->folderId, $this->mimeType, $this->requestId);
    }

    public function withModel(OcrModel $model): self
    {
        return new self($this->languageCodes, $model, $this->folderId, $this->mimeType, $this->requestId);
    }

    public function withRequestId(string $requestId): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, $this->mimeType, $requestId);
    }

    public function withoutRequestId(): self
    {
        return new self($this->languageCodes, $this->model, $this->folderId, $this->mimeType, null);
    }

    /**
     * @return array<int, LanguageCode>
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    public function getModel(): OcrModel
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
