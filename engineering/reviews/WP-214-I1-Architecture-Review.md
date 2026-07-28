---
id: WP-214-I1-REVIEW
title: WP-214-I1 Error Handling and Recovery 2.0 Architecture Review
summary: Reviews the proposed typed, deterministic, policy-driven, secret-safe, reportable, recovery-safe, and compatibility-first architecture for Error Handling and Recovery 2.0.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-214
tags:
  - foundation
  - errors
  - recovery
  - diagnostics
  - architecture
  - review
depends_on:
  - EG-281
  - EG-218
  - EG-226
  - EG-233
  - EG-249
  - EG-257
  - EG-265
  - EG-273
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-214-I1 — Error Handling and Recovery 2.0 Architecture Review

## Scope

WP-214-I1 defines the architecture, responsibility boundaries, immutable model, classification process, recovery policy, reporting isolation, recursion controls, compatibility strategy, integration points, delivery sequence, and completion criteria for Error Handling and Recovery 2.0.

It adds no production PHP code and does not alter Runtime, exceptions, diagnostics, Event Dispatcher, Event Observation, Execution Context, Audit, Persistence, Container, Configuration, Module Registry, Structured Logging, or current application behavior.

## Need review

The selected work package addresses a real cross-cutting gap.

SIF already has mature subsystem-specific failure types and operational reporting. What remains missing is a governed answer to classification and recovery. Without that boundary, each integration risks inventing its own transient/permanent distinctions, retry rules, disclosure behavior, and reporter failure policy.

WP-214 is therefore a coherent successor to Structured Logging 2.0. Logging now provides a safe operational output channel, while WP-214 defines the authoritative failure envelope and recovery decision that may be projected into that channel.

## Boundary review

The architecture correctly preserves the authority of:

- exceptions for executable failure semantics;
- diagnostics for typed conditions;
- logging for operational observations;
- audit for accountability;
- events for occurrences and extension;
- owning runtime boundaries for action execution.

This prevents the error subsystem from becoming an all-purpose exception framework, response renderer, scheduler, service locator, or process supervisor.

## Core model review

An immutable `FailureEnvelope` is appropriate because classification, policy evaluation, and multiple reporters must observe a stable representation.

Separating category, severity, disposition, origin, and recovery action avoids overloaded status fields. In particular, failure severity must remain distinct from logging level, and transient disposition must not automatically authorize retry.

Preserving the original throwable is mandatory. The envelope may expose a safe projection, but it must never replace the causal object or weaken its chain.

## Classification review

Deterministic classifier composition is approved.

Stable identity, explicit priority, no-match semantics, and a terminal unknown fallback provide predictable behavior without forcing all exceptions into a new inheritance hierarchy.

Classifiers must remain side-effect free. Logging or reporting during classification would create recursion and make results depend on infrastructure availability.

## Recovery-policy review

The canonical actions `continue`, `degrade`, `retry`, `abort`, and `rethrow` are sufficient for the Foundation boundary.

The subsystem correctly returns decisions instead of executing them. This preserves ownership: a transaction boundary, worker, HTTP adapter, CLI command, or Runtime lifecycle may each execute the same decision differently.

Defaulting unknown failures to rethrow or abort is safer than implicit continuation.

## Retry review

Retry guidance is deliberately descriptive.

The architecture correctly excludes sleeping, scheduling, queue mutation, and hidden randomness. A retry must require both a retryable classification and explicit policy, and callers must still establish idempotency where necessary.

This is especially important for persistence and module lifecycle failures, where replay may duplicate effects.

## Metadata and disclosure review

Reusing the bounded normalization and redaction principles from Structured Logging 2.0 creates a consistent security boundary.

The prohibition on automatic request bodies, environment dumps, arbitrary object properties, and local variables is approved. Failure paths are precisely where accidental disclosure is most likely.

Distinct internal and public-safe views should remain possible in later increments without introducing protocol-specific rendering into the core.

## Reporting review

Provider-neutral reporters and isolated failures are appropriate.

A reporter failing while another failure is being handled must never replace the original cause. The handling result should expose reporter failures for diagnostics, while a reduced emergency reporter remains terminal and independent of normal composition.

The logging adapter must include explicit recursion protection because the logging subsystem itself may be the failing dependency.

## Recursion review

Bounded handling scopes, duplicate protection, terminal emergency behavior, and preservation of the first throwable are approved.

The implementation must avoid a mutable unscoped global boolean. Nested handling, tests, long-running workers, and fibers require ownership and guaranteed scope restoration.

## Plan review

Compiling all behavior into an immutable `ErrorHandlingPlan` before publication is consistent with Configuration 2.0, Container 2.0, Module Registry 2.0, and Structured Logging 2.0.

The plan provides one place to validate identities, order, policy conflicts, retry bounds, disclosure rules, reporter composition, defaults, and fingerprint material before any live failure occurs.

Secrets, throwable messages, timestamps, closures, and object identities must remain outside fingerprints.

## Integration review

### Runtime

Optional integration preserves current boot and shutdown behavior. Existing `BootResult` and lifecycle exception semantics remain authoritative.

### Structured Logging

A reporter bridge is appropriate, provided logging failures are isolated and cannot recursively enter the same error plan.

### Execution Context

Safe correlation metadata improves incident analysis. Absence of context must remain valid.

### Event Observation

Listener failures may be adapted without changing dispatcher ordering or fail-fast policy.

### Audit

Only approved operational facts may be emitted. Raw traces and confidential metadata must not be copied automatically.

### Persistence

Classification may identify timeouts, conflicts, or unavailable dependencies, but retry still requires explicit idempotency and policy.

### Container and Configuration

Composition and validation belong at bootstrap. Live handling must avoid uncontrolled resolution and mutable configuration reads.

### Module Registry

Declarative owner-validated contributions are consistent with the module architecture. Modules must not mutate a published plan.

## Compatibility review

The additive strategy is approved.

No existing exception must inherit from a new base class. No global PHP handler should be installed during this work package. Applications without an `ErrorHandlingPlan` must retain exactly their existing behavior.

Protocol adapters and global-handler installation require separate, explicit governance because they affect restoration, nesting, output, and process-level semantics.

## Security review

The architecture treats failure data as potentially sensitive and correctly requires:

- bounded metadata;
- secret redaction;
- controlled message and trace disclosure;
- no implicit environment capture;
- no arbitrary object traversal;
- safe fingerprints;
- isolated reporter failures;
- terminal emergency behavior.

These requirements are mandatory rather than optional hardening.

## Determinism review

Classifier order, policy order, selected rule, reporter order, normalized metadata, decisions, diagnostics, and plan fingerprints are all explicitly deterministic.

Injected clocks and identifier factories keep tests reproducible and prevent hidden runtime dependencies.

## Delivery review

The eight-increment sequence is approved:

1. architecture;
2. core model;
3. classification;
4. recovery policy;
5. safe metadata;
6. reporting;
7. orchestration;
8. runtime integration and completion.

The sequence builds the authoritative model before adapters and delays Runtime modification until the subsystem is independently testable.

## Risks and controls

| Risk | Required control |
|---|---|
| Original exception is hidden | Preserve original throwable and chain |
| Unknown failure is swallowed | Safe default to rethrow or abort |
| Retry duplicates side effects | Explicit policy and caller-owned idempotency |
| Failure metadata leaks secrets | Bounded normalization and redaction |
| Reporter masks application failure | Isolated reporting result |
| Logging/error recursion | Scoped reentry guard and terminal emergency path |
| Policy result varies by registration order | Stable identities, priority, and deterministic compilation |
| Modules mutate live behavior | Immutable plan and declarative contributions |
| Core becomes HTTP-specific | Keep protocol mapping outside WP-214 |
| Global handlers break nesting | Defer process-global integration |

## Review decision

WP-214-I1 is approved as the architecture baseline for Error Handling and Recovery 2.0.

Implementation may begin with:

> **WP-214-I2 — Core failure model, identifiers, categories, severity, disposition, origins, envelope, clock boundary, and failure taxonomy.**
