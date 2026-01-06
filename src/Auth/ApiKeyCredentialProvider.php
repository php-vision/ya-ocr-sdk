<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Auth;

final readonly class ApiKeyCredentialProvider implements CredentialProviderInterface
{
    public function __construct(private string $apiKey)
    {
    }

    public function getAuthorizationHeader(): string
    {
        return 'Api-Key ' . $this->apiKey;
    }
}
