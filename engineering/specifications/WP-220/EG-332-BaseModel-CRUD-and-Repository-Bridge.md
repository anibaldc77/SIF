---
id: EG-332
title: BaseModel CRUD and Repository Bridge
summary: Defines the BaseModel 2.0 runtime object, provider-neutral mapper bridge, simple and composite identity handling, and explicit save refresh and delete boundaries.
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
  - crud
  - repository
  - identity
  - mapper
depends_on:
  - EG-331
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-332 — BaseModel CRUD and Repository Bridge

## 1. Purpose

WP-220 I4 establishes the explicit persistence boundary for BaseModel 2.0 without coupling model objects to PDO or SQL.

## 2. Base model

`BaseModel` SHALL own immutable metadata and mutable attribute state. It SHALL expose controlled access, assignment, dirty tracking, public serialization, storage serialization and identity inspection. It SHALL NOT contain a PDO connection or execute SQL.

## 3. Mapper bridge

`BaseModelMapper` SHALL implement the provider-neutral mapper contract. Hydration SHALL create a model from a `StorageRecord` through an explicit factory. Extraction SHALL serialize model state to a `StorageRecord`. Successful hydration SHALL mark the model persisted and clean.

## 4. Repository bridge

`ModelRepositoryBridge` SHALL coordinate provider-neutral read and write repositories. Repository name and managed type SHALL match model metadata. `save()` SHALL synchronize the original snapshot only after repository success. `delete()` SHALL require a complete identity and mark the object deleted only after repository success. `refresh()` SHALL replace model state from persisted data.

## 5. Identity

Simple identities SHALL use `findById`. Composite identities SHALL remain ordered according to metadata and MAY be resolved by an explicit repository finder. Absence of a composite finder SHALL fail closed rather than infer provider-specific behavior.

## 6. Deferred scope

I4 does not introduce query builders, pagination, soft delete, lifecycle hooks, events, audit, relations or runtime registration.
