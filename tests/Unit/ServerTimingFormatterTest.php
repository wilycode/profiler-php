<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WilyCode\Profiler\ProfileMetric;
use WilyCode\ProfilerPhp\ServerTimingFormatter;

final class ServerTimingFormatterTest extends TestCase
{
    public function testFormatsOneMetric(): void
    {
        $metric = new ProfileMetric('db.query', 1, 12.5);

        self::assertSame(
            'db.query;dur=12.5;desc="1 call"',
            (new ServerTimingFormatter())->format(['db.query' => $metric]),
        );
    }

    public function testFormatsMultipleMetricsAndNormalizesNames(): void
    {
        $metrics = [
            'db query' => new ProfileMetric('db query', 2, 0.0),
            'template.render' => new ProfileMetric('template.render', 3, 1.2357),
        ];

        self::assertSame(
            'db-query;dur=0;desc="2 calls", template.render;dur=1.236;desc="3 calls"',
            (new ServerTimingFormatter())->format($metrics),
        );
    }

    public function testEmptyCollectionProducesEmptyHeaderValue(): void
    {
        self::assertSame('', (new ServerTimingFormatter())->format([]));
    }
}
