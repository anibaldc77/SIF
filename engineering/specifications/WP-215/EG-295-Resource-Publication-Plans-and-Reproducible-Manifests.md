---
id: EG-295
title: Resource Publication Plans and Reproducible Manifests
summary: Defines collision-safe publication targets, immutable publication plans, canonical manifests and reproducible SHA-256 fingerprints.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-215
tags:
  - foundation
  - resources
  - publication
  - manifests
  - fingerprints
depends_on:
  - EG-289
  - EG-290
  - EG-291
  - EG-292
  - EG-293
  - EG-294
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-295 — Resource Publication Plans and Reproducible Manifests

## 1. Purpose

WP-215-I7 defines a declarative publication model for already identified resources. It SHALL compile immutable publication plans, reject portable target collisions, emit canonical manifests and derive reproducible SHA-256 manifest fingerprints.

The increment SHALL NOT copy, delete or transform files, create directories, publish to a CDN, mutate runtime state or install global helpers.

## 2. Publication requests

A `ResourcePublicationRequest` SHALL bind:

- one immutable `ResourceDescriptor`;
- one explicit source `ResourceRootIdentifier`;
- one relative target `ResourcePath`;
- one SHA-256 `ResourceContentFingerprint`;
- one non-negative content size.

The content fingerprint SHALL be supplied or derived by the caller. The planner SHALL not read source files.

## 3. Target safety and collisions

Publication targets SHALL use the existing safe relative `ResourcePath` model. The planner SHALL reject:

- duplicate qualified resource identities;
- duplicate exact target paths;
- case-only target collisions.

Case-only collisions SHALL be rejected even on case-sensitive systems so the same plan remains portable to Windows and other case-insensitive filesystems.

No implicit overwrite or last-writer-wins behavior SHALL exist.

## 4. Deterministic planning

Input order SHALL be retained as provenance through a zero-based publication order. Effective publications SHALL be ordered by:

1. ascending target path;
2. ascending original publication order.

The compiled plan SHALL be immutable and SHALL expose no mutation or execution operation.

## 5. Canonical manifest

Each manifest entry SHALL include:

- qualified resource identifier;
- resource type;
- source root and source path;
- target path;
- priority;
- logical version and owner when present;
- content SHA-256;
- content size;
- original publication order.

Manifest entries SHALL be sorted canonically by target path and qualified identifier. JSON serialization SHALL preserve governed field order, unescaped slashes and Unicode text.

The manifest fingerprint SHALL be the lowercase SHA-256 digest of the canonical JSON representation.

## 6. Failure model

Validation and planning failures SHALL use typed exceptions for invalid content fingerprints, invalid requests, duplicate resource publication, target collisions, invalid publication order and invalid manifests.

## 7. Deferred scope

The following remain deferred:

- filesystem publication execution;
- atomic copy and rollback;
- stale target cleanup;
- minification, bundling and transpilation;
- CDN and remote storage;
- runtime service-provider integration.
