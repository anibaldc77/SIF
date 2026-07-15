# Quality Gate

Every merge candidate must pass:

1. `composer validate --strict`
2. `composer test`
3. `composer analyse` at PHPStan level 8 with zero errors
4. `composer style:check` in dry-run mode
5. Valid `component.json` and `component.lock`
6. Updated documentation and Work Package implementation report
7. A clean Git worktree after validation

Failures are fixed in the Work Package branch; they are never waived by changing production behavior without an approved specification or ADR.
