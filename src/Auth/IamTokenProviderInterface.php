<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Auth;

interface IamTokenProviderInterface
{
    public function getIamToken(): string;

    public function getExpiresAt(): ?\DateTimeImmutable;
}
