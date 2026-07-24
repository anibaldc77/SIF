---
id: EG-211
title: Dotenv Loader
summary: Deterministic parsing and immutable provider loading for local .env configuration sources.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - dotenv
  - environment
  - foundation
  - parser
work_package: WP-204
depends_on:
  - EG-210
related_adrs: []
supersedes: null
superseded_by: null
increment: I2
---

# EG-211 — Dotenv Loader

## 1. Purpose

Define a dependency-free and deterministic loader for `.env` sources that exposes parsed values through `EnvironmentProviderInterface` without mutating PHP process globals.

## 2. Scope

This increment provides:

- parsing from strings;
- loading from filesystem paths;
- immutable provider snapshots;
- blank lines and full-line comments;
- optional `export` prefixes;
- unquoted, single-quoted and double-quoted values;
- double-quoted escape sequences;
- inline comments for unquoted values;
- `${NAME}` expansion;
- `${NAME:-default}` fallback expansion;
- explicit syntax, source and resolution failures;
- unit tests.

This increment does not integrate environment loading into `Application`, `Bootstrap` or `Lifecycle` and does not write parsed values into `$_ENV`, `$_SERVER` or the process environment.

## 3. Parsing model

Each non-empty, non-comment line is an assignment composed of a valid environment variable name, the `=` operator and a value. Variable names must match `[A-Za-z_][A-Za-z0-9_]*`.

Whitespace surrounding the assignment operator is ignored. An optional `export` token may precede the variable name.

### 3.1 Unquoted values

Unquoted values are trimmed on the right. A `#` begins an inline comment only when it is the first character or is preceded by whitespace. Variable expansion is enabled.

### 3.2 Single-quoted values

Single-quoted values are literal. Escape sequences and variable expansion are disabled.

### 3.3 Double-quoted values

Double-quoted values support `\n`, `\r`, `\t`, `\"`, `\\` and `\$`. Variable expansion is enabled after escape processing.

## 4. Expansion model

`${NAME}` resolves in this order:

1. values parsed earlier in the same source;
2. the optional fallback `EnvironmentProviderInterface`.

An unresolved mandatory reference fails explicitly. `${NAME:-default}` returns the supplied default when neither source contains the variable.

Forward references are therefore invalid unless supplied by the fallback provider. This keeps parsing single-pass and deterministic.

## 5. File loading

`DotenvEnvironmentProvider::fromFile()` requires an existing readable regular file. Parsed values are retained as an immutable snapshot and the source file is not read again after construction.

## 6. Security and isolation

- loading never mutates process globals;
- parsing does not execute PHP or shell syntax;
- command substitution is unsupported;
- only explicit `${...}` expansion is recognized;
- source failures retain the affected path but do not expose unrelated environment values.

## 7. Deferred work

- runtime/application integration belongs to WP-204-I3;
- environment-specific source discovery belongs to runtime composition;
- typed conversion remains a configuration-facing responsibility;
- advanced shell parameter operators are outside this increment.

## 8. Acceptance criteria

- PHP 8.2 syntax validation succeeds;
- all dotenv loader tests pass;
- the existing environment-provider tests remain green;
- PHPStan level 8 reports no errors;
- the complete repository test suite remains green;
- SIF Builder reports zero diagnostics and deterministic artifacts.
