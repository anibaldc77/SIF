---
id: WP-215-I1-REVIEW
title: WP-215 I1 Architecture Review
summary: Reviews the proposed architecture, boundaries, security posture, compatibility rules and incremental plan for Resource Management and Asset Foundation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-215
tags:
  - review
  - resources
  - assets
  - architecture
depends_on:
  - EG-289
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-215-I1 — Architecture Review

## Decision

The proposed Resource Management and Asset Foundation architecture is suitable for incremental implementation, subject to repository validation and approval of the stated exclusions.

## Findings

### Contract separation

The proposal correctly separates:

- resource identity;
- registration;
- resolution;
- filesystem access;
- publication;
- localization discovery;
- deployment-specific delivery.

This prevents the Foundation from becoming coupled to HTTP, templating or build-tool concerns.

### Security posture

Filesystem confinement is treated as a first-class invariant rather than an adapter detail.

The architecture explicitly rejects traversal, absolute-path misuse, null bytes and canonical-root escape.

### Determinism

Priority plus registration order provides a reproducible ordering model.

Silent replacement is prohibited, reducing environment-dependent behavior.

### Compatibility

The architecture is additive and does not replace existing helpers or public asset paths.

This is consistent with the alpha compatibility policy and SemVer constraints.

### Integration

The subsystem can integrate with modules, logging, error handling and Container 2.0 while retaining independent contracts.

## Risks

1. Filesystem canonicalization differs across operating systems.
2. Symbolic-link behavior requires platform-aware tests.
3. Override semantics can become difficult to audit if made implicit.
4. Publication can accidentally become coupled to HTTP or a specific web root.
5. Translation execution can expand scope beyond resource discovery.

## Required controls

- platform-neutral path value objects;
- Windows and POSIX path test vectors;
- explicit override policy;
- immutable registration ownership metadata;
- dry-run publication planning;
- no content execution;
- no implicit current-working-directory dependency.

## Increment authorization

WP-215-I2 may proceed with the immutable core value model and typed validation exceptions.

Production filesystem access, module integration and publication remain prohibited until their corresponding increments.
