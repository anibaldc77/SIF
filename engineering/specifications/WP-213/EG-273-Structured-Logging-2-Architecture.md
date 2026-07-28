---
id: EG-273
title: Structured Logging 2.0 Architecture
summary: Defines a deterministic, context-aware, secret-safe, channel-oriented, processor-driven, handler-neutral, and compatibility-first structured logging architecture for SIF Foundation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-213
tags:
  - foundation
  - logging
  - observability
  - diagnostics
  - context
  - architecture
depends_on:
  - EG-202
  - EG-213
  - EG-218
  - EG-226
  - EG-229
  - EG-233
  - EG-249
  - EG-257
  - EG-265
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-273 — Structured Logging 2.0 Architecture

## 1. Purpose

WP-213 defines Structured Logging 2.0 for SIF Foundation.

The subsystem SHALL provide deterministic structured records, explicit severity, safe contextual data, channel composition, processing pipelines, handler abstraction, failure containment, diagnostics, immutable configuration, and compatible integration with Runtime, Event Observation, Execution Context, Audit, Configuration 2.0, Container 2.0, and Module Registry 2.0.

WP-213-I1 is exclusively architectural. It introduces no production PHP code and SHALL NOT change existing runtime behavior.

## 2. Architectural need

SIF already produces several forms of operational information:

- boot warnings and errors;
- runtime lifecycle events;
- isolated observation failures;
- execution context metadata;
- audit records;
- persistence and container diagnostics;
- configuration diagnostics;
- module integration diagnostics.

These facilities have different responsibilities and SHALL remain independent. However, SIF does not yet provide one governed boundary for emitting operational log records consistently.

Structured Logging 2.0 fills that boundary. It SHALL NOT replace Audit, Event Dispatcher, diagnostics, exception handling, or domain events.

## 3. Goals

The subsystem SHALL support:

1. immutable log levels and records;
2. explicit channels;
3. message templates and structured attributes;
4. execution-context enrichment;
5. deterministic processor ordering;
6. handler-neutral delivery;
7. configurable minimum levels and channel policies;
8. secret-safe normalization and redaction;
9. bounded serialization of complex values;
10. handler failure containment;
11. emergency fallback reporting;
12. immutable logging plans and fingerprints;
13. module-provided processors and handlers through controlled contributions;
14. compatibility adapters for established logger contracts where appropriate;
15. testable in-memory reference implementations;
16. lifecycle-safe flush and shutdown.

## 4. Non-goals

WP-213 SHALL NOT implement:

- centralized log collection services;
- vendor-specific cloud exporters;
- distributed tracing;
- metrics aggregation;
- application performance monitoring;
- audit persistence;
- exception-to-HTTP conversion;
- user-facing error pages;
- arbitrary expression evaluation in logging configuration;
- remote handler discovery;
- unbounded object serialization;
- hidden global logger mutation;
- mandatory third-party logging dependencies.

Vendor adapters, tracing, metrics, and remote transports require separate governed work.

## 5. Responsibility boundaries

### 5.1 Logging

Logging records operational observations intended for diagnostics and support.

### 5.2 Audit

Audit records accountability-relevant actions and state changes. Audit SHALL NOT be implemented as ordinary logging, and log retention SHALL NOT be treated as an audit guarantee.

### 5.3 Events

Events represent occurrences and extension points. Event dispatch SHALL NOT depend on a concrete logger. Observation adapters MAY emit structured log records through an explicit logger contract.

### 5.4 Diagnostics

Diagnostics describe validation or runtime conditions in typed subsystem-specific form. A diagnostic MAY be projected into a log record, but the original diagnostic type remains authoritative.

### 5.5 Exceptions

Exceptions preserve failure semantics. Logging an exception SHALL NOT swallow, replace, or mutate it unless an explicit isolation boundary owns that policy.

## 6. Core model

The core model SHALL contain immutable value objects for:

- `LogLevel`;
- `LogChannel`;
- `LogMessage` or message template;
- `LogRecord`;
- `LogTimestamp` or an injected clock boundary;
- normalized structured attributes;
- exception metadata represented without serializing arbitrary internal state.

A log record SHALL contain at least:

- timestamp;
- level;
- channel;
- message or template;
- structured attributes;
- optional execution-context projection;
- optional throwable metadata;
- stable record identifier when configured.

Records SHALL NOT expose mutable arrays or mutable internal objects.

## 7. Levels

The normative level set SHALL be ordered and compatible with the commonly understood emergency-to-debug severity model:

```text
emergency
alert
critical
error
warning
notice
info
debug
```

Level comparison SHALL be deterministic. Unknown levels SHALL be rejected by the core model. Custom semantic categories belong in structured attributes or channels, not in ad hoc severity strings.

## 8. Channels

A channel identifies the operational source or concern of a record.

Examples include:

```text
runtime
configuration
container
modules
persistence
audit.integration
application
```

Channel identifiers SHALL use a portable canonical grammar. Channel ownership SHALL be explicit when contributed by modules.

Channels SHALL NOT imply a handler. Routing is defined by an immutable logging plan.

## 9. Message and attributes

Messages SHALL support plain text and deterministic template interpolation.

Interpolation SHALL:

- never execute code;
- never invoke arbitrary object methods;
- preserve the original structured attributes;
- use bounded normalization;
- represent missing placeholders predictably;
- avoid leaking secret values.

Structured attributes SHALL use string keys and normalized values. Reserved keys SHALL be documented and protected from accidental overwrite.

## 10. Normalization and redaction

All values entering a record SHALL pass through a deterministic normalizer before handler delivery.

The normalizer SHALL enforce configurable bounds for:

- nesting depth;
- collection size;
- string length;
- throwable chain depth;
- object representation;
- binary data representation.

Secret redaction SHALL integrate with Configuration 2.0 secret markers and the existing context redaction model where available.

The subsystem SHALL prefer omission or explicit redaction markers over partial disclosure.

## 11. Processors

Processors transform one immutable record into another immutable record before handler delivery.

Processor ordering SHALL be deterministic and explicit.

Reference processor categories MAY include:

- execution-context enrichment;
- runtime-state enrichment;
- module identity enrichment;
- throwable normalization;
- redaction;
- correlation identifiers;
- hostname or process metadata through explicit environment contracts.

Processors SHALL NOT resolve arbitrary application services during record emission unless the composition root explicitly permits that dependency.

A processor failure SHALL be handled according to the configured failure policy and SHALL NOT silently corrupt the original record.

## 12. Handlers

Handlers receive normalized immutable records.

The handler contract SHALL support:

- level and channel acceptance checks;
- record handling;
- optional flushing;
- optional shutdown;
- stable handler identity for diagnostics and fingerprints.

Initial reference handlers SHOULD include:

- in-memory handler for tests;
- stream handler for local files or standard streams;
- null handler;
- composite or fan-out handler.

Handlers SHALL NOT assume ownership of application exceptions.

## 13. Routing and logging plan

Configuration SHALL compile into an immutable `LoggingPlan` before normal operation.

The plan SHALL define:

- channels;
- minimum levels;
- processor pipelines;
- handler routing;
- failure policy;
- flush policy;
- shutdown order;
- deterministic fingerprint inputs.

The plan SHALL be validated before publication. Invalid channel references, duplicate handler identifiers, cyclic composites, impossible level ranges, and incompatible contributions SHALL fail before runtime mutation.

## 14. Failure containment

Logging is an observability facility and SHALL NOT normally replace the application failure that triggered logging.

The architecture SHALL distinguish:

- strict failures during bootstrap and plan compilation;
- isolated failures during normal record emission;
- emergency fallback failures;
- flush or shutdown failures.

During emission, handler failures SHALL be captured as structured logging diagnostics. An explicit emergency reporter MAY receive a reduced, redacted representation.

Recursive logging failure SHALL be prevented through a bounded re-entry guard.

The emergency path SHALL avoid Container resolution, module resolution, complex processors, and the failing handler chain.

## 15. Execution Context integration

Logging MAY project safe fields from the active Execution Context, including correlation, causation, actor, request, operation, and tenant identifiers where present and allowed.

Context integration SHALL be explicit and dependency-injected. Logging SHALL work without an active context.

Context projection SHALL use the established redaction rules and SHALL NOT serialize arbitrary context attachments.

## 16. Runtime integration

Runtime integration SHALL remain optional and compatibility-first.

The Runtime MAY emit structured records for:

- boot start and completion;
- boot warnings and failures;
- provider registration and boot failures;
- shutdown failures;
- module-plan publication;
- configuration freeze;
- capability publication.

Runtime SHALL depend only on logging contracts or an adapter, never on a concrete handler.

Existing boot results, events, and diagnostics remain authoritative.

## 17. Event Observation integration

The isolated event observer MAY use Structured Logging 2.0 as a failure reporter through an adapter.

Listener ordering and dispatcher fail-fast semantics defined by the Event Dispatcher SHALL remain unchanged.

Logging failures within the observation boundary SHALL be contained and SHALL NOT replace the listener failure being reported.

## 18. Audit integration

Audit and logging SHALL remain separately configurable.

Allowed integration includes:

- logging audit pipeline failures;
- logging audit sink availability diagnostics;
- projecting an audit record identifier into a log record;
- producing operational records about audit composition.

Prohibited integration includes:

- treating a log handler as an audit repository by default;
- placing full confidential audit payloads into ordinary logs;
- deriving legal retention guarantees from logging configuration.

## 19. Configuration 2.0 integration

Logging configuration SHALL be provided through Configuration 2.0 schemas and immutable snapshots.

Configuration keys SHOULD cover:

- default channel;
- channel policies;
- handler declarations;
- processor declarations;
- minimum levels;
- redaction policy;
- normalization bounds;
- failure policy;
- flush behavior.

Handler credentials or tokens SHALL use secret-safe configuration values and SHALL NOT participate in public fingerprints.

## 20. Container 2.0 integration

Container 2.0 MAY provide:

- logger contracts;
- named channel loggers;
- processors;
- handler factories;
- clock and environment abstractions;
- emergency reporter.

Compilation of the logging plan SHALL occur in the composition root. The logger SHALL NOT use uncontrolled service-location during emission.

## 21. Module Registry 2.0 integration

Modules MAY declaratively contribute:

- channel identifiers they own;
- processors;
- handler factories;
- configuration schema fragments;
- routing fragments restricted to owned channels.

Contributions SHALL be composed in resolved module order. Ownership collisions SHALL be rejected before application.

Modules SHALL NOT mutate a live logger, handler registry, or logging plan directly.

## 22. Compatibility strategy

Structured Logging 2.0 SHALL be additive.

Existing applications without a logging composition SHALL continue to boot unchanged.

Compatibility adapters MAY support established logger interfaces without making them the internal domain model. Adapter behavior SHALL preserve level semantics, context values, exception metadata, and failure policy as far as the external contract permits.

No static global logger is required. An optional facade MAY be considered only after the explicit composition API is complete and characterized.

## 23. Security and privacy

The subsystem SHALL assume that logs may leave the local process and may have broader access than application data.

Therefore it SHALL:

- redact secret-marked values;
- reject unsafe arbitrary serialization;
- bound payload size;
- avoid stack-local variable capture;
- avoid environment dumps;
- avoid configuration snapshot dumps;
- support channel-level suppression of sensitive attributes;
- preserve exception class and message only according to policy;
- make full stack traces opt-in and environment-aware.

## 24. Determinism and fingerprints

The compiled logging plan SHALL expose a deterministic fingerprint derived from safe structural inputs such as:

- channel identifiers;
- minimum levels;
- processor identifiers and order;
- handler identifiers and routing;
- failure-policy identifiers;
- normalization-policy identifiers.

Fingerprints SHALL exclude:

- secrets;
- absolute machine-specific paths unless explicitly normalized;
- current timestamps;
- object hashes;
- closures;
- handler credentials;
- mutable runtime state.

## 25. Diagnostics

The subsystem SHALL define typed diagnostics for at least:

- invalid level;
- invalid channel;
- invalid record attribute;
- normalization truncation;
- redaction action;
- processor failure;
- handler failure;
- emergency reporter failure;
- recursive logging prevention;
- flush failure;
- shutdown failure;
- invalid plan contribution;
- ownership collision.

Diagnostics SHALL be safe to render and SHALL not require the logger itself to be observable.

## 26. Lifecycle

The logging lifecycle SHALL distinguish:

1. definition;
2. plan validation;
3. publication;
4. normal emission;
5. flush;
6. shutdown.

Shutdown SHALL occur in deterministic reverse order where handler composition requires it.

A failed handler shutdown SHALL not prevent attempts to shut down remaining handlers. The first cause SHALL remain available through the aggregate result or diagnostic report.

## 27. Testing strategy

The work package SHALL include:

- unit tests for every immutable value object;
- level ordering tests;
- interpolation characterization;
- normalization-boundary tests;
- redaction tests;
- processor-order tests;
- handler-routing tests;
- recursive-failure tests;
- flush and shutdown ordering tests;
- deterministic fingerprint tests;
- Configuration, Container, Module Registry, Context, Runtime, and Observation integration tests;
- compatibility tests proving unchanged behavior when logging is absent.

Reference tests SHALL avoid dependence on wall-clock time through an injected clock.

## 28. Proposed delivery sequence

WP-213 is divided into eight increments:

1. **I1 — Architecture:** subsystem boundaries, failure policy, integrations, compatibility, and roadmap.
2. **I2 — Core value model:** levels, channels, messages, immutable records, clock boundary, and exceptions.
3. **I3 — Normalization and redaction:** bounded canonical normalization, throwable projection, secret safety, and diagnostics.
4. **I4 — Processor pipeline:** deterministic processors, enrichment contracts, immutable transformation, and failure policy.
5. **I5 — Handlers and routing:** handlers, filtering, fan-out, in-memory and stream references, flush, and shutdown.
6. **I6 — Immutable logging plan:** configuration schema, plan compiler, validation, fingerprints, and Container 2.0 bindings.
7. **I7 — Context, observation, audit, and module composition:** controlled adapters and declarative contributions.
8. **I8 — Runtime integration and product completion:** optional bootstrap integration, compatibility characterization, reference example, documentation, and closure review.

## 29. Completion criteria

WP-213 is complete when:

- the core model is immutable and fully tested;
- records are normalized and redacted deterministically;
- processor and handler order is deterministic;
- handler failures are contained without recursive collapse;
- immutable plans are validated before publication;
- safe fingerprints are reproducible;
- Configuration 2.0, Container 2.0, Module Registry 2.0, Context, Runtime, Observation, and Audit integrations are explicit and tested;
- applications without logging retain existing behavior;
- reference handlers and examples are documented;
- PHPUnit, PHPStan level 8, Builder validation, and repository quality gates pass.

## 30. Architectural decision

Structured Logging 2.0 SHALL be a Foundation subsystem composed explicitly at the application boundary.

It SHALL use immutable records, deterministic pipelines, handler-neutral contracts, secret-safe normalization, and isolated runtime emission failures.

It SHALL complement rather than replace events, diagnostics, audit, and exceptions.
