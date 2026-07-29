---
id: EG-299
title: Requirement Probes and Deterministic Assessment
summary: Defines read-only requirement probes, immutable results, required and optional severities, deterministic ordering and compiled assessment reports for the SIF Installer.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-216
tags:
  - foundation
  - installer
  - requirements
  - deterministic-assessment
depends_on:
  - EG-297
  - EG-298
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-299 — Requirement Probes and Deterministic Assessment

## 1. Purpose

WP-216-I3 introduces the read-only requirement evaluation boundary used before installation planning.

The increment SHALL NOT register installation steps, compile dependency graphs, describe mutations, execute installation behavior or integrate with runtime boot.

## 2. Probe contract

A requirement probe SHALL expose:

- a stable `RequirementIdentifier`;
- a `RequirementSeverity`;
- an integer priority;
- a read-only `probe()` operation receiving an immutable `InstallationRequest`.

A probe SHALL return exactly one immutable `RequirementProbeResult` for its own identifier and declared severity.

## 3. Severity

The governed severities are:

- `required`: failure prevents planning from proceeding;
- `optional`: failure produces a warning but does not prevent planning.

Severity describes planning impact and does not imply logging level.

## 4. Result model

A result SHALL contain:

- requirement identifier;
- severity;
- status (`passed` or `failed`);
- a bounded, non-empty diagnostic message.

Messages SHALL be safe for diagnostics and SHALL NOT contain secret option values.

## 5. Deterministic assessment

The assessor SHALL:

1. normalize the supplied iterable into a registration list;
2. reject duplicate identifiers;
3. order probes by ascending priority;
4. preserve registration order for equal priorities;
5. invoke each probe exactly once;
6. validate identifier and severity consistency;
7. return an immutable report preserving assessed order.

Equivalent probe registrations and requests SHALL produce equivalent result ordering.

## 6. Failure handling

Invalid probe registrations and inconsistent results SHALL throw typed Installer exceptions.

A throwable raised by a probe SHALL be wrapped in `RequirementProbeExecutionException`, preserving the original throwable as the previous cause.

## 7. Compiled report

`RequirementAssessmentReport` SHALL:

- preserve ordered unique results;
- reject duplicate identifiers;
- report whether planning can proceed;
- report whether optional failures exist;
- expose a deterministic summary.

An empty report is valid and can proceed.

## 8. Safety invariants

- Probes SHALL NOT mutate application state.
- Assessment SHALL NOT authorize execution.
- Result summaries SHALL remain secret-safe.
- Probe exceptions SHALL not disclose request option values.
- No filesystem, database, network or environment adapter is introduced by this increment.

## 9. Compatibility

The implementation is additive under `Sif\Foundation\Installer` and does not modify existing runtime signatures.

## 10. Acceptance criteria

I3 is accepted when:

1. probe contracts and severities are explicit;
2. results and reports are immutable;
3. required failures block proceeding;
4. optional failures produce warnings only;
5. duplicate probes fail deterministically;
6. ordering uses priority and registration order;
7. inconsistent results fail with typed exceptions;
8. probe throwables preserve their original cause;
9. focused PHPUnit tests succeed;
10. PHPStan level 8 succeeds;
11. governed metadata validates with zero diagnostics.
