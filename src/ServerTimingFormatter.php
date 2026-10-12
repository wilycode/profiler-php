<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp;

use WilyCode\Profiler\ProfileMetric;

final readonly class ServerTimingFormatter
{
    /** @param array<string, ProfileMetric> $metrics */
    public function format(array $metrics): string
    {
        $parts = [];

        foreach ($metrics as $metric) {
            $name = $this->metricName($metric->name());
            $duration = rtrim(rtrim(number_format($metric->durationMilliseconds(), 3, '.', ''), '0'), '.');
            $description = $metric->count() === 1
                ? '1 call'
                : sprintf('%d calls', $metric->count());

            $parts[] = sprintf('%s;dur=%s;desc="%s"', $name, $duration === '' ? '0' : $duration, $description);
        }

        return implode(', ', $parts);
    }

    private function metricName(string $name): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9!#$%&\'*+.^_`|~-]+/', '-', $name);

        return is_string($normalized) && $normalized !== '' ? $normalized : 'metric';
    }
}
