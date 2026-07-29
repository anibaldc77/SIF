---
id: EG-301
title: Safe Mutation Planning
summary: Defines authorized installation targets, immutable mutation descriptors, overwrite policies and deterministic secret-safe mutation plan fingerprints.
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
  - mutations
  - security
depends_on:
  - EG-297
  - EG-300
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-301 — Safe Mutation Planning

## 1. Purpose

WP-216-I5 introduces a mutation planning model that describes intended changes without performing them. Planning remains immutable, deterministic and safe to inspect.

## 2. Authorized targets

Filesystem mutations SHALL reference an `AuthorizedInstallationTarget` composed of:

- a stable authorized-root identifier;
- a normalized relative path.

Absolute paths, empty segments, `.` segments, `..` traversal and NUL bytes SHALL be rejected. Planning SHALL NOT resolve or enumerate the physical filesystem.

## 3. Overwrite policy

Every mutation SHALL declare one of:

- `deny`: existing state must not be overwritten;
- `if-unchanged`: replacement is permitted only when the current content matches an expected SHA-256 fingerprint;
- `allow`: replacement is explicitly authorized and still requires execution-time validation.

`if-unchanged` SHALL include the expected current fingerprint.

## 4. Mutation descriptors

A mutation descriptor SHALL contain:

- stable identifier;
- operation name;
- mutation classification;
- optional authorized target;
- overwrite policy;
- rollback policy;
- optional content fingerprint;
- optional expected-current fingerprint;
- scalar, diagnostic-safe metadata.

Filesystem mutations SHALL declare an authorized target. Fingerprints SHALL use lowercase SHA-256 hexadecimal values.

Mutation descriptors SHALL NOT contain raw file content, credentials, secret values, callbacks, closures, shell commands or executable expressions.

## 5. Mutation plans

A `MutationPlan` SHALL:

- preserve declared mutation order;
- reject duplicate identifiers;
- expose immutable descriptors;
- produce a deterministic SHA-256 fingerprint from its safe canonical summary.

Equivalent ordered intent SHALL produce the same fingerprint. A change in order or declared intent SHALL change the fingerprint.

## 6. Safety boundaries

I5 SHALL NOT:

- create or replace files;
- resolve authorized roots to physical paths;
- evaluate current filesystem state;
- persist configuration;
- execute infrastructure operations;
- invoke rollback.

These responsibilities belong to I6 and adapter boundaries.

## 7. Acceptance criteria

I5 is accepted when:

1. unsafe relative paths are rejected;
2. filesystem mutations require authorized targets;
3. overwrite intent is explicit;
4. conditional overwrite requires an expected fingerprint;
5. raw sensitive payloads do not appear in summaries or plan fingerprints;
6. duplicate mutation identifiers fail deterministically;
7. equivalent plans produce identical fingerprints;
8. PHPUnit and PHPStan pass.
