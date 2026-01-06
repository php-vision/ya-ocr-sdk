<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Auth;

final readonly class StaticIamTokenProvider implements IamTokenProviderInterface
{
    public function __construct(private string $token, private ?\DateTimeImmutable $expiresAt = null)
    {
    }

    public function getIamToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
