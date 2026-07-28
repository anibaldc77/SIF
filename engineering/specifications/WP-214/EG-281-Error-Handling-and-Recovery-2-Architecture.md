---
id: EG-281
title: Error Handling and Recovery 2.0 Architecture
summary: Defines a deterministic, typed, context-aware, policy-driven, reportable, recovery-safe, and compatibility-first error handling architecture for SIF Foundation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-214
tags:
  - foundation
  - errors
  - exceptions
  - recovery
  - diagnostics
  - architecture
depends_on:
  - EG-202
  - EG-213
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

# EG-281 — Error Handling and Recovery 2.0 Architecture

## 1. Purpose

WP-214 defines Error Handling and Recovery 2.0 for SIF Foundation.

The subsystem SHALL provide one governed boundary for representing failures, classifying their semantics, attaching safe execution context, evaluating explicit recovery policy, reporting operationally, preserving original causes, and integrating compatibly with Runtime, Event Observation, Execution Context, Audit, Persistence, Container, Configuration, Module Registry, and Structured Logging 2.0.

WP-214-I1 is exclusively architectural. It introduces no production PHP code and SHALL NOT change existing runtime behavior.

## 2. Architectural need

SIF already has multiple authoritative failure forms:

- typed exceptions;
- boot errors and warnings;
- subsystem diagnostics;
- event listener failures;
- persistence failures;
- container resolution failures;
- configuration validation failures;
- module lifecycle failures;
- structured log records.

These types correctly preserve subsystem ownership, but there is not yet a common, immutable, policy-driven model for answering the following cross-cutting questions:

1. What category and severity does this failure have?
2. Is it transient, permanent, invalid, unauthorized, unavailable, or unknown?
3. May execution retry, degrade, continue, abort, or rethrow?
4. Which safe context may be attached?
5. Which reporters should observe the failure?
6. How are reporter failures isolated without replacing the original cause?
7. How can adapters translate failures without weakening their semantics?

Error Handling and Recovery 2.0 fills this gap. It SHALL NOT replace subsystem exceptions, diagnostics, logging, audit, events, HTTP response handling, or process supervision.

## 3. Goals

The subsystem SHALL support:

1. immutable failure envelopes;
2. stable failure identifiers;
3. explicit categories, severity, origin, and disposition;
4. preservation of the original `Throwable`;
5. bounded, secret-safe metadata;
6. execution-context projection;
7. deterministic classifier composition;
8. explicit recovery decisions;
9. immutable recovery policies;
10. bounded retry guidance without sleeping or scheduling internally;
11. reporter-neutral failure publication;
12. isolated reporter failures;
13. structured logging and diagnostics bridges;
14. optional audit integration for accountability-relevant operational failures;
15. immutable error-handling plans and fingerprints;
16. compatibility-first runtime integration;
17. in-memory reference implementations and deterministic tests.

## 4. Non-goals

WP-214 SHALL NOT implement:

- HTTP exception rendering or status-code negotiation;
- HTML error pages;
- CLI presentation formatting;
- process restart supervision;
- job queue retry scheduling;
- circuit breakers;
- distributed tracing;
- alert delivery services;
- remote incident management;
- persistence of arbitrary exception objects;
- automatic swallowing of exceptions;
- arbitrary user-defined policy expressions;
- hidden global exception handlers;
- replacement of subsystem-specific exception taxonomies;
- mandatory third-party error-reporting dependencies.

These concerns require separate governed work packages or adapters.

## 5. Responsibility boundaries

### 5.1 Exceptions

Exceptions remain the authoritative executable failure mechanism. An envelope SHALL preserve the original throwable and SHALL NOT mutate its message, code, trace, or chain.

### 5.2 Diagnostics

Diagnostics describe typed validation or runtime conditions. A diagnostic MAY be projected into a failure envelope, but its original diagnostic type remains authoritative.

### 5.3 Logging

Logging records operational observations. Failure handling MAY emit structured log records, but a log record SHALL NOT become the authoritative failure object.

### 5.4 Audit

Audit records accountability-relevant actions and state changes. Only policy-approved operational failure facts MAY enter audit. Confidential throwable details SHALL NOT be copied by default.

### 5.5 Events

Events expose occurrences and extension points. Failure publication MAY use an explicit event adapter, but recovery policy SHALL NOT depend on uncontrolled listener behavior.

### 5.6 Recovery

Recovery decides the next permissible action. It SHALL NOT execute infrastructure-specific retries, sleep, restart processes, send responses, or mutate global state.

## 6. Core model

The core model SHALL contain immutable value objects for:

- `FailureId`;
- `FailureCategory`;
- `FailureSeverity`;
- `FailureOrigin`;
- `FailureDisposition`;
- `FailureMetadata`;
- `FailureEnvelope`;
- `RecoveryAction`;
- `RecoveryDecision`;
- optional retry guidance.

A failure envelope SHALL contain at least:

- stable identifier;
- occurrence timestamp from an injected clock;
- original throwable or typed source reference;
- category;
- severity;
- origin;
- safe message or summary;
- bounded metadata;
- optional execution-context projection;
- optional causal parent identifier;
- classification provenance.

The envelope SHALL expose no mutable collections.

## 7. Failure categories

The initial canonical category set SHALL include:

```text
validation
authentication
authorization
not_found
conflict
rate_limited
timeout
unavailable
dependency
configuration
persistence
programming
security
cancelled
unknown
```

Categories describe semantics, not presentation. Adapters MAY map categories to protocol-specific representations, but those mappings SHALL remain outside the core.

Unknown values SHALL be rejected. Extension categories require governed registration and ownership validation rather than arbitrary strings.

## 8. Failure severity

Severity SHALL be independent of logging level and SHALL express operational impact:

```text
low
moderate
high
critical
```

A bridge MAY map failure severity to a log level, but the two types SHALL remain distinct.

Severity ordering SHALL be deterministic and testable.

## 9. Failure disposition

Disposition captures whether the failure is expected to change without code or input correction:

```text
transient
permanent
invalid
forbidden
cancelled
unknown
```

Disposition SHALL guide policy but SHALL NOT alone authorize retries.

## 10. Failure classification

A classifier SHALL transform a throwable or typed failure source into classification facts without executing recovery.

Classifiers SHALL:

- implement an explicit contract;
- declare stable identity and priority;
- be evaluated in deterministic order;
- avoid side effects;
- avoid logging or reporting recursively;
- preserve original causes;
- return either a classification or no match;
- never inspect arbitrary object graphs.

The classifier chain SHALL support a terminal safe fallback to `unknown`.

Ambiguous equal-priority ownership SHALL be rejected during plan compilation when deterministic ordering cannot otherwise be proven.

## 11. Safe metadata and context

All metadata SHALL pass through the bounded normalizer and secret redactor established by Structured Logging 2.0 or an equivalent explicit contract.

The subsystem SHALL permit safe projections of:

- execution context identifier;
- correlation identifier;
- operation;
- source;
- tenant identifier where policy permits;
- subsystem and component;
- attempt number;
- dependency name;
- bounded diagnostic codes.

It SHALL NOT capture automatically:

- environment dumps;
- request bodies;
- credentials;
- stack-local variables;
- arbitrary object properties;
- full configuration snapshots;
- raw audit payloads.

Stack traces and exception messages SHALL be controlled by explicit disclosure policy.

## 12. Recovery actions

The canonical action set SHALL include:

```text
continue
degrade
retry
abort
rethrow
```

A `RecoveryDecision` SHALL explain:

- selected action;
- policy rule or owner;
- whether reporting is required;
- optional bounded retry guidance;
- optional degraded capability;
- rationale code safe for diagnostics.

`continue` and `degrade` SHALL require explicit policy. The default for unclassified failures SHALL be `rethrow` or `abort` according to the owning runtime boundary.

## 13. Retry guidance

The core MAY describe retry guidance but SHALL NOT execute timing or scheduling.

Guidance MAY include:

- maximum attempts;
- current attempt;
- delay duration;
- backoff strategy identifier;
- jitter policy identifier;
- retry-after timestamp.

Bounds SHALL prevent negative delays, unbounded attempts, overflow, and nondeterministic hidden randomness.

A retry decision SHALL require both a retryable classification and an explicit policy rule.

## 14. Recovery policies

Policies SHALL be immutable and compiled before publication.

A policy rule MAY match on:

- category;
- severity;
- disposition;
- origin subsystem;
- throwable type;
- operation;
- capability;
- attempt range.

Policy evaluation SHALL:

- use deterministic ordering;
- select one explainable result;
- reject contradictory terminal rules;
- expose provenance;
- avoid arbitrary expression evaluation;
- avoid service location during failure handling.

## 15. Reporting

Failure reporters SHALL receive an immutable envelope and decision through a provider-neutral contract.

Reference reporters MAY include:

- in-memory reporter;
- structured logging reporter;
- diagnostic collector reporter;
- null reporter;
- composed reporter.

Reporter order SHALL be deterministic. Each reporter failure SHALL be isolated and recorded in a report result without replacing or suppressing the original application failure.

A reduced emergency reporter MAY be used only when ordinary reporting fails. It SHALL avoid the Container, modules, configuration lookup, event dispatch, and normal logging pipelines.

## 16. Recursion prevention

Failure handling commonly executes while another subsystem is already failing. The architecture SHALL therefore include:

- bounded reentry depth;
- stable handling-scope identity;
- duplicate-envelope protection where appropriate;
- terminal emergency behavior;
- no recursive reporting of reporter failures through the same plan;
- preservation of the first causal throwable.

Recursion controls SHALL be scoped explicitly and SHALL NOT rely on mutable process-wide flags without ownership and reset guarantees.

## 17. ErrorHandlingPlan

Configuration, classifiers, policies, reporters, redaction rules, and integration contributions SHALL compile into an immutable `ErrorHandlingPlan`.

The plan SHALL define:

- clock;
- identifier factory;
- metadata normalizer and redactor;
- classifier chain;
- recovery policy set;
- reporter pipeline;
- disclosure policy;
- recursion policy;
- default category, severity, disposition, and action;
- stable plan fingerprint.

The plan fingerprint SHALL exclude:

- secrets;
- timestamps;
- credentials;
- object hashes;
- closures;
- mutable runtime state;
- raw exception messages and traces.

## 18. Orchestration

A provider-neutral orchestrator SHALL execute the following flow:

```text
1. Receive throwable or typed failure source
2. Establish a bounded handling scope
3. Build safe source metadata
4. Classify deterministically
5. Create immutable FailureEnvelope
6. Evaluate immutable recovery policy
7. Report through isolated reporters
8. Return an inspectable HandlingResult
```

The orchestrator SHALL NOT automatically throw, retry, sleep, terminate, render a response, or mutate application state. The owning boundary executes the selected action.

## 19. Integration

### 19.1 Runtime

Runtime integration SHALL be optional and contract-based. Existing `BootResult`, lifecycle errors, provider shutdown behavior, and original exception propagation SHALL remain authoritative.

Runtime MAY use the subsystem to classify and report boot, module, and shutdown failures and to obtain explicit recovery guidance.

### 19.2 Structured Logging 2.0

A logging reporter adapter MAY project envelopes and decisions into safe structured records. It SHALL avoid reporting its own failures through the same logging route recursively.

### 19.3 Execution Context

Active context MAY enrich envelopes through a safe projection. Failure handling without an active context SHALL remain valid.

### 19.4 Event Observation

Listener and observer failures MAY be adapted into envelopes. Existing dispatcher ordering and isolation policy SHALL not be changed implicitly.

### 19.5 Audit

Only policy-authorized operational facts SHALL be emitted. Full exception traces and confidential metadata SHALL be excluded by default.

### 19.6 Persistence

Persistence exceptions and transaction failures MAY be classified without changing repository or transaction contracts. Retry decisions SHALL never assume idempotency unless explicitly declared by the caller or policy.

### 19.7 Container 2.0

The Container MAY compose the plan before runtime publication. Live error handling SHALL NOT perform uncontrolled service lookup.

### 19.8 Configuration 2.0

Schemas, immutable snapshots, secret markers, and validation diagnostics SHALL be the configuration source. Invalid policy SHALL fail during bootstrap rather than during failure handling.

### 19.9 Module Registry 2.0

Modules MAY contribute classifiers, policy rules, and reporters through declarative, owner-validated contributions composed in resolved module order. Modules SHALL NOT mutate a live plan.

## 20. Compatibility

WP-214 SHALL be additive.

Applications that do not configure Error Handling and Recovery 2.0 SHALL continue with existing exception, boot, and shutdown semantics.

No global PHP exception or error handler SHALL be installed in the initial product integration. Such adapters require an explicit later increment or work package with restoration and nesting guarantees.

Existing public exception classes SHALL not be replaced or forced to inherit from a new base type.

## 21. Security requirements

The subsystem SHALL assume failure data may contain secrets and personal information.

It SHALL enforce:

- bounded normalization;
- recursive key-based redaction;
- policy-controlled messages and traces;
- no arbitrary object serialization;
- no automatic request or environment capture;
- no secret material in fingerprints;
- distinct internal and public-safe views;
- explicit audit disclosure policy;
- terminal handling for reporter failure.

## 22. Determinism requirements

The following SHALL be deterministic:

- classifier order;
- policy order;
- selected rule;
- reporter order;
- normalized metadata;
- envelope projection;
- recovery decision;
- plan fingerprint;
- failure and reporter result ordering.

Wall-clock time and generated identifiers SHALL enter only through injected contracts.

## 23. Diagnostics

Plan compilation and handling results SHALL expose typed, inspectable diagnostics for conditions such as:

- duplicate classifier identity;
- conflicting policy rules;
- invalid retry bounds;
- unsupported category or action;
- unsafe disclosure configuration;
- reporter failure;
- recursion suppression;
- unknown classification fallback.

Diagnostics SHALL not leak secrets or raw traces by default.

## 24. Proposed delivery sequence

WP-214 SHALL be delivered in eight increments:

1. **I1 — Architecture:** boundaries, model, policies, integration, security, and completion criteria.
2. **I2 — Core failure model:** identifiers, categories, severity, disposition, origins, envelope, clock, and taxonomy.
3. **I3 — Classification:** classifier contracts, deterministic chain, throwable matching, and safe fallback.
4. **I4 — Recovery policy:** actions, decisions, retry guidance, immutable rules, compilation, and conflict diagnostics.
5. **I5 — Safe metadata:** bounded projections, context enrichment, disclosure policy, canonical serialization, and redaction.
6. **I6 — Reporting:** reporter contracts, composition, failure isolation, emergency reporting, and handling reports.
7. **I7 — Orchestration:** immutable `ErrorHandlingPlan`, facade, handling scope, recursion prevention, and provider-neutral execution.
8. **I8 — Runtime integration and completion:** service-provider integration, lifecycle adapters, examples, compatibility tests, and completion review.

## 25. Completion criteria

WP-214 is complete only when:

- the core model is immutable and fully typed;
- original throwables remain preserved;
- classifiers and policies are deterministic;
- recovery decisions are explicit and inspectable;
- no retry executes implicitly;
- metadata is bounded and secret-safe;
- reporter failures are isolated;
- recursion is bounded;
- the plan is immutable and fingerprintable;
- runtime integration is optional;
- existing behavior without a plan is characterized and preserved;
- PHPStan level 8 passes;
- focused and complete PHPUnit suites pass;
- governed artifacts validate with zero diagnostics;
- implementation and completion reviews are published.

## 26. Architectural decision

Error Handling and Recovery 2.0 is approved for implementation as a distinct Foundation subsystem.

Its central rule is:

> Preserve the original failure, classify it deterministically, decide recovery explicitly, report it safely, and leave action execution to the owning boundary.
