---
id: EG-292
title: Authorized Roots and Safe Filesystem Resolution
summary: Defines explicit authorized resource roots, canonical path resolution, confinement validation and symbolic-link escape protection.
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
  - filesystem
  - security
  - paths
depends_on:
  - EG-289
  - EG-290
  - EG-291
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-292 — Authorized Roots and Safe Filesystem Resolution

## 1. Purpose

WP-215-I4 introduces an explicit security boundary between portable resource paths and local filesystem paths.

A `ResourcePath` remains storage-neutral and relative. Filesystem access SHALL occur only through an explicitly registered `AuthorizedResourceRoot` and a `ResourcePathResolverInterface` implementation.

This increment SHALL NOT publish, copy, transform, bundle or serve resources. It SHALL NOT discover modules or integrate with runtime bootstrap.

## 2. Root identity

Each authorized root SHALL have a portable, case-sensitive `ResourceRootIdentifier`.

Identifiers SHALL:

- contain between 1 and 128 characters;
- begin with an alphanumeric character;
- contain only alphanumeric characters, `.`, `_` and `-`;
- reject whitespace, separators, traversal syntax and null bytes.

## 3. Authorized root

An `AuthorizedResourceRoot` SHALL:

- reference an existing directory;
- require the directory to be readable at registration time;
- canonicalize the directory through `realpath()`;
- store a normalized absolute path independent of the current working directory;
- reject null bytes and invalid roots.

A root collection SHALL reject duplicate identifiers and expose typed lookup failure.

## 4. Resolution algorithm

For a requested root and relative resource path, the safe filesystem resolver SHALL:

1. locate the explicit authorized root;
2. append the already validated portable `ResourcePath`;
3. canonicalize the candidate with `realpath()`;
4. require an existing regular file;
5. verify that the canonical candidate remains inside the canonical root boundary;
6. require the file to be readable;
7. return an immutable `ResolvedResourcePath`.

Resolution SHALL NOT search alternative roots or apply fallback behavior.

## 5. Confinement

Confinement SHALL compare canonical paths using a directory boundary, not a simple textual prefix.

For example, a root `/srv/public` SHALL NOT authorize `/srv/public-old/file.css`.

Comparison SHALL be case-insensitive on Windows and case-sensitive on platforms whose directory separator is not `\\`.

## 6. Symbolic links

Canonicalization SHALL resolve symbolic links before confinement is evaluated.

A symbolic link whose target leaves the authorized root SHALL raise `ResourcePathEscapeException`.

A symbolic link whose final target remains within the same root MAY resolve successfully.

This increment validates the path at resolution time. Atomic open-handle confinement and operating-system sandboxing remain outside its scope.

## 7. Failure model

The subsystem SHALL expose typed failures for:

- invalid root definition;
- duplicate root identity;
- unknown root;
- missing or non-regular resource file;
- canonical path escape;
- unreadable resource file.

Failure messages MAY contain the logical root identifier and relative resource path. They SHOULD avoid exposing unrelated filesystem data.

## 8. Determinism and compatibility

Resolution SHALL depend only on:

- the explicit root collection;
- the requested root identifier;
- the validated relative path;
- the filesystem state at resolution time.

The increment is additive and SHALL NOT alter existing resource registry behavior or public runtime signatures.
