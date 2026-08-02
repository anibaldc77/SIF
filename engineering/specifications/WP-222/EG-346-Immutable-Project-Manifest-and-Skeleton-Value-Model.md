---
id: EG-346
title: Immutable Project Manifest and Skeleton Value Model
summary: Defines the immutable, portable and deterministic value model for SIF application manifests, project paths, ownership, overwrite policy, entry points, environments, capabilities and first-run states.
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
  - manifest
  - scaffolding
  - first-run
  - portability
depends_on:
  - EG-345
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-346 — Immutable Project Manifest and Skeleton Value Model

## 1. Purpose

WP-222 I2 establishes the provider-neutral and filesystem-independent vocabulary used to describe a SIF application before any directories or files are generated.

## 2. Project identity

A project identifier SHALL be lowercase and hyphen-delimited. A project namespace SHALL be an explicit PSR-4-compatible namespace and SHALL NOT be inferred from the display name.

## 3. Portable paths

`ProjectPath` SHALL represent relative logical paths using `/`. Absolute paths, Windows drive prefixes, backslashes, traversal segments, dot segments, duplicate separators and control characters SHALL be rejected.

## 4. Ownership and overwrite policy

Every governed path SHALL declare one ownership class: `skeleton-owned`, `user-owned` or `runtime-owned`.

Overwrite policies SHALL be `fail`, `skip` or `replace`. The `replace` policy SHALL only be legal for skeleton-owned paths.

## 5. Manifest

`ProjectManifest` SHALL contain project identity, schema and skeleton versions, SIF compatibility constraint, minimum PHP version, entry points, environments, governed paths and optional capabilities.

Collections SHALL reject duplicates where identity would be ambiguous and SHALL serialize in deterministic order.

## 6. Secrets

The manifest SHALL contain declarations only. It SHALL NOT contain credentials, tokens, passwords, private keys or runtime environment values.

## 7. First-run states

First-run lifecycle states SHALL be explicit: uninitialized, validated, configured, planned, authorized, executed, completed and failed.

## 8. Deferred scope

I2 does not read or write `sif.project.json`, access the filesystem, render templates, create directories, invoke Installer, execute migrations or register CLI commands.
