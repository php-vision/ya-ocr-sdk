<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests\Support;

use PhpVision\YandexVision\Auth\CredentialProviderInterface;

final readonly class StaticCredentialProvider implements CredentialProviderInterface
{
    public function __construct(private string $header)
    {
    }

    public function getAuthorizationHeader(): string
    {
        return $this->header;
    }
}
