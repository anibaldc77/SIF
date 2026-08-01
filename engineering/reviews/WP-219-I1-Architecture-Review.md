---
id: WP-219-I1-ARCHITECTURE-REVIEW
title: WP-219 I1 Architecture Review
summary: Reviews the proposed PDO persistence adapter boundaries, SQL compilation model, platform capabilities, execution safety, repository composition and eight-increment delivery sequence.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-31
updated: 2026-07-31
tags:
  - persistence
  - database
  - pdo
  - sql
  - architecture
  - security
  - review
work_package: WP-219
depends_on:
  - EG-321
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-219 I1 Architecture Review

## Scope reviewed

- dependency direction between WP-209 and PDO infrastructure;
- relationship with WP-218 migration adapters;
- immutable SQL planning and compilation;
- identifier and value separation;
- criteria, sorting and pagination translation;
- PostgreSQL, MySQL and SQL Server platform profiles;
- prepared-statement execution and parameter binding;
- connection and transaction ownership;
- result-set and mapper boundaries;
- repository metadata and composite keys;
- bounded Unit of Work behavior;
- failure translation and secret redaction;
- runtime registration without eager connectivity;
- conformance testing and vendor support evidence;
- compatibility and eight-increment delivery roadmap.

## Architectural decision

WP-219 SHALL be implemented as an additive PDO infrastructure package over WP-209 persistence contracts.

WP-209 remains provider-neutral and authoritative for persistence abstractions. WP-219 owns SQL planning, dialect compilation, PDO execution, concrete connection/transaction adaptation and repository composition.

The required dependency direction is:

```text
WP-219 PDO adapters -> WP-209 persistence contracts
```

The reverse dependency is prohibited.

## Sequencing decision

BaseModel 2.0 SHALL not begin before WP-219 provides real SQL-backed repository and transaction adapters.

The current repository contains only the WP-209 in-memory reference adapter. Beginning BaseModel first would either couple it directly to PDO or force it to target an incomplete persistence path. WP-219 therefore precedes BaseModel 2.0.

## SQL safety review

The architecture correctly separates identifiers from values.

- values use prepared-statement parameters;
- identifiers use validated platform-specific quoting;
- unrestricted SQL fragments are excluded from the ordinary query model;
- compilation completes before any PDO operation;
- diagnostics exclude parameter values and credentials by default.

This is a mandatory security boundary.

## Platform review

The design correctly rejects the assumption that PDO normalizes SQL dialects.

PostgreSQL, MySQL and SQL Server receive explicit compiler and capability profiles. Pagination, generated-key retrieval, quoting and transaction behavior remain platform decisions.

Modern SQL Server support is intentionally separated from SQL Server 2000 compatibility. A future legacy adapter must declare its own compiler and evidence.

## Persistence review

Repository mapping is explicit and supports composite keys as structured values.

WP-219 does not require entity inheritance and therefore preserves the WP-209 mapper boundary. This allows BaseModel 2.0 to be introduced later as one consumer rather than as a dependency of persistence infrastructure.

## Transaction review

Connection ownership is explicit and caller-owned PDO instances cannot be committed, rolled back or closed without contract authority.

The proposed bounded Unit of Work avoids hidden dirty tracking and shutdown flushing. Cross-connection atomicity and distributed transactions remain out of scope.

## Runtime review

Optional runtime publication is additive and lazy.

Service registration and boot are prohibited from opening connections or running queries. This preserves deterministic boot and keeps infrastructure failure tied to explicit persistence use.

## Testing review

The architecture appropriately separates generic PDO mechanics from vendor proof.

SQLite may test generic binding and result behavior but cannot establish PostgreSQL, MySQL or SQL Server conformance. Vendor support requires conditional integration suites against declared versions.

## Compatibility review

WP-219 is additive. The in-memory reference adapter remains valid and applications without PDO registration observe no behavior change.

No incompatible change to WP-209 public contracts is authorized by I1.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---:|---|
| Values are interpolated into SQL | Critical | Immutable compiled statements and mandatory bound parameters. |
| User-controlled identifiers bypass validation | Critical | Dedicated identifier values and platform quoting. |
| PDO is treated as dialect portability | Critical | Explicit PostgreSQL, MySQL and SQL Server compilers. |
| BaseModel couples directly to PDO | Critical | Complete WP-219 before BaseModel 2.0. |
| Caller-owned transaction is committed accidentally | Critical | Explicit ownership and external transaction policy. |
| SQL Server pagination is emitted without ordering | High | Compiler-level capability and validation rule. |
| Composite keys are flattened unsafely | High | Structured key metadata and bindings. |
| Driver errors expose credentials or values | High | Typed translation and redacted diagnostics. |
| SQLite tests are presented as vendor support | High | Separate integration evidence per vendor. |
| Runtime boot opens a database connection | High | Lazy composition and no-I/O boot tests. |
| Repository metadata is inferred unpredictably | High | Explicit immutable mapping metadata. |
| Automatic retries repeat mutations | Critical | No implicit retry of non-idempotent operations. |

## Increment decision

I2 may begin after approval of EG-321.

I2 is limited to immutable connection, ownership, platform capability, identifier, parameter and compiled-statement values plus validation tests. It SHALL perform no database I/O and SHALL not introduce repositories or SQL compilers.
