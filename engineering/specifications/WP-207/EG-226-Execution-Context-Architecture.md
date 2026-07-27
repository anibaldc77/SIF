---
id: EG-226
title: Execution Context Architecture
summary: Defines the immutable, explicit, serializable, and infrastructure-neutral execution context required by runtime diagnostics, events, audit, and future request adapters.
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
  - runtime
  - audit
  - events
  - security
depends_on:
  - EG-225
  - EG-218
  - EG-214-A1
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-226 — Execution Context Architecture

## 1. Purpose

This specification defines the architecture of the SIF Execution Context subsystem.

Execution Context provides an explicit, immutable carrier for execution identity and contextual attributes used by runtime observation, diagnostics, events, audit, console, HTTP, jobs, and future adapters.

WP-207 SHALL remain infrastructure-neutral. It SHALL NOT depend on a database, HTTP implementation, session subsystem, authentication provider, logger, queue, or framework module.

## 2. Problem statement

Cross-cutting subsystems require common contextual information such as:

- correlation and causation identifiers;
- actor or subject identifiers;
- tenant, organization, or application scope;
- source channel and operation name;
- locale and timezone hints;
- remote-address or transport metadata supplied by adapters;
- arbitrary domain-safe attributes.

Without a governed context contract, each subsystem invents its own representation, creates hidden global state, leaks transport-specific objects, and makes audit records difficult to reproduce or serialize safely.

## 3. Architectural goals

The subsystem SHALL provide:

1. immutable context values;
2. explicit propagation through contracts and method parameters;
3. deterministic serialization;
4. typed standard fields with extensible attributes;
5. safe redaction boundaries;
6. parent/child derivation without mutation;
7. infrastructure and storage neutrality;
8. compatibility with PHP 8.2 and PHPStan level 8;
9. suitability for events, diagnostics, and audit;
10. no behavioral change when Context is not composed.

## 4. Non-goals

WP-207 SHALL NOT provide:

- authentication or authorization;
- user or tenant lookup;
- session storage;
- request-local global containers;
- database persistence;
- audit storage;
- distributed tracing export;
- automatic HTTP or CLI discovery;
- mutable ambient context;
- secrets management;
- arbitrary object serialization.

## 5. Core model

The initial model SHALL be composed of:

- `ExecutionContextInterface` — read-only public contract;
- `ExecutionContext` — immutable implementation;
- `ContextId` — non-empty execution identifier value object;
- `ContextAttributes` — immutable scalar/array attribute collection;
- `ContextFactoryInterface` — explicit context creation boundary;
- `ContextSerializerInterface` — deterministic safe serialization boundary;
- typed context exceptions for invalid identifiers and unsupported attribute values.

The implementation MAY be divided into smaller increments, but the public model SHALL preserve these responsibilities.

## 6. Standard fields

Execution Context SHALL support the following optional or required fields:

| Field | Requirement | Meaning |
|---|---|---|
| `context_id` | required | Unique identifier of the current execution context. |
| `correlation_id` | required | Identifier shared by related operations. Defaults to the context identifier when no external value exists. |
| `causation_id` | optional | Identifier of the operation or context that caused this execution. |
| `parent_context_id` | optional | Identifier of the parent context when a child is derived. |
| `actor_id` | optional | Stable actor identifier supplied by an adapter or application. |
| `tenant_id` | optional | Stable tenant or organizational scope identifier. |
| `operation` | optional | Stable operation name such as `runtime.run` or `audit.write`. |
| `source` | optional | Origin such as `cli`, `http`, `job`, `test`, or application-defined source. |
| `locale` | optional | Locale hint without loading locale infrastructure. |
| `timezone` | optional | Timezone identifier hint. |
| `created_at` | required | Immutable creation timestamp represented by `DateTimeImmutable`. |
| `attributes` | required | Immutable extension attributes, empty by default. |

Identifiers SHALL be opaque strings. The Context subsystem SHALL NOT infer identity, permissions, ownership, or trust from identifier content.

## 7. Attribute value policy

Context attributes SHALL accept only deterministic, serializable values:

- `null`;
- `bool`;
- `int`;
- `float`;
- `string`;
- nested lists or associative arrays containing only supported values.

Objects, resources, closures, and cyclic structures SHALL be rejected.

Attribute keys SHALL be non-empty strings. Implementations SHALL preserve insertion-independent deterministic serialization by sorting associative keys recursively during serialization.

## 8. Immutability and derivation

Execution Context SHALL be immutable after construction.

Operations such as adding an attribute, changing an operation name, or creating a child context SHALL return a new instance.

Child derivation SHALL:

1. create a new `context_id`;
2. preserve the existing `correlation_id`;
3. set `parent_context_id` to the parent context identifier;
4. set `causation_id` explicitly when supplied;
5. copy standard fields and attributes unless explicitly overridden;
6. never mutate the parent.

## 9. Propagation model

Context propagation SHALL be explicit.

Approved mechanisms include:

- constructor injection;
- method parameters;
- immutable event properties;
- explicit adapter composition;
- child derivation at operation boundaries.

The initial implementation SHALL NOT use static mutable state, thread-local emulation, superglobals, service locators, or hidden singleton access.

A future convenience scope MAY be proposed only through a separate specification and SHALL preserve an explicit underlying contract.

## 10. Serialization and redaction

The canonical serializer SHALL produce a deterministic associative array suitable for JSON encoding.

The serialized representation SHALL:

- use stable snake-case field names;
- emit timestamps in an explicit ISO-8601 format;
- sort associative attribute keys recursively;
- preserve list order;
- omit no required fields;
- represent absent optional fields consistently;
- avoid stack traces, host paths, resources, and arbitrary object dumps.

Redaction SHALL be explicit and policy-driven. The Context model SHALL NOT silently guess whether a value is sensitive.

The initial architecture SHALL support a configurable deny-list of attribute keys for serialization. Redacted keys SHALL be represented by a stable marker rather than their original values.

## 11. Security and confidentiality

Execution Context MAY contain confidential metadata. Therefore:

- secrets, passwords, access tokens, private keys, raw credentials, and complete authorization headers SHALL NOT be stored;
- adapters SHALL normalize external data before adding it;
- remote addresses and user-agent values SHALL be treated as untrusted strings;
- serialization SHALL not expose internal object structure;
- diagnostics and audit integrations SHALL consume only the safe serialized representation unless they explicitly require the typed object;
- Context values SHALL not establish authorization decisions by themselves.

## 12. Event and observation integration boundary

WP-207 SHALL initially remain independent of automatic runtime wiring.

Future integrations MAY attach an `ExecutionContextInterface` to new events or audit records, but SHALL NOT retroactively change existing event constructors without a compatibility plan.

Observation diagnostics MAY reference `context_id` and `correlation_id` through additive adapters. Listener failures SHALL remain isolated according to WP-205 guarantees.

## 13. Audit integration boundary

Execution Context is a prerequisite for the future Audit subsystem.

Audit SHALL consume Context through contracts and events. Context SHALL NOT know about audit models, database tables, persistence engines, or audit levels.

The audit subsystem MAY serialize Context as JSON, but the canonical Context serializer SHALL remain independent of audit storage.

## 14. Error model

The subsystem SHALL define typed exceptions for at least:

- empty or invalid context identifiers;
- invalid attribute keys;
- unsupported attribute values;
- invalid timestamps or timezone hints when validation is requested;
- serialization policy violations.

Validation errors SHALL fail fast during construction or derivation. Serialization SHALL not silently coerce unsupported objects or resources.

## 15. Increment plan

WP-207 is divided into:

- **WP-207-I1** — architecture and boundaries;
- **WP-207-I2** — identifiers, attributes, contracts, and immutable core;
- **WP-207-I3** — factory, child derivation, and deterministic serialization;
- **WP-207-I4** — redaction policy and diagnostics integration;
- **WP-207-I5** — event and audit reference integrations;
- **WP-207-I6** — documentation, examples, and product completion.

Each implementation increment SHALL pass PHPUnit, PHPStan level 8, Builder validation, and deterministic artifact generation.

## 16. Compatibility guarantees

The initial WP-207 implementation SHALL be additive.

It SHALL NOT modify default behavior in:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- `RuntimeStateMachine`;
- capability registration;
- event dispatch or observation composition.

Existing consumers SHALL remain unaffected when Execution Context is not explicitly used.

## 17. Acceptance criteria for I1

WP-207-I1 is accepted when:

1. this specification passes repository metadata validation;
2. the architecture review confirms additive scope and dependency direction;
3. generated repository artifacts are deterministic;
4. no production PHP file changes are introduced;
5. the baseline test and static-analysis results remain unchanged.

## 18. Decision

SIF SHALL implement Execution Context as an immutable, explicit, infrastructure-neutral subsystem before implementing the Audit subsystem.

Context SHALL carry execution metadata; it SHALL NOT become a mutable global environment, security authority, persistence model, or transport abstraction.
