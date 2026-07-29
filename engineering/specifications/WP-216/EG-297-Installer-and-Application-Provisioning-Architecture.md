---
id: EG-297
title: Installer and Application Provisioning Architecture
summary: Defines the governed architecture, safety boundaries, deterministic planning model and incremental roadmap for the SIF Installer and Application Provisioning Foundation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-216
tags:
  - foundation
  - installer
  - provisioning
  - architecture
  - security
depends_on:
  - EG-279
  - EG-289
  - EG-296
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-297 — Installer and Application Provisioning Architecture

## 1. Purpose

WP-216 establishes the Installer and Application Provisioning Foundation for SIF.

The subsystem SHALL transform an explicit installation request into a validated, deterministic and auditable installation plan. Execution SHALL remain separated from planning and SHALL occur only through explicitly authorized adapters.

The Installer SHALL support application provisioning without coupling the Foundation to a web controller, interactive wizard, CLI framework, database vendor, operating system shell, package manager or deployment platform.

## 2. Architectural objectives

The Installer SHALL provide:

1. immutable installation requests and plans;
2. deterministic requirement evaluation;
3. explicit filesystem and configuration mutations;
4. ordered installation steps with declared dependencies;
5. dry-run support;
6. execution reports and typed diagnostics;
7. rollback-aware execution boundaries;
8. extension points for modules and infrastructure adapters;
9. secret-safe diagnostics;
10. additive runtime integration.

## 3. Non-goals

WP-216 SHALL NOT implement:

- a graphical installation wizard;
- an HTTP installer;
- a vendor-specific database migration engine;
- Composer installation or package downloads;
- arbitrary shell command execution;
- remote deployment;
- operating-system user or permission management;
- secret generation through insecure randomness;
- automatic discovery of writable directories;
- modification of files outside authorized roots;
- silent overwrite of existing application state.

Database migrations remain a separate governed subsystem. WP-216 MAY consume a future migration contract but SHALL NOT define SQL migration semantics.

## 4. Layered architecture

The subsystem is divided into six boundaries.

### 4.1 Domain model

Immutable value objects describe:

- installation identifier;
- installation mode;
- target environment;
- requested application path;
- installation options;
- requirement identifiers;
- step identifiers;
- dependency declarations;
- mutation intent;
- rollback policy.

The domain model SHALL NOT access the filesystem, environment variables, database connections or network.

### 4.2 Requirement evaluation

Requirement probes evaluate explicit conditions such as:

- PHP version;
- required PHP extensions;
- writable authorized paths;
- availability of configuration values;
- adapter-provided infrastructure readiness.

A probe returns an immutable result. A failed required probe prevents plan execution. Optional probes MAY produce warnings.

Probes SHALL NOT mutate application state.

### 4.3 Planning

The planner converts an installation request, requirement results and registered contributions into an immutable `InstallationPlan`.

The plan SHALL contain:

- ordered steps;
- preconditions;
- declared mutations;
- rollback capabilities;
- warnings;
- a reproducible fingerprint.

Planning SHALL be deterministic for equivalent inputs.

### 4.4 Execution

The executor applies an already compiled plan.

Execution SHALL:

- reject plans whose preconditions are no longer satisfied;
- execute steps in declared order;
- stop on non-recoverable failure;
- record every attempted step;
- invoke rollback only where explicitly supported;
- preserve the original failure as the primary cause;
- return an immutable execution report.

Execution SHALL NOT infer additional steps.

### 4.5 Adapters

Infrastructure operations SHALL be isolated behind contracts, including:

- filesystem mutation;
- configuration persistence;
- secret storage;
- database readiness;
- future migration execution;
- clock and identifier generation where required.

The Foundation SHALL depend on contracts, never concrete vendor adapters.

### 4.6 Runtime integration

Runtime integration SHALL be optional and additive.

A future Installer service provider MAY expose planning and execution services through the application container. Application boot SHALL NOT run installation automatically.

## 5. Installation lifecycle

The governed lifecycle is:

```text
Request
  → Normalize
  → Evaluate requirements
  → Compile plan
  → Review or dry-run
  → Authorize execution
  → Execute
  → Verify
  → Commit result
  → Roll back supported mutations on failure
  → Produce report
```

No phase may be skipped implicitly.

## 6. Determinism

Equivalent normalized inputs SHALL produce:

- the same ordered step identifiers;
- the same requirement ordering;
- the same mutation declarations;
- the same plan fingerprint.

Ordering SHALL use explicit priority and registration order. Hash-map iteration, filesystem enumeration and locale-sensitive comparisons SHALL NOT determine execution order.

## 7. Safety invariants

### INS-001 — Explicit authorization

No mutation SHALL occur without an explicit execution request.

### INS-002 — Authorized roots

Filesystem mutations SHALL be constrained to declared authorized roots and safe relative paths.

### INS-003 — No silent overwrite

Existing files SHALL NOT be overwritten unless the plan contains an explicit overwrite policy and the executor validates it immediately before mutation.

### INS-004 — Secret redaction

Secrets SHALL NOT appear in diagnostics, logs, exceptions, reports, fingerprints or serialized plans.

### INS-005 — Plan immutability

A compiled plan SHALL NOT change during execution.

### INS-006 — Time-of-check validation

Preconditions that can change between planning and execution SHALL be checked again immediately before the affected mutation.

### INS-007 — Typed failure

Requirement, planning, authorization, execution, verification and rollback failures SHALL remain distinguishable.

### INS-008 — Controlled rollback

Rollback SHALL apply only to mutations that declare a supported compensating operation. The Installer SHALL NOT claim transactional guarantees for irreversible external effects.

### INS-009 — No arbitrary code execution

Installer contributions SHALL be registered through governed contracts. Installation metadata SHALL NOT contain executable PHP, shell commands or dynamically evaluated expressions.

### INS-010 — Idempotency declaration

Every step SHALL declare whether it is idempotent. Re-execution policy SHALL be explicit.

## 8. Installation steps

An installation step SHALL declare:

- stable identifier;
- description;
- priority;
- dependencies;
- required capabilities;
- mutation classification;
- idempotency;
- rollback support;
- sensitive-input usage;
- verification policy.

Dependency graphs SHALL be acyclic.

A missing dependency or cycle SHALL fail plan compilation.

## 9. Mutation classes

The architecture recognizes:

- directory creation;
- file creation;
- controlled file replacement;
- configuration value persistence;
- secret reference persistence;
- adapter-defined infrastructure operation;
- future migration invocation.

Each mutation SHALL expose a safe diagnostic representation that excludes secret values.

## 10. Dry-run

Dry-run SHALL compile and inspect the same plan used for execution.

Dry-run MAY evaluate read-only preconditions but SHALL NOT:

- create files;
- modify configuration;
- connect with mutation privileges;
- execute migrations;
- generate persistent secrets;
- invoke rollback.

The dry-run report SHALL clearly distinguish verified facts from adapter-declared assumptions.

## 11. Rollback model

Rollback is compensating, not universally transactional.

The execution report SHALL distinguish:

- execution succeeded;
- execution failed and rollback succeeded;
- execution failed and rollback partially failed;
- execution failed and rollback was unavailable;
- verification failed after mutations.

Rollback SHALL run in reverse successful-step order.

A rollback failure SHALL NOT replace the original execution failure as the primary cause.

## 12. Extension model

Modules MAY contribute:

- requirement probes;
- installation steps;
- configuration declarations;
- verification rules.

Contributions SHALL be explicit and associated with a stable module identity.

The same rules used by the resource subsystem apply conceptually:

- deterministic registration;
- explicit precedence;
- duplicate detection;
- immutable compiled result.

Core SHALL NOT depend on module implementations.

## 13. Relationship with existing subsystems

### Configuration 2.0

The Installer MAY prepare configuration values and persistence operations through Configuration contracts. It SHALL NOT bypass validation, precedence or secret policies.

### Modules 2.0

Module installation contributions SHALL use stable module identities and explicit registration.

### Structured Logging 2.0

Execution MAY emit structured events through an injected logging contract. Secret-safe reporting remains mandatory even when no logger is configured.

### Error Handling and Recovery 2.0

Installer failures SHALL preserve typed causes and deterministic recovery information. Global error-handler registration is outside WP-216.

### Resource Management

Installer assets and templates MAY be addressed through the safe resource resolver. The Installer SHALL NOT publish resources implicitly.

### Future Migrations

A future migration engine MAY be invoked through a narrow adapter. Migration discovery, ordering, transactions and schema history belong to their own Work Package.

## 14. Public API compatibility

WP-216 SHALL be additive.

It SHALL NOT:

- alter existing runtime lifecycle method signatures;
- trigger installation during application creation;
- require Installer services for existing applications;
- change Configuration, Modules or Resources public behavior.

## 15. Increment roadmap

### I1 — Architecture

Governed boundaries, lifecycle, safety invariants and roadmap.

### I2 — Immutable installation value model

Identifiers, modes, options, requirements, step metadata and typed exceptions.

### I3 — Requirement probes and deterministic assessment

Probe contracts, immutable results, severity and compiled requirement reports.

### I4 — Installation step registry and dependency planning

Explicit contributions, duplicate detection, dependency validation, cycle detection and deterministic ordering.

### I5 — Safe mutation planning

Authorized targets, mutation descriptors, overwrite policies, secret-safe representations and plan fingerprints.

### I6 — Execution, verification and rollback

Executor contracts, execution state, reports, reverse rollback and primary-cause preservation.

### I7 — Configuration, module and infrastructure contributions

Configuration provisioning, module contributions and narrow adapters for external infrastructure and future migrations.

### I8 — Runtime integration and Work Package completion

Installer plan aggregate, service provider, optional application exposure, examples, compatibility tests and completion evidence.

## 16. Acceptance criteria

WP-216 is complete when:

1. all eight increments are implemented;
2. planning is deterministic and immutable;
3. no mutation occurs during planning or dry-run;
4. filesystem operations are confined to authorized roots;
5. secret data is excluded from diagnostics and fingerprints;
6. dependency cycles and duplicates fail deterministically;
7. rollback behavior is explicit and tested;
8. runtime integration remains optional;
9. Composer validation succeeds;
10. PHPUnit and PHPStan succeed;
11. Builder generation and validation produce zero diagnostics;
12. the complete Work Package is tagged in Git.
