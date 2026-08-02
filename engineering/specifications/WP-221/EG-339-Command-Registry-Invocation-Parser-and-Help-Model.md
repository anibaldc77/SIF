---
id: EG-339
title: Command Registry Invocation Parser and Help Model
summary: Defines deterministic command registration, alias resolution, token parsing, invocation validation and structured help metadata for the SIF Developer CLI.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-01
updated: 2026-08-01
work_package: WP-221
tags:
  - foundation
  - cli
  - registry
  - parser
  - help
depends_on:
  - EG-337
  - EG-338
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-339 — Command Registry, Invocation Parser and Help Model

## 1. Purpose

I3 defines deterministic command registration and resolution, parsing of process tokens into the immutable invocation model, and renderer-neutral help metadata.

## 2. Command contract and registry

Commands SHALL implement an explicit contract exposing metadata and execution. Registration SHALL use canonical names and declared aliases only. Reflection-based command discovery is prohibited.

The registry SHALL reject collisions between canonical names and aliases and SHALL expose commands in canonical lexical order.

## 3. Parser boundary

The parser SHALL consume a list of already-tokenized strings and an explicit environment map. It SHALL NOT read `argv`, standard input or process environment directly.

Supported syntax includes long options, `--name=value`, single-character shortcuts, repeatable valued options, `--` termination, non-interactive mode and governed verbosity flags.

The parser SHALL reject unknown options, missing option values, illegal repetition, missing required arguments and excess positional arguments.

## 4. Canonical invocation

Aliases SHALL resolve to canonical command names before `CliInvocation` is created. This ensures logging, auditing and command execution use one stable identity.

## 5. Help model

Help SHALL be represented structurally and independently of terminal rendering. It SHALL include canonical name, usage, descriptions, arguments, options, aliases and operational safety metadata.

## 6. Scope boundary

I3 does not introduce console I/O, a console kernel, rendering, command execution orchestration or process exit handling. Those responsibilities belong to I4.
