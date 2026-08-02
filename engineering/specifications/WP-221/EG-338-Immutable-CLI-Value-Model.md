---
id: EG-338
title: Immutable CLI Value Model
summary: Defines the immutable command metadata, invocation input, operational classification, interaction, verbosity, result and governed exit-code values for the SIF Developer CLI.
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
  - console
  - values
  - commands
  - input
  - exit-codes
depends_on:
  - EG-337
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-338 — Immutable CLI Value Model

## 1. Purpose

This specification defines the immutable values used at the SIF Developer CLI boundary before parsing, command registration or terminal I/O are introduced.

I2 SHALL provide validated command names, argument and option definitions, command metadata, invocation input, interaction and verbosity values, operational classification, governed exit codes and structured command results.

## 2. Command identity

A canonical command name SHALL contain at least two lowercase namespace segments separated by `:`. Each segment SHALL begin with a letter and MAY contain lowercase letters, digits and hyphens.

Valid examples:

```text
runtime:doctor
config:validate
migration:status
```

Command identity SHALL NOT be derived from PHP class names.

## 3. Argument and option definitions

Argument and option definitions SHALL be immutable and ordered.

Arguments SHALL declare description, requirement, variadic and sensitivity properties. A variadic argument SHALL be required and SHALL be the final argument in command metadata.

Options SHALL declare long name, optional single-character shortcut, value requirement, repeatability and sensitivity. Repeatable and sensitive options SHALL require values.

Command metadata SHALL reject duplicate argument names, option names, shortcuts and aliases. Required arguments SHALL NOT follow optional arguments.

## 4. Operational classification

The initial classifications are:

```text
inspection
validation
planning
mutation
maintenance
```

A command marked destructive SHALL use a mutating classification. This declaration does not itself authorize execution.

## 5. Invocation input

`CliInvocation` SHALL preserve:

- canonical command name;
- ordered positional arguments;
- named option values and boolean flags;
- process-supplied environment values;
- interaction capability;
- requested verbosity.

The value object SHALL reject null bytes and invalid environment names. It SHALL expose a safe summary containing counts and option names but not argument, option or environment values.

## 6. Exit codes

The governed exit-code categories are fixed:

```text
0 success
1 execution failure
2 invalid usage
3 command not found
4 validation failure
5 not authorized
6 requirements not satisfied
7 partial or compensated execution
8 internal failure
```

Values outside this set SHALL be rejected.

## 7. Command results

A command result SHALL combine a governed exit code with an optional message, structured data and warnings.

Safe summaries SHALL expose status, counts and data keys only. Rendering and redaction of payload values belong to later output adapters.

## 8. Exclusions

I2 does not provide:

- process argument parsing;
- command registry or alias resolution;
- help rendering;
- console kernel;
- terminal input or output;
- command execution.

These responsibilities are delivered in later WP-221 increments.
