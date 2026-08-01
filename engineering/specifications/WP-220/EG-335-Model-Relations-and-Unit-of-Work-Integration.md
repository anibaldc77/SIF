---
id: EG-335
title: Model Relations and Unit of Work Integration
summary: Defines explicit BaseModel relation metadata, deterministic loading, key synchronization and Unit of Work coordination.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - foundation
  - basemodel
  - relations
  - unit-of-work
depends_on:
  - EG-334
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-335 — Model Relations and Unit of Work Integration

## 1. Purpose

WP-220 I7 introduces explicit relation definitions and model-level Unit of Work coordination without adding implicit queries or storage dependencies to BaseModel.

## 2. Relation definitions

A relation SHALL declare an owner metadata object, related metadata, a stable relation name, relation type and ordered local/foreign key lists. Key cardinalities SHALL match and every declared attribute SHALL exist in its corresponding metadata.

## 3. Supported relation types

The initial relation types are belongs-to, has-one and has-many. Relation types define result cardinality only; they SHALL NOT trigger automatic loading.

## 4. Explicit loading

Relations SHALL be loaded only through `ModelRelationLoader`. The loader SHALL build a provider-neutral `ModelQuery` and SHALL resolve a compatible `ModelQueryService` explicitly. Property access, serialization and JSON conversion SHALL NOT execute relation queries.

## 5. Key synchronization

`ModelRelationSynchronizer` SHALL copy ordered local key values to the related model through the managed-attribute boundary. Null owner keys SHALL fail closed.

## 6. Registry

`ModelRelationRegistry` SHALL reject duplicate relation names per owner model and SHALL preserve deterministic registration order.

## 7. Unit of Work

`ModelUnitOfWork` SHALL classify unpersisted models as new and persisted dirty models as dirty. Removal SHALL remain explicit. Commit, clear and state SHALL delegate to the existing provider-neutral Unit of Work contract.

## 8. Deferred scope

Runtime composition, compatibility migration guidance and product completion remain assigned to I8.
