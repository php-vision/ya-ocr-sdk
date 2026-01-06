<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

final readonly class BackoffPolicy
{
    public function __construct(private int $initialDelaySeconds = 1, private int $maxDelaySeconds = 10, private float $multiplier = 2.0)
    {
    }

    public function getInitialDelaySeconds(): int
    {
        return $this->initialDelaySeconds;
    }

    public function getMaxDelaySeconds(): int
    {
        return $this->maxDelaySeconds;
    }

    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    public function getDelayForAttempt(int $attempt): int
    {
        if ($attempt <= 0) {
            return $this->initialDelaySeconds;
        }

        $delay = (int) round($this->initialDelaySeconds * ($this->multiplier ** $attempt));

        return min($delay, $this->maxDelaySeconds);
    }
}
