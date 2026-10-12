<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp;

use WilyCode\Profiler\ProfilerInterface;
use WilyCode\Profiler\ProfileSpanInterface;

final readonly class NullProfiler implements ProfilerInterface
{
    public function start(string $name): ProfileSpanInterface
    {
        return new NullProfileSpan();
    }

    public function measure(string $name, callable $callback): mixed
    {
        return $callback();
    }

    public function trace(string $name, array $context, callable $callback): mixed
    {
        return $callback();
    }

    public function getMetrics(): array
    {
        return [];
    }

    public function getTraces(): array
    {
        return [];
    }
}
