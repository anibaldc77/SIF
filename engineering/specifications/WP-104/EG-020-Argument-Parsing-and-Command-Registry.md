# EG-020 — Argument Parsing and Command Registry

- **Work Package:** WP-104
- **Increment:** 2 of 6
- **Status:** Implemented
- **Version:** 1.0.0
- **Depends on:** EG-018, EG-019
- **Target:** SIF Builder v2.0.0-alpha1

## 1. Purpose

This increment transforms an immutable raw argument vector into `CommandInput` and introduces deterministic command registration and lookup. It does not dispatch or execute commands.

## 2. Raw input

`ArgvInput` stores terminal tokens without consulting global state. `fromPhpArgv()` removes the executable token and keeps the remaining tokens unchanged.

## 3. Parsing rules

The parser:

- requires the command name as the first token;
- accepts positional arguments;
- accepts `--name=value` and `--name value`;
- accumulates repeatable value options;
- recognizes governed boolean flags;
- treats all tokens after `--` as positional arguments;
- rejects short options, unknown options, empty required values and conflicting flags;
- does not access the filesystem or environment.

Governed value options are `repository`, `output`, `policy`, `analyzer`, `generator` and `format`.

Governed flags are `no-write`, `quiet`, `verbose`, `help`, `version`, `strict` and `lenient`.

## 4. Command registry

`CommandRegistry`:

- normalizes command names;
- preserves insertion order;
- rejects duplicate normalized names;
- performs exact normalized lookup;
- exposes deterministic enumeration;
- supports idempotent freezing;
- rejects registration after freezing.

Aliases and command execution remain outside this increment.

## 5. Diagnostics and exceptions

Invalid input is represented by typed CLI exceptions. Parser and registry exceptions must be translated into stable `CommandResult` and `ExitCode` values by the application layer in a later increment.

## 6. Acceptance criteria

- raw argv is immutable and validated;
- parsing is deterministic and side-effect free;
- repeated analyzer and generator options retain order;
- end-of-options behavior is covered by tests;
- registry ordering and freezing are covered by tests;
- no Engine dependency is introduced into the parser or registry.
