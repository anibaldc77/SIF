---
id: WP-213-I1-REVIEW
title: WP-213-I1 Structured Logging 2.0 Architecture Review
summary: Reviews the proposed deterministic, context-aware, secret-safe, handler-neutral, failure-contained, and compatibility-first architecture for Structured Logging 2.0.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-213
tags:
  - foundation
  - logging
  - observability
  - architecture
  - compatibility
  - review
depends_on:
  - EG-273
  - EG-213
  - EG-218
  - EG-226
  - EG-233
  - EG-249
  - EG-257
  - EG-265
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-213-I1 — Structured Logging 2.0 Architecture Review

## Scope

WP-213-I1 defines the architecture, responsibility boundaries, safety requirements, compatibility strategy, integration points, delivery sequence, and completion criteria for Structured Logging 2.0.

It adds no production PHP code and does not alter Runtime, Event Dispatcher, Event Observation, Execution Context, Audit, Persistence, Container, Configuration, Module Registry, or existing application behavior.

## Need review

The selected work package addresses a real Foundation gap.

SIF already exposes typed events, diagnostics, boot results, context, audit records, persistence failures, configuration diagnostics, and module diagnostics. These outputs currently lack a single governed operational logging boundary.

Structured Logging 2.0 is therefore a coherent next step after Module Registry 2.0 because it can consume existing observability information without duplicating subsystem ownership.

## Boundary review

The specification correctly separates:

- operational logging;
- legal and accountability-oriented audit;
- domain and runtime events;
- typed diagnostics;
- exception semantics.

This is essential. Treating logs as audit records would create false retention and integrity guarantees. Treating events as logs would couple extension behavior to operational output. Treating exceptions as log records would weaken failure semantics.

The proposed architecture permits adapters while preserving each authoritative model.

## Core model review

Immutable levels, channels, messages, attributes, timestamps, throwable projections, and records are appropriate because the same record may pass through multiple processors and handlers.

The standard eight-level severity model provides interoperability without introducing arbitrary custom levels. Semantic distinctions remain available through channels and structured attributes.

The requirement for an injected clock prevents nondeterministic tests and avoids hidden time dependencies.

## Normalization review

Bounded normalization is mandatory for safety and availability.

Without explicit limits, logging may accidentally traverse deep object graphs, expose secrets, retain excessive memory, or recursively invoke application behavior. The proposed limits for depth, collection size, strings, throwable chains, objects, and binary data are proportionate.

Integration with Configuration secret markers and Context redaction creates a consistent security model across Foundation.

## Processor review

Processors are correctly modeled as deterministic transformations from one immutable record to another.

Explicit ordering avoids hidden enrichment precedence. Restricting uncontrolled service resolution during emission prevents the logger from becoming a service locator and reduces recursion risk.

Processor failure policy must remain explicit because enrichment failure should not silently corrupt a record or replace the application failure being observed.

## Handler review

Handler-neutral contracts preserve extensibility and avoid a mandatory external dependency.

The proposed in-memory, stream, null, and fan-out reference handlers provide sufficient product validation without committing SIF to remote vendors.

Acceptance filtering, flushing, shutdown, and stable handler identity are necessary for deterministic routing, diagnostics, and plan fingerprints.

## Failure-containment review

Logging can fail while reporting another failure. The architecture correctly treats this as a first-class condition.

The following controls are approved:

- strict validation during bootstrap;
- isolated handler failures during emission;
- bounded recursive-entry prevention;
- a reduced emergency path;
- no Container or module resolution in the emergency path;
- preservation of the original application failure;
- continued shutdown attempts after individual handler failures.

The emergency reporter must remain minimal and secret-safe.

## Logging-plan review

Compiling configuration and contributions into an immutable `LoggingPlan` before publication is consistent with Container 2.0, Configuration 2.0, and Module Registry 2.0.

This permits ownership validation, routing validation, cycle detection, stable ordering, and fingerprints before live runtime mutation.

The plan must not contain secrets in its public diagnostics or fingerprint material.

## Integration review

### Runtime

Optional contract-based integration preserves existing boot behavior. Runtime events and boot results remain authoritative.

### Event Observation

A logging reporter adapter is appropriate provided dispatcher ordering and fail-fast semantics remain unchanged.

### Execution Context

Safe projection of correlation and operation metadata improves observability. Logging without an active context must remain valid.

### Audit

Only operational audit-pipeline information may be logged by default. Confidential audit payloads must not flow into ordinary logs.

### Configuration 2.0

Schemas, secret markers, and immutable snapshots are the correct configuration source.

### Container 2.0

The Container may provide contracts and factories, but runtime emission must avoid uncontrolled service location.

### Module Registry 2.0

Declarative, owner-validated contributions composed in resolved module order are consistent with WP-212. Modules must not mutate a live logger.

## Compatibility review

The additive strategy is approved.

Applications that do not configure logging must continue unchanged. Optional compatibility adapters may support established logger contracts without forcing the external contract to become SIF's internal model.

A static facade is deliberately deferred until explicit composition is implemented and characterized.

## Security review

The specification correctly assumes that logs may have broader visibility than application data.

The following requirements are approved:

- secret-marker redaction;
- bounded payloads;
- no arbitrary object serialization;
- no environment or configuration dumps;
- no implicit stack-local capture;
- policy-controlled exception messages and traces;
- channel-level suppression of sensitive fields.

## Determinism review

Deterministic processor order, handler order, routing, shutdown, serialization, and fingerprints are required for reproducible operation and diagnostics.

Excluding timestamps, secrets, closures, object hashes, credentials, and mutable state from fingerprints is correct.

## Delivery review

The eight-increment sequence is coherent:

1. architecture;
2. immutable core model;
3. normalization and redaction;
4. processor pipeline;
5. handlers and routing;
6. immutable plan and configuration;
7. subsystem and module adapters;
8. runtime integration and product completion.

Each increment has a bounded responsibility and testable completion point.

## Risks

Primary risks are:

- accidental coupling between logging and audit;
- recursive logging during handler failure;
- secret disclosure through context or exception data;
- hidden nondeterminism from clocks, environment data, or unordered maps;
- handler-driven runtime failure;
- premature dependence on a third-party logging implementation;
- uncontrolled service location during emission.

EG-273 addresses each risk with explicit boundaries and policies.

## Review decision

WP-213-I1 is architecturally approved for implementation subject to repository validation.

The next increment SHALL be:

> **WP-213-I2 — Core value model, immutable records, levels, channels, message templates, clock boundary, and failure taxonomy.**

No Runtime integration, concrete stream output, module contribution, or static facade shall be introduced in I2.
