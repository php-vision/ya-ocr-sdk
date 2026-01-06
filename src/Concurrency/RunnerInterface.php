<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Concurrency;

interface RunnerInterface
{
    /**
     * @param array<int, callable(): mixed> $tasks
     * @return array<int, mixed>
     */
    public function run(array $tasks): array;
}
