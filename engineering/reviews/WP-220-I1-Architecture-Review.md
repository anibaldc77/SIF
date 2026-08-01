---
id: WP-220-I1-ARCHITECTURE-REVIEW
title: WP-220 I1 Architecture Review
summary: Reviews the BaseModel 2.0 boundaries, metadata and lifecycle model, persistence delegation, audit and event integration, compatibility strategy and eight-increment roadmap.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - basemodel
  - persistence
  - audit
  - events
  - compatibility
  - architecture
  - review
depends_on:
  - EG-329
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-220 I1 Architecture Review

## Scope reviewed

- dependency direction between BaseModel 2.0 and Persistence;
- relationship with WP-219 PDO Persistence;
- immutable model metadata;
- attribute storage and mass-assignment policy;
- hydration, serialization and dirty tracking;
- simple and composite identity;
- CRUD delegation through repositories;
- query API and soft-delete behavior;
- lifecycle hooks and event ordering;
- execution-context and audit integration;
- relationship loading policy;
- Unit of Work participation;
- runtime composition and side-effect rules;
- compatibility with legacy BaseModel applications;
- eight-increment delivery sequence.

## Architectural decision

WP-220 SHALL introduce BaseModel 2.0 as an application-facing layer over the provider-neutral Persistence subsystem.

BaseModel SHALL NOT become a dependency of Persistence, PDO Persistence, Audit, Event or Context. It SHALL consume those contracts through explicit runtime composition.

The required direction is:

```text
BaseModel 2.0 -> Persistence contracts -> concrete adapter
```

Direct PDO access or SQL generation inside model classes is prohibited.

## Readiness decision

WP-220 is now correctly sequenced.

The project already contains:

- provider-neutral persistence contracts and values;
- in-memory repository and Unit of Work references;
- PDO query translation, platform compilers and prepared execution;
- transaction management;
- PDO repositories, mappers and Unit of Work composition;
- Event Dispatcher;
- Execution Context;
- Audit contracts and implementation;
- runtime and Service Provider infrastructure.

BaseModel 2.0 can therefore target stable boundaries instead of inventing temporary storage behavior.

## Metadata review

The proposed explicit metadata model is approved.

Model persistence SHALL be driven by declared fields, keys, casts, visibility and policies. Unrestricted reflection and dynamic properties are rejected because they make persistence behavior implicit and conflict with PHP 8.2 requirements.

Normalized metadata may be cached, provided construction is deterministic and performs no I/O.

## State model review

The separation among current attributes, original attributes, existence state and dirty state is approved.

Hydration must establish an original snapshot without invoking save hooks. Successful persistence synchronizes that snapshot; failed persistence preserves dirty values for diagnosis or retry.

This boundary is required for reliable audit change sets.

## Persistence review

CRUD delegation through repositories is approved.

The model must not infer insert versus update solely from a null key. Explicit existence state and repository results are authoritative, particularly for assigned and composite keys.

The architecture preserves direct repository use for applications that do not need BaseModel.

## Query and soft-delete review

A model query convenience API is acceptable only as a translation layer over the provider-neutral Query model.

Arbitrary SQL fragments are excluded.

Soft delete is approved as explicit metadata and query scope behavior. It must not silently affect models that have not enabled the policy.

## Lifecycle and events review

Deterministic lifecycle hooks are approved.

Pre-operation hooks may veto an operation. Completed events must not be emitted before persistence and the governing transaction have succeeded.

Events should carry model identity and safe changes rather than serializing full model objects.

## Context and audit review

The integration direction is correct.

BaseModel may adapt model state to the existing audit contracts, but Audit remains independent of BaseModel. Execution context must be obtained through contracts, never through HTTP globals or session state.

Redaction remains mandatory for sensitive attributes.

## Relationship review

The initial scope of belongs-to, has-one and has-many is accepted.

Hidden I/O is prohibited by default. Relationship loading must be explicit, and batch/eager loading should be preferred over repeated per-model queries.

Cascades require explicit metadata and are not assumed by I1.

## Unit of Work review

Explicit registration and flush are approved.

Automatic flush during object destruction or application shutdown is prohibited. The existing Persistence Unit of Work remains authoritative for transaction coordination.

## Compatibility review

An additive compatibility path is required because SIF applications already use BaseModel conventions.

The review approves familiar method names and declarations when they delegate to the new runtime. It rejects compatibility that restores dynamic properties, global hidden connections, undeclared fields or SQL concatenation.

A migration guide is a product-completion requirement, not optional documentation.

## Runtime review

Optional runtime publication is approved.

Registering or booting BaseModel services must not open connections, query schemas, run migrations or persist models. Database I/O begins only with an explicit model or Unit of Work operation.

## Risks and mitigations

### Risk: active-record coupling leaks into Persistence

Mitigation: maintain one-way dependency and repository delegation.

### Risk: magic methods hide behavior

Mitigation: keep metadata explicit, constrain magic access and prohibit hidden I/O.

### Risk: legacy compatibility weakens new invariants

Mitigation: isolate compatibility adapters and emit deprecation diagnostics.

### Risk: event and audit ordering becomes inconsistent

Mitigation: define lifecycle state transitions and transaction-relative ordering in I6.

### Risk: relationships cause N+1 query behavior

Mitigation: explicit loading and governed eager-loading support in I7.

### Risk: composite keys are flattened incorrectly

Mitigation: retain structured keys throughout metadata, repositories and identity handling.

## Review conclusion

The architecture is approved for implementation.

The next increment is:

```text
WP-220 I2 — Immutable Metadata and Attribute Model
```

No BaseModel runtime, CRUD behavior or compatibility adapter is introduced by I1.
