---
id: EG-336
title: BaseModel Runtime Integration, Compatibility and Product Completion
summary: Defines explicit BaseModel 2.0 runtime publication, compatibility boundaries, migration guidance and product completion criteria.
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
  - runtime
  - compatibility
depends_on:
  - EG-335
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-336 — BaseModel Runtime Integration, Compatibility and Product Completion

## 1. Purpose

WP-220 I8 completes BaseModel 2.0 by publishing its registries and stateless model services through an explicit runtime capability and by defining the migration boundary from the previous BaseModel generation.

## 2. Runtime composition

`BaseModelRuntime` SHALL aggregate the model metadata registry, relation registry, attribute caster, hydrator and serializer. Construction SHALL NOT resolve repositories, execute queries, flush a Unit of Work or mutate application models.

## 3. Application integration

Applications MAY implement `MutableBaseModelApplicationInterface`. `BaseModelRuntimeServiceProvider` SHALL install the supplied runtime only on compatible applications and SHALL publish the capabilities `models` and `models.basemodel2`.

## 4. Bootstrap safety

Bootstrap integration SHALL remain optional. Merely constructing or registering the runtime SHALL NOT open connections, execute SQL, load relations, dispatch lifecycle events or emit audit records.

## 5. Compatibility boundary

BaseModel 2.0 SHALL not emulate dynamic properties, implicit global connections, hidden SQL execution or automatic persistence on object destruction. Legacy applications SHALL migrate through explicit metadata, mapper, repository bridge and runtime composition.

## 6. Product completion

WP-220 is complete when metadata, state, CRUD, query, soft delete, lifecycle, audit, relations, Unit of Work and runtime integration are validated by PHPUnit, PHPStan and governed repository validation.
