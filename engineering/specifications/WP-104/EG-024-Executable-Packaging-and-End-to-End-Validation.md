# EG-024 — Executable Packaging and End-to-End Validation

**Work Package:** WP-104 — Builder CLI  
**Increment:** 6 of 6  
**Status:** Implemented  
**Version:** 1.0.0

## 1. Purpose

This increment publishes the Builder CLI as an executable application and closes the vertical path from the operating-system argument vector to a stable process exit code.

## 2. Runtime boundary

The executable must remain a thin adapter. It may:

1. load Composer autoloading;
2. determine the current working directory;
3. create the default application composition;
4. delegate execution to `CliRunner`;
5. terminate with the returned integer code.

It must not contain command, parsing, reporting, repository, or generation rules.

## 3. Execution flow

```text
Operating system argv
    → bin/sif-builder
    → DefaultCliApplicationFactory
    → CliRunner
    → CliApplication
    → CommandResult
    → ConsoleOutput
    → process exit code
```

## 4. Output contract

`CliRunner` is the sole coordinator of a terminal execution. It writes `CommandResult::standardOutput` through `OutputInterface::write()`, writes `standardError` through `writeError()`, and returns the backed integer value of `ExitCode`.

Output failures and unexpected runtime failures must not leak stack traces. They produce exit code `10` and a safe message when the error stream remains writable.

## 5. Default composition

`DefaultCliApplicationFactory` composes the parser, command registry, governed commands, request mapping, Engine factory, reporters, version provider, and component catalog.

The initial default extension registries are intentionally empty. WP-104 packages the platform and extension points; concrete product analyzers and generators must be introduced by later governed work packages and registered only in the composition root.

Available reporters are:

- `report.markdown`
- `report.json`

## 6. Executables

- `bin/sif-builder`: portable PHP entry point with Unix shebang.
- `bin/sif-builder.bat`: Windows wrapper preserving the PHP process exit code.

A missing Composer autoloader is a configuration failure and exits with code `3`.

## 7. Compatibility

The public compatibility surface now includes:

- executable paths;
- `CliRunner` behavior;
- stdout/stderr separation;
- integer exit codes;
- default command names;
- default reporter identifiers.

Changes require an explicit migration plan.

## 8. Validation

Required checks:

```powershell
composer validate --strict
composer dump-autoload -o
vendor\bin\phpunit tools\builder\tests\Cli
vendor\bin\phpunit
vendor\bin\phpstan analyse
php bin\sif-builder version
php bin\sif-builder help
php bin\sif-builder list
```

Expected smoke-test exit codes for `version`, `help`, and `list`: `0`.

## 9. Acceptance criteria

- The PHP executable and Windows wrapper are present.
- The executable does not contain domain rules.
- Output channels remain separated.
- Exit codes are preserved exactly.
- Default commands are reachable end to end.
- Runtime errors are represented safely.
- Unit and smoke validations pass.

## 10. Completion

This increment completes WP-104. Subsequent functionality must extend the CLI through registered commands and Engine extensions rather than adding logic to the executable.
