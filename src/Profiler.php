<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp;

use WilyCode\Profiler\ProfileMetric;
use WilyCode\Profiler\ProfilerInterface;
use WilyCode\Profiler\ProfileSpanInterface;
use WilyCode\Profiler\ProfileTrace;

final class Profiler implements ProfilerInterface
{
    /** @var array<string, array{count:int,nanoseconds:int}> */
    private array $metrics = [];

    /** @var list<ProfileTrace> */
    private array $traces = [];

    public function start(string $name): ProfileSpanInterface
    {
        $this->assertName($name);

        return new ProfileSpan(
            $name,
            hrtime(true),
            $this->record(...),
        );
    }

    public function measure(string $name, callable $callback): mixed
    {
        $span = $this->start($name);

        try {
            return $callback();
        } finally {
            $span->stop();
        }
    }

    public function trace(string $name, array $context, callable $callback): mixed
    {
        $this->assertName($name);
        $startedAt = hrtime(true);

        try {
            return $callback();
        } finally {
            $nanoseconds = max(0, hrtime(true) - $startedAt);
            $this->record($name, $nanoseconds);
            $this->traces[] = new ProfileTrace(
                $name,
                $nanoseconds / 1_000_000,
                $context,
            );
        }
    }

    public function getMetrics(): array
    {
        $result = [];

        foreach ($this->metrics as $name => $metric) {
            $result[$name] = new ProfileMetric(
                $name,
                $metric['count'],
                $metric['nanoseconds'] / 1_000_000,
            );
        }

        return $result;
    }

    public function getTraces(): array
    {
        return $this->traces;
    }

    private function record(string $name, int $nanoseconds): void
    {
        $metric = $this->metrics[$name] ?? ['count' => 0, 'nanoseconds' => 0];
        $metric['count']++;
        $metric['nanoseconds'] += max(0, $nanoseconds);
        $this->metrics[$name] = $metric;
    }

    private function assertName(string $name): void
    {
        if ($name === '') {
            throw new \InvalidArgumentException('Profile metric name cannot be empty.');
        }
    }
}
