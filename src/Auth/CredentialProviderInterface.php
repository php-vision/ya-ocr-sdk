<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Auth;

interface CredentialProviderInterface
{
    public function getAuthorizationHeader(): string;
}
