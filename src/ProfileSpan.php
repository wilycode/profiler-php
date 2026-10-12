<?php

declare(strict_types=1);

namespace WilyCode\ProfilerPhp;

use Closure;
use WilyCode\Profiler\ProfileSpanInterface;

final class ProfileSpan implements ProfileSpanInterface
{
    private bool $stopped = false;

    /** @param Closure(string, int):void $record */
    public function __construct(
        private readonly string $name,
        private readonly int $startedAt,
        private readonly Closure $record,
    ) {}

    public function stop(): void
    {
        if ($this->stopped) {
            return;
        }

        $this->stopped = true;
        ($this->record)($this->name, hrtime(true) - $this->startedAt);
    }
}
