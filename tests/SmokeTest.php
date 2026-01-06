<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Tests;

use PhpVision\YandexVision\Concurrency\SequentialRunner;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testSequentialRunnerExecutesTasks(): void
    {
        $runner = new SequentialRunner();
        $results = $runner->run([
            static fn () => 1,
            static fn () => 2,
        ]);

        self::assertSame([1, 2], $results);
    }
}
