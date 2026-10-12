<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WilyCode\ProfilerPhp\NullProfiler;

final class NullProfilerTest extends TestCase
{
    public function testItExecutesCallbacksWithoutCollectingMetricsOrTraces(): void
    {
        $profiler = new NullProfiler();
        $calls = 0;

        self::assertSame(42, $profiler->measure('sample', static function () use (&$calls): int {
            ++$calls;
            return 42;
        }));

        self::assertSame('done', $profiler->trace('sample', ['type' => 'test'], static function () use (&$calls): string {
            ++$calls;
            return 'done';
        }));

        $profiler->start('sample')->stop();

        self::assertSame(2, $calls);
        self::assertSame([], $profiler->getMetrics());
        self::assertSame([], $profiler->getTraces());
    }

    public function testItPropagatesExceptionsFromCallbacks(): void
    {
        $profiler = new NullProfiler();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected exception');

        $profiler->measure('sample', static function (): never {
            throw new RuntimeException('Expected exception');
        });
    }
}
