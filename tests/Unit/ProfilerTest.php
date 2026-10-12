<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WilyCode\ProfilerPhp\Profiler;
use WilyCode\ProfilerPhp\ServerTimingFormatter;

final class ProfilerTest extends TestCase
{
    public function testMeasureAggregatesRepeatedMetric(): void
    {
        $profiler = new Profiler();
        $profiler->measure('db.query', static fn (): int => 1);
        $profiler->measure('db.query', static fn (): int => 2);

        $metric = $profiler->getMetrics()['db.query'];
        self::assertSame(2, $metric->count());
        self::assertGreaterThanOrEqual(0.0, $metric->durationMilliseconds());
    }

    public function testMeasureStopsWhenCallbackThrows(): void
    {
        $profiler = new Profiler();

        try {
            $profiler->measure('controller', static function (): never {
                throw new RuntimeException('fail');
            });
        } catch (RuntimeException) {
        }

        self::assertSame(1, $profiler->getMetrics()['controller']->count());
    }


    public function testTraceKeepsIndividualContextAndAggregatesMetric(): void
    {
        $profiler = new Profiler();

        $result = $profiler->trace(
            'db.query',
            ['operation' => 'fetchAll', 'sql' => 'SELECT id FROM users'],
            static fn (): array => [['id' => 1]],
        );

        self::assertSame([['id' => 1]], $result);
        self::assertSame(1, $profiler->getMetrics()['db.query']->count());
        self::assertCount(1, $profiler->getTraces());
        self::assertSame('db.query', $profiler->getTraces()[0]->name());
        self::assertSame(
            ['operation' => 'fetchAll', 'sql' => 'SELECT id FROM users'],
            $profiler->getTraces()[0]->context(),
        );
        self::assertGreaterThanOrEqual(0.0, $profiler->getTraces()[0]->durationMilliseconds());
    }

    public function testFormatsServerTiming(): void
    {
        $profiler = new Profiler();
        $profiler->measure('db.query', static fn (): null => null);

        $value = new ServerTimingFormatter()->format($profiler->getMetrics());

        self::assertStringContainsString('db.query;dur=', $value);
        self::assertStringContainsString('desc="1 call"', $value);
    }
}
