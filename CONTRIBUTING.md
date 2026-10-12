# Contributing to WilyCode Profiler PHP

Thank you for helping improve this package.

## Before submitting a change

1. Open an issue to discuss major API changes.
2. Keep each pull request focused and include a short explanation.
3. Add or update tests for behavioral changes.
4. Run the checks below before opening a pull request:

```bash
composer install
composer validate --strict
composer test
```

Please keep the public profiling contracts compatible with [`wilycode/profiler`](https://github.com/wilycode/profiler). Update `README.md` and `CHANGELOG.md` whenever behavior or the public API changes.

## The WilyCode ecosystem

This is one of the independent packages in [WilyCode](https://github.com/wilycode/wilycode). See the central repository for an overview of the ecosystem.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
