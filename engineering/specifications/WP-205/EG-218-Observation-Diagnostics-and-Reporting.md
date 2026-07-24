---
id: EG-218
title: Observation Diagnostics and Reporting
summary: Defines stable diagnostic codes, deterministic serialization, in-memory reporting, and explicit isolated reporter composition for runtime event observation failures.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-24
updated: 2026-07-24
work_package: WP-205
tags:
  - runtime
  - events
  - observation
  - diagnostics
  - reporting
depends_on:
  - EG-217
  - EG-216
  - EG-215
  - EG-214-A1
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-218 — Observation Diagnostics and Reporting

## 1. Purpose

This increment standardizes host-visible diagnostics for isolated observation failures without introducing a logging dependency, persistence policy, or Runtime authority.

## 2. Stable diagnostic code

`ObservationDiagnosticCode` SHALL expose stable machine-readable identifiers. The initial code is:

- `OBSERVATION-001`: a listener failed during isolated event observation.

Codes MUST remain independent from exception messages and class names.

## 3. Diagnostic representation

`ObservationDiagnostic` SHALL wrap an immutable `ObservationFailure` and serialize using this stable key order:

1. `code`;
2. `event_type`;
3. `cause_type`;
4. `message`;
5. `occurred_at`.

Serialization MUST NOT include stack traces, file-system paths, object dumps, or mutable host state.

## 4. In-memory reporter

`InMemoryObservationFailureReporter` SHALL:

- record diagnostics in insertion order;
- expose a typed list of diagnostics;
- expose count and empty-state inspection;
- allow explicit clearing;
- perform no external I/O.

It is intended for tests, development tooling, and host-controlled inspection. It is not a persistence mechanism.

## 5. Reporter composition

`ObservationFailureReporterComposer::combine()` SHALL apply deterministic cardinality rules:

- zero reporters: return `NullObservationFailureReporter`;
- one reporter: return the same reporter instance;
- two or more reporters: return `CompositeObservationFailureReporter` preserving argument order.

## 6. Composite isolation

`CompositeObservationFailureReporter` SHALL:

1. pass the exact same `ObservationFailure` instance to every reporter;
2. invoke reporters in insertion order;
3. continue when a reporter throws;
4. never rethrow reporter exceptions;
5. never replace or mutate the original observation failure.

Reporter isolation is intentionally silent at this layer to avoid recursive diagnostic failure chains.

## 7. Architectural boundaries

This increment MUST NOT:

- modify `Application`, `Bootstrap`, `Kernel`, `Lifecycle`, `Runtime`, or `RuntimeStateMachine`;
- add logging, filesystem, database, network, or queue dependencies;
- register reporters automatically;
- introduce global mutable state;
- make diagnostics authoritative over Runtime results.

## 8. Acceptance criteria

The increment is accepted when:

- diagnostic codes are stable and typed;
- serialization is deterministic and safe;
- in-memory diagnostics preserve order and identity;
- composite reporting preserves order and attempts every reporter;
- reporter exceptions remain isolated;
- zero, one, and multiple reporter composition are verified;
- PHPStan level 8 reports zero errors;
- Builder validation reports zero diagnostics;
- governed generation remains deterministic.
