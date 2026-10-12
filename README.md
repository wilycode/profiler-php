# WilyCode Profiler PHP

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/profiler-php/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/profiler-php/actions/workflows/tests.yml)

**A simple PHP implementation of the WilyCode profiling contracts.**

Measure execution time, inspect aggregated metrics, or record individual traces. No framework is required.

> **WilyCode PHP Ecosystem**
>
> Part of **[WilyCode](https://github.com/wilycode/wilycode)** — independent PHP components, each with its own repository, releases, and Composer package.
>
> **[Explore the WilyCode ecosystem →](https://github.com/wilycode/wilycode)**

## Features

- **Accurate timing** using PHP's monotonic `hrtime(true)` clock.
- **Aggregated metrics** with total duration and call count for each operation name.
- **Individual traces** with optional context for selected operations.
- **Safe callback measurement:** timing is recorded even if the callback throws.
- **`NullProfiler`** to disable recording without changing application code.
- **`ServerTimingFormatter`** to format metrics for an HTTP `Server-Timing` header.

## Requirements

- PHP 8.4 or newer.
- [`wilycode/profiler`](https://github.com/wilycode/profiler) `^0.1` for the profiling interfaces and value objects.

## Installation

```bash
composer require wilycode/profiler-php
```

Composer also installs `wilycode/profiler`, which provides the shared contracts.

## Quick start

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use WilyCode\ProfilerPhp\Profiler;

$profiler = new Profiler();

// Measure a callback and keep its return value.
$result = $profiler->measure(
    'page.render',
    static fn (): string => 'Hello, world!',
);

echo $result; // Hello, world!

// Read the total duration and call count for this operation.
foreach ($profiler->getMetrics() as $metric) {
    printf(
        '%s: %d call(s), %.2f ms\n',
        $metric->name(),
        $metric->count(),
        $metric->durationMilliseconds(),
    );
}
```

### Measure a block of code

Use `start()` and `stop()` when you need to time multiple statements. A span only records a measurement once, even if `stop()` is called again.

```php
$span = $profiler->start('request.handle');

try {
    // Handle a request.
} finally {
    $span->stop();
}
```

### Keep individual traces

Use `trace()` when you want to retain each measurement and attach simple context. Traces are also included in aggregated metrics.

```php
$articles = $profiler->trace(
    'db.query',
    ['operation' => 'fetchAll', 'table' => 'articles'],
    static fn (): array => ['Article 1', 'Article 2'],
);

foreach ($profiler->getTraces() as $trace) {
    printf('%s: %.2f ms\n', $trace->name(), $trace->durationMilliseconds());
    // $trace->context() returns the supplied context array.
}
```

**Privacy:** avoid including SQL values, personal information, passwords, access tokens, or other secrets in trace context. Keep context small and use scalar values or `null`.

## Disable profiling

Inject `NullProfiler` instead of `Profiler`. Both implement `WilyCode\Profiler\ProfilerInterface`, so callers do not need to change.

```php
use WilyCode\ProfilerPhp\NullProfiler;
use WilyCode\ProfilerPhp\Profiler;

$profiler = $debugMode ? new Profiler() : new NullProfiler();

// Both variants run the callback and return its result.
$value = $profiler->measure('page.render', static fn (): string => 'Done');
```

`NullProfiler` returns empty metric and trace collections. It skips timing and recording, although calls to its methods still execute.

## Export timing to HTTP headers

`ServerTimingFormatter` converts aggregated metrics into a value for the standard HTTP [`Server-Timing`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server-Timing) response header.

```php
use WilyCode\ProfilerPhp\ServerTimingFormatter;

$headerValue = (new ServerTimingFormatter())->format($profiler->getMetrics());
// Example: page.render;dur=0.123;desc="1 call"

// Set the HTTP response header in your application:
// $response = $response->withHeader('Server-Timing', $headerValue);
```

The formatter only builds a string. It does not send a response, write logs, or include individual traces. Actual durations vary from run to run.

## Related packages

| Package | Purpose |
| --- | --- |
| [`wilycode/profiler`](https://github.com/wilycode/profiler) | Shared profiling interfaces and value objects |
| [`wilycode/router-profiler`](https://github.com/wilycode/router-profiler) | Optional routing performance diagnostics |
| [`wilycode/database-query-executor-profiler`](https://github.com/wilycode/database-query-executor-profiler) | Optional database query diagnostics |

Each component is maintained and released separately.

## Development

```bash
composer install
composer validate --strict
composer test
```

Tests run automatically on PHP 8.4 in [GitHub Actions](https://github.com/wilycode/profiler-php/actions). They remain in the Git repository but are excluded from distribution archives.

## Contributing

Issues and pull requests are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) first.

## License

MIT © 2026 Simon Piter. See [LICENSE](LICENSE).
