<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Concurrency;

final class SequentialRunner implements RunnerInterface
{
    public function run(array $tasks): array
    {
        $results = [];
        foreach ($tasks as $task) {
            $results[] = $task();
        }

        return $results;
    }
}
