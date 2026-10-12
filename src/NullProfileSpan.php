<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp;

use WilyCode\Profiler\ProfileSpanInterface;

final readonly class NullProfileSpan implements ProfileSpanInterface
{
    public function stop(): void
    {
    }
}
