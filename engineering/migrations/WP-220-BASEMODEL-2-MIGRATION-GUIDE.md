---
id: WP-220-BASEMODEL-2-MIGRATION-GUIDE
title: BaseModel 2.0 Migration Guide
summary: Guides applications from legacy active-record style models to explicit BaseModel 2.0 metadata, repositories and runtime services.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - basemodel
  - migration
  - compatibility
depends_on:
  - EG-336
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# BaseModel 2.0 Migration Guide

## 1. Migration strategy

Migrate one model boundary at a time. Keep legacy models operational while introducing explicit metadata, mapper and repository wiring for the replacement model.

## 2. Replace dynamic properties

Declare every persisted attribute through `ModelAttributeDefinition`. Replace direct dynamic-property assignment with `get()`, `set()` or controlled `fill()` calls.

## 3. Replace connection-aware models

Remove PDO, connection names and SQL fragments from model classes. Configure a provider-neutral repository and connect it through `ModelRepositoryBridge`.

## 4. Replace implicit persistence

Replace model methods that save or delete through global state with explicit bridge or lifecycle coordinator calls. Do not persist during destruction or serialization.

## 5. Replace implicit relations

Declare relations in `ModelRelationRegistry` and load them through `ModelRelationLoader`. Property access and serialization must remain query-free.

## 6. Introduce runtime composition

Register metadata and relations in `BaseModelRuntime`, then provide that runtime to Bootstrap. Runtime publication only exposes model infrastructure; repositories and lifecycle services remain explicit application composition concerns.

## 7. Compatibility checklist

- every attribute is declared;
- identity order is explicit;
- fillable, hidden and read-only policies are reviewed;
- casts are deterministic;
- soft-delete metadata is explicit;
- repository names and managed types match;
- hooks, events, context and audit are injected;
- relation loading is explicit;
- PHPUnit and PHPStan pass before removing the legacy model.
