---
id: EG-231
title: Context Integration Contracts
summary: Defines explicit context-aware boundaries and immutable envelopes for future event, audit, command, and transport integrations.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-207
tags:
  - foundation
  - context
  - integration
  - envelope
  - contracts
depends_on:
  - EG-230
  - EG-229
  - EG-228
  - EG-227
  - EG-226
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-231 — Context Integration Contracts

## 1. Purpose

This specification defines minimal, explicit integration boundaries that associate an execution context with an object payload without introducing automatic Runtime, Event Dispatcher, Observation, Audit, persistence, or transport coupling.

## 2. Public contracts and types

The increment SHALL provide:

- `ContextAwareInterface`;
- `ContextEnvelopeInterface`;
- `ContextEnvelope`;
- `ContextEnvelopeFactory`.

## 3. Context-aware boundary

`ContextAwareInterface` SHALL expose an `ExecutionContextInterface` through an explicit `context()` method. Implementing the contract SHALL NOT imply ambient context registration, lifecycle ownership, persistence, serialization, or automatic propagation.

## 4. Context envelope

`ContextEnvelopeInterface` SHALL associate an object payload with an execution context. `ContextEnvelope` SHALL preserve the exact payload and context instances supplied to it.

The envelope SHALL be immutable. Replacing the payload or context SHALL return a new envelope, except when the same instance is supplied, in which case the existing envelope MAY be returned.

The envelope SHALL NOT clone, serialize, normalize, inspect, execute, mutate, or otherwise reinterpret its payload.

## 5. Explicit composition

`ContextEnvelopeFactory::fromCarrier()` SHALL create an envelope from an explicit `ContextCarrierInterface`. It SHALL preserve exact payload and context identity and SHALL NOT create or derive a context implicitly.

Scopes can participate because `ExecutionContextScopeInterface` extends `ContextCarrierInterface`; no scope-specific global integration is required.

## 6. Future integration use

The contracts MAY be used by future event, command, audit, queue, HTTP, CLI, or persistence adapters. Such adapters SHALL remain separate work packages and SHALL define their own error, serialization, redaction, and transport policies.

The generic envelope SHALL NOT replace typed domain or Runtime events automatically. Dispatching an envelope is an explicit application decision.

## 7. Exclusions

This increment SHALL NOT include:

- changes to Runtime, Bootstrap, Kernel, Lifecycle, Event Dispatcher, Observation, or Audit;
- automatic wrapping or dispatching;
- ambient context access or process-global state;
- payload serialization, redaction, cloning, validation, or persistence;
- HTTP headers, CLI variables, queue metadata, database records, or authentication adapters;
- reflection, attributes, or automatic discovery.

## 8. Acceptance criteria

The increment is accepted when contract implementation, exact identity preservation, immutable payload/context replacement, explicit carrier composition, payload non-interference, PHPStan level 8, Builder validation, deterministic governed artifact generation, and the complete repository quality gate pass.
