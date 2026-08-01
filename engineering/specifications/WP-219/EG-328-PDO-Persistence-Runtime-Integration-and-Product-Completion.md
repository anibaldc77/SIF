---
id: EG-328
title: PDO Persistence Runtime Integration and Product Completion
summary: Defines explicit publication of the completed PDO persistence adapter layer and the governed completion boundary for WP-219.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-219
tags:
  - foundation
  - persistence
  - pdo
  - runtime
  - completion
depends_on:
  - EG-321
  - EG-322
  - EG-323
  - EG-324
  - EG-325
  - EG-326
  - EG-327
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Persistence Runtime Integration and Product Completion

## Purpose

Publish the completed PDO persistence composition through explicit runtime integration without performing database work during provider registration.

## Normative requirements

1. Runtime integration MUST receive an already constructed persistence runtime.
2. Provider registration MUST NOT issue SQL, begin transactions, or inspect database metadata.
3. The provider MUST publish `persistence` and `persistence.pdo` capabilities.
4. The provider MUST install the runtime only when the application implements `MutablePersistenceApplicationInterface`.
5. Bootstrap integration MUST remain optional.
6. Runtime summaries MUST NOT expose DSNs, credentials, parameter values, or row contents.
7. WP-219 completion MUST preserve WP-209 provider-neutral contracts.

## Completion boundary

WP-219 completes PDO connection, query translation, platform compilers, prepared execution, transaction coordination, repositories, composite keys, Unit of Work, and explicit Runtime integration. BaseModel 2.0 remains a subsequent Work Package.
