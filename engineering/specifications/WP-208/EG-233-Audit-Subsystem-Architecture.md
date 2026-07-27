---
id: EG-233
title: Audit Subsystem Architecture
summary: Defines an event-driven, context-aware, storage-neutral Audit subsystem with canonical JSON records, typed audit levels, customizable models, and explicit policy boundaries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-208
tags:
  - foundation
  - audit
  - context
  - events
  - json
  - architecture
depends_on:
  - EG-232
  - EG-217
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-233 — Audit Subsystem Architecture

## 1. Purpose

This specification defines the architecture of the SIF Audit subsystem.

Audit SHALL provide an immutable, event-driven, context-aware representation of relevant application activity without depending on a database, transport, framework adapter, HTTP runtime, CLI runtime, ORM, or persistence engine.

The subsystem SHALL be suitable for later integration with application models, domain services, Runtime operations, security adapters, persistence adapters, reporting tools, and external observability systems while preserving strict Core independence.

## 2. Architectural position

Audit is an infrastructure-neutral Foundation subsystem.

Its dependency direction SHALL be:

```text
Application or module
        |
        v
Audit contracts and immutable records
        |
        v
Audit event
        |
        v
Event Dispatcher
        |
        v
Optional adapters
```

Audit MAY depend on stable Foundation contracts such as Execution Context and Event Dispatcher abstractions.

Audit MUST NOT depend on:

- SQL or NoSQL databases;
- PDO, ORM, repositories, migrations, or schemas;
- HTTP requests or sessions;
- CLI input;
- authentication implementations;
- logging frameworks;
- queues or message brokers;
- application-specific models;
- global state.

## 3. Core design principles

### 3.1 Storage neutrality

The Audit Core SHALL NOT know whether an audit record is eventually:

- persisted in SQL Server, PostgreSQL, MySQL, SQLite, or NoSQL;
- written to a file;
- sent to a queue;
- forwarded to an external service;
- retained only in memory;
- discarded by a null adapter.

Persistence SHALL be implemented by optional listeners or adapters outside the Core.

### 3.2 Event-driven emission

Audit records SHALL be emitted through explicit audit events.

The Core SHALL NOT call a persistence adapter directly.

A producer SHALL create an immutable audit record and dispatch an event containing that record. Listeners MAY persist, forward, aggregate, redact, transform for transport, or observe the event according to their own contracts.

Listener failures SHALL follow the error policy of the integration layer and SHALL NOT silently mutate the original audit record.

### 3.3 Mandatory Execution Context

Every audit record SHALL include an `ExecutionContextInterface`.

The context provides:

- context identity;
- correlation identity;
- causation identity when available;
- parent context identity when available;
- actor and tenant when available;
- operation and source;
- creation timestamp;
- validated attributes.

Audit SHALL not create ambient context and SHALL not provide `Audit::currentContext()` or any equivalent global accessor.

### 3.4 Canonical JSON representation

The canonical transport and persistence-neutral representation of an audit record SHALL be JSON-compatible data.

The canonical document SHALL:

- use deterministic field names;
- use stable scalar and structured values;
- preserve `null` for absent optional values where required by the serializer contract;
- embed a safe context snapshot produced through the Context serializer;
- avoid PHP object serialization;
- avoid stack traces and host paths by default;
- support deterministic encoding and hashing by future adapters.

JSON is the principal representation, but the Core SHALL expose immutable objects and arrays rather than forcing direct file or database output.

### 3.5 Customizable models

Applications and modules SHALL be able to customize audit payload construction without replacing Core semantics.

Customization SHALL occur through contracts such as:

- audit subject descriptors;
- change-set or diff providers;
- audit metadata providers;
- record factories;
- serializers;
- policies.

The Core SHALL NOT use reflection to inspect arbitrary models automatically.

Application models SHALL opt in explicitly through interfaces, adapters, or model-specific listeners.

### 3.6 Typed audit levels

Audit severity or importance SHALL be represented by a typed value, not arbitrary strings.

The initial semantic levels SHALL be capable of representing at least:

- diagnostic;
- informational;
- notice;
- warning;
- critical.

The exact PHP representation and names SHALL be defined in a later increment.

Audit level SHALL describe the significance of the audited action. It SHALL NOT be confused with log verbosity, authorization level, or retention policy.

## 4. Conceptual model

The Audit subsystem SHALL distinguish these concepts.

### 4.1 Audit record

An immutable statement that a relevant action or state transition occurred.

A record SHALL contain at minimum:

- audit record identifier;
- event or action name;
- audit level;
- occurred-at timestamp;
- execution context;
- subject type;
- optional subject identifier;
- canonical payload;
- optional before state;
- optional after state;
- optional change set;
- optional tags;
- schema version.

### 4.2 Audit subject

The entity, aggregate, resource, operation, or conceptual object affected by the action.

A subject SHALL be described explicitly. The Audit Core SHALL not infer identity from arbitrary model internals.

### 4.3 Audit action

A stable semantic identifier such as:

```text
user.created
case.updated
document.signed
runtime.started
permission.denied
```

Action names SHALL be application-readable and stable enough for filtering and reporting.

### 4.4 Audit payload

Application-defined JSON-compatible information associated with the action.

Payload values SHALL pass the same safety principles used by Context attributes:

- no resources;
- no closures;
- no arbitrary objects;
- no recursive arrays;
- no non-finite floating-point values.

### 4.5 State snapshots and change sets

Before and after snapshots MAY be attached when meaningful.

A change set MAY describe field-level differences. The Core SHALL not force every record to contain a diff.

Diff calculation SHALL be delegated to explicit providers or factories. Audit SHALL not introspect database rows or ORM state automatically.

## 5. Proposed public boundaries

Later increments SHOULD define contracts conceptually equivalent to:

- `AuditRecordInterface`;
- `AuditRecordFactoryInterface`;
- `AuditIdGeneratorInterface`;
- `AuditSerializerInterface`;
- `AuditPolicyInterface`;
- `AuditableSubjectInterface`;
- `AuditMetadataProviderInterface`;
- `AuditChangeSetProviderInterface`;
- `AuditEventInterface`.

The final names and signatures SHALL be approved incrementally.

## 6. Static facade policy

A static `Audit` facade MAY be introduced only as an optional convenience entry point after the instance-based contracts are complete.

If introduced, it SHALL:

- delegate to explicitly configured contracts;
- contain no database knowledge;
- contain no hidden mutable global context;
- not become the only supported API;
- fail predictably when no dispatcher or factory is configured;
- preserve testability through replaceable contracts.

The architectural source of truth SHALL remain the instance-based model.

## 7. Event model

The initial event model SHOULD include one immutable event representing a produced audit record.

Future events MAY represent:

- record accepted;
- record rejected by policy;
- adapter persistence succeeded;
- adapter persistence failed.

Adapter lifecycle events SHALL remain outside the Audit Core unless a later specification demonstrates a stable cross-adapter need.

## 8. Policy boundaries

Audit policy SHALL be explicit and composable.

Policies MAY decide:

- whether an action is auditable;
- which audit level applies;
- which payload fields are allowed;
- whether before/after snapshots are permitted;
- which fields require redaction;
- maximum payload depth or size;
- which tags are attached.

Policies SHALL NOT perform persistence.

## 9. Confidentiality and data minimization

Audit data can contain sensitive institutional or personal information. Therefore:

1. Producers SHALL include only information necessary for the audit purpose.
2. Secrets, credentials, tokens, private keys, and raw authentication material SHALL NOT be audited.
3. Redaction SHALL be explicit and deterministic.
4. Context snapshots SHALL use an approved redaction policy.
5. Stack traces, local paths, and raw exception dumps SHALL be excluded from canonical records by default.
6. Persistence adapters SHALL define retention, encryption, access control, and deletion policies outside the Core.
7. Canonical JSON compatibility does not imply that every payload is safe to persist.

## 10. Immutability

Audit records, identifiers, levels, subjects, payload values, and events SHALL be immutable after construction.

Derived or redacted representations SHALL create new values rather than mutate existing records.

## 11. Error policy

The Core SHALL use typed exceptions for invalid construction, unsupported values, invalid identifiers, invalid action names, and policy violations.

A producer error SHALL be distinguishable from a listener or persistence-adapter failure.

No adapter failure SHALL rewrite the original record or its Execution Context.

## 12. Compatibility and extensibility

The subsystem SHALL be additive and compatible with PHP 8.2.

The Core SHALL remain usable without:

- any database extension;
- any web server;
- any CLI integration;
- any application model base class;
- any external package.

Extension SHALL occur through interfaces, listeners, and service providers.

## 13. Explicit exclusions

WP-208-I1 does not define or implement:

- database tables;
- migrations;
- repositories;
- retention schedules;
- cryptographic signing;
- hash chains;
- immutable external ledgers;
- async queues;
- event sourcing;
- automatic ORM hooks;
- automatic BaseModel integration;
- user authentication;
- authorization;
- dashboards or reports;
- static facade implementation.

## 14. Increment plan

WP-208 SHOULD proceed in small increments:

1. **WP-208-I1** — architecture;
2. **WP-208-I2** — identifiers, levels, action and subject value model;
3. **WP-208-I3** — immutable audit record and payload validation;
4. **WP-208-I4** — factory, canonical serialization and redaction;
5. **WP-208-I5** — event-driven emission contracts and dispatcher integration;
6. **WP-208-I6** — customizable model and change-set contracts;
7. **WP-208-I7** — optional static facade and explicit composition;
8. **WP-208-I8** — reference integrations and product completion.

Each increment SHALL pass PHPUnit, PHPStan level 8, Builder validation, deterministic governed generation, and `git diff --check`.

## 15. Acceptance criteria

WP-208-I1 is accepted when:

- this specification passes repository metadata validation;
- the architecture preserves storage neutrality;
- mandatory Execution Context association is explicit;
- JSON is defined as canonical representation;
- typed levels and customizable model boundaries are defined;
- event-driven emission is mandatory;
- exclusions prevent premature persistence coupling;
- governed artifact generation is deterministic.
