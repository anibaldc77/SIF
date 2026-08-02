---
id: EG-347
title: Deterministic Directory and File Generation
summary: Defines filesystem-independent skeleton blueprints, deterministic generation plans, content fingerprints, overwrite decisions, conflict reporting, idempotent execution and a confined native filesystem adapter.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - foundation
  - application-skeleton
  - scaffolding
  - generation
  - filesystem
  - idempotency
depends_on:
  - EG-346
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-347 — Deterministic Directory and File Generation

## 1. Purpose

WP-222 I3 defines the deterministic planning and execution boundary used to create application directories and files from the immutable manifest model established by I2.

## 2. Blueprint

A skeleton blueprint SHALL contain the project manifest and an ordered set of directory or file artifacts. Every artifact path SHALL be declared by the manifest. Duplicate artifact paths SHALL be rejected.

File artifacts SHALL contain normalized LF content and a SHA-256 fingerprint. Directory artifacts SHALL NOT contain file content.

## 3. Planning

Generation SHALL be planned before mutation. Each entry SHALL resolve to one action: create directory, create file, replace file, skip or conflict.

The planner SHALL inspect existing targets only through `SkeletonFilesystemInterface`. Plans SHALL expose a deterministic fingerprint and SHALL be non-executable when any conflict exists.

## 4. Existing targets

An unchanged file SHALL be skipped. Existing directories SHALL be skipped when the artifact is a directory. Type mismatches SHALL be conflicts.

Different existing file content SHALL follow the manifest overwrite policy. `fail` creates a conflict, `skip` preserves the existing file and `replace` authorizes replacement only where the manifest already permits skeleton ownership.

## 5. Execution

The executor SHALL reject plans containing conflicts. It SHALL create directories and write only entries explicitly represented by the plan. Skipped entries SHALL cause no mutation.

Repeated planning after successful execution SHALL produce only skip actions when generated content remains unchanged.

## 6. Native filesystem confinement

`NativeSkeletonFilesystem` SHALL operate relative to one existing root directory. All target paths SHALL originate from validated `ProjectPath` values. The adapter SHALL create missing parent directories and SHALL report read or write failures through application-skeleton exceptions.

## 7. Deferred scope

I3 does not render templates, construct bootstrap content, interpret environments, register CLI commands, authorize first-run or invoke Installer and Migration runtimes.
