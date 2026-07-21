# EG-019 — CLI Core Model

- **Work Package:** WP-104
- **Increment:** 1 of 6
- **Status:** Ready for integration
- **Version:** 1.0.0
- **Date:** 2026-07-21
- **Depends on:** EG-018, WP-103
- **Target:** SIF Builder v2.0.0-alpha1

## 1. Purpose

This increment establishes the immutable public model used by the SIF Builder command-line adapter. It defines process outcomes, parsed command input, command execution results, and the minimum command/output contracts without implementing argument parsing, dispatch, concrete commands, Engine composition, or executable scripts.

## 2. Scope

The increment introduces:

- `ExitCode`;
- immutable `CommandInput`;
- immutable `CommandResult`;
- `CommandInterface`;
- `OutputInterface`;
- base and validation exceptions;
- unit tests for normalization, invariants, and stable exit codes.

## 3. Namespace

```text
Sif\Builder\Cli
```

Composer mappings required by EG-018:

```json
"Sif\\Builder\\Cli\\": "tools/builder/src/Cli/"
```

Tests:

```json
"Sif\\Builder\\Tests\\Cli\\": "tools/builder/tests/Cli/"
```

## 4. Exit codes

`ExitCode` is a backed enum and is the only governed source of process code values:

```text
SUCCESS                0
INVALID_USAGE          2
CONFIGURATION_ERROR    3
VALIDATION_FAILED      4
GENERATION_FAILED      5
PARTIAL_SUCCESS        6
INTERNAL_ERROR        10
```

Integer literals for these outcomes must not be scattered through commands or executable scripts.

## 5. Command input

`CommandInput` represents already parsed command data. It does not parse `argv` and does not access the filesystem.

It contains:

- normalized command name;
- ordered positional arguments;
- named options with one or more ordered values;
- ordered boolean flags.

Option values are represented as:

```php
array<string, list<string>>
```

This supports repeated options such as:

```text
--analyzer=reference.broken
--analyzer=repository.metadata
```

### 5.1 Normalization

Command, option, and flag names:

- are trimmed;
- are converted to lowercase;
- may be supplied with leading `--`;
- must match `^[a-z0-9]+(?:[._-][a-z0-9]+)*$`.

Argument and option values are preserved verbatim except that null bytes are rejected.

### 5.2 Invariants

The model rejects:

- invalid or empty names;
- non-string arguments or option values;
- non-list option values;
- options without values;
- duplicate normalized option names;
- duplicate normalized flags;
- a name used simultaneously as option and flag;
- null bytes in user-controlled values.

The parser introduced by Increment 2 must create this model only after syntactic parsing.

## 6. Command result

`CommandResult` contains:

- `ExitCode`;
- optional standard-output payload;
- optional standard-error payload;
- optional WP-103 `BuilderResult`;
- optional safe failure summary.

It does not write streams and does not terminate the process.

### 6.1 Invariants

- successful results cannot contain a failure summary;
- unsuccessful results require a non-empty safe failure summary;
- payloads cannot contain null bytes;
- empty payload strings normalize to `null`;
- the failure factory rejects `SUCCESS`.

`CommandResult::failure()` uses the safe failure summary as standard-error payload when no explicit error payload is supplied.

## 7. Contracts

### 7.1 Command

```php
interface CommandInterface
{
    public function name(): string;
    public function description(): string;
    public function execute(CommandInput $input): CommandResult;
}
```

Commands return results; they never call `exit()`.

### 7.2 Output

```php
interface OutputInterface
{
    public function write(string $content): void;
    public function writeError(string $content): void;
}
```

The production stream adapter and in-memory test adapter are deferred to later increments.

## 8. Exceptions

Expected model validation failures derive from `CliException`:

- `InvalidCommandInputException`;
- `InvalidCommandResultException`.

These exceptions represent invariant violations. Mapping them to user-facing output and exit codes belongs to the application layer.

## 9. Public API

The following are public from this increment:

- `ExitCode` names and integer values;
- `CommandInput` constructor and query methods;
- `CommandResult` constructor and factories;
- `CommandInterface`;
- `OutputInterface`.

Changes require an explicit specification amendment and compatibility review.

## 10. Deferred work

This increment intentionally excludes:

- raw `argv` model;
- argument parser;
- option definitions;
- command registry;
- application dispatch;
- concrete commands;
- Engine factory and request mapping;
- reporters and exit-code mapper;
- stdout/stderr adapters;
- executable scripts.

## 11. Acceptance criteria

The increment is accepted when:

1. exit-code values exactly match EG-018;
2. command input is immutable and supports repeated options;
3. invalid names, types, duplicates, collisions, and null bytes are rejected;
4. command result enforces success/failure invariants;
5. command and output contracts have no dependency on terminal globals;
6. tests pass under PHP 8.2 and PHPUnit 10;
7. PHPStan level 8 reports no errors;
8. Composer validation and whitespace checks pass.
