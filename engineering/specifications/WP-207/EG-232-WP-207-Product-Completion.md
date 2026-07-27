---
id: EG-232
title: WP-207 Execution Context Product Completion
summary: Completes WP-207 by consolidating the immutable execution context model, deterministic construction and derivation, safe serialization and redaction, explicit scoped propagation, and integration contracts.
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
  - propagation
  - serialization
  - integration
  - completion
depends_on:
  - EG-231
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

# EG-232 — WP-207 Product Completion

## 1. Purpose

This specification closes WP-207 after the Execution Context subsystem has been defined, implemented, and validated as an immutable, explicit, infrastructure-neutral foundation for future event, command, audit, transport, and persistence integrations.

WP-207 does not introduce ambient context, automatic Runtime wiring, persistence, authentication, or audit storage. It establishes the contracts and value model required by those future adapters while preserving Runtime and application compatibility.

## 2. Completed increments

WP-207 includes:

- WP-207-I1 — execution context architecture;
- WP-207-I2 — core contracts and immutable model;
- WP-207-I3 — deterministic factory and parent/child derivation;
- WP-207-I4 — canonical serialization, explicit redaction, and diagnostic snapshots;
- WP-207-I5 — explicit context propagation and scoped execution;
- WP-207-I6 — context-aware integration contracts and immutable envelopes;
- WP-207-I7 — product completion and acceptance baseline.

## 3. Completed public surface

The completed subsystem provides:

- `ExecutionContextInterface`;
- `ExecutionContext`;
- `ContextId`;
- `ContextAttributes`;
- `ContextIdGeneratorInterface`;
- `ClockInterface`;
- `ExecutionContextFactoryInterface` and `ExecutionContextFactory`;
- `ContextSerializerInterface` and `ExecutionContextSerializer`;
- `ContextRedactionPolicyInterface` and `ContextRedactionPolicy`;
- `ContextDiagnosticSnapshot`;
- `ContextCarrierInterface` and `ContextCarrier`;
- `ExecutionContextScopeInterface` and `ExecutionContextScope`;
- `ContextAwareInterface`;
- `ContextEnvelopeInterface`, `ContextEnvelope`, and `ContextEnvelopeFactory`;
- typed exceptions for invalid identifiers, keys, values, and redaction policies.

## 4. Architectural guarantees

The completed subsystem SHALL preserve these invariants:

1. Execution contexts are immutable value graphs.
2. Context propagation is explicit and never ambient.
3. No process-global, static, singleton, thread-local, or fiber-local current context exists.
4. Root creation and child derivation use injected identifier and clock contracts.
5. Child derivation preserves correlation and assigns explicit parent and causation relationships.
6. Context attributes accept only validated JSON-compatible values.
7. Canonical serialization is deterministic and independent of host state.
8. Redaction is explicit, key-based, deterministic, and applied before diagnostic export.
9. Scoped execution passes the context directly to the callback and does not hide restoration state.
10. Envelopes preserve exact payload and context identity and do not reinterpret payloads.
11. Context remains independent of Runtime, Bootstrap, Kernel, Lifecycle, Event Dispatcher, Observation, Audit, databases, transports, authentication, and sessions.
12. Existing Runtime capabilities and behavior remain unchanged.

## 5. Security and confidentiality boundary

Execution Context MAY contain identifiers and operational metadata but SHALL NOT be treated as a secret store.

Callers are responsible for selecting attributes appropriate for propagation. Diagnostic or external representations SHALL use an explicit redaction policy. Automatic secret detection, encryption, access control, retention, and secure persistence remain outside WP-207.

## 6. Acceptance baseline

The completion baseline validated after WP-207-I6 is:

```text
PHPUnit: 558 tests, 1608 assertions, 0 failures, 0 errors, 0 warnings
PHPStan: 0 errors
SIF Builder: succeeded, 0 diagnostics
Second governed generation: 0 artifacts
```

WP-207-I7 is documentary and MUST NOT change the functional source baseline.

## 7. Completion quality gate

WP-207 may be marked complete when these commands succeed:

```powershell
composer validate --strict
vendor\bin\phpunit --display-warnings
vendor\bin\phpstan analyse
powershell -ExecutionPolicy Bypass -File tools\builder\scripts\generate-governed-artifacts.ps1 -RepositoryRoot D:\SIF
php bin\sif-builder validate
git diff --check
```

The second governed artifact generation SHALL produce zero artifacts.

## 8. Deferred integrations

The following remain separate work packages:

- audit record creation and persistence;
- automatic Runtime or event enrichment;
- HTTP, CLI, queue, session, authentication, and database adapters;
- distributed trace propagation standards;
- encryption, signing, retention, and privacy enforcement;
- ambient context access;
- framework-level automatic context lifecycle management.

## 9. Completion decision

When the quality gate and governed metadata validation pass, WP-207 SHALL be considered complete and suitable as the context foundation for the future Audit subsystem.
