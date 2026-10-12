<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WilyCode\ProfilerPhp\Profiler;

final class ProfileSpanTest extends TestCase
{
    public function testStoppingASpanTwiceRecordsOnlyOneMeasurement(): void
    {
        $profiler = new Profiler();
        $span = $profiler->start('request.handle');
        $span->stop();
        $span->stop();

        self::assertSame(1, $profiler->getMetrics()['request.handle']->count());
        self::assertSame([], $profiler->getTraces());
    }

    public function testMeasureRecordsEvenWhenCallbackThrows(): void
    {
        $profiler = new Profiler();

        try {
            $profiler->measure('request.handle', static function (): never {
                throw new RuntimeException('Expected exception');
            });
            self::fail('The callback should have thrown.');
        } catch (RuntimeException $exception) {
            self::assertSame('Expected exception', $exception->getMessage());
        }

        self::assertSame(1, $profiler->getMetrics()['request.handle']->count());
    }

    public function testTraceRecordsEvenWhenCallbackThrows(): void
    {
        $profiler = new Profiler();

        try {
            $profiler->trace('db.query', ['operation' => 'select'], static function (): never {
                throw new RuntimeException('Database unavailable');
            });
            self::fail('The callback should have thrown.');
        } catch (RuntimeException $exception) {
            self::assertSame('Database unavailable', $exception->getMessage());
        }

        self::assertSame(1, $profiler->getMetrics()['db.query']->count());
        self::assertCount(1, $profiler->getTraces());
        self::assertSame(['operation' => 'select'], $profiler->getTraces()[0]->context());
    }

    public function testEmptyMetricNamesAreRejected(): void
    {
        $profiler = new Profiler();
        $this->expectException(InvalidArgumentException::class);
        $profiler->start('');
    }
}
