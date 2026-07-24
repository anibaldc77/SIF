---
id: EG-040
title: Configuration Model and JSON Loader
status: Draft
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-107
summary: Immutable repository configuration model and deterministic JSON loading for SIF Builder.
tags:
  - builder
  - configuration
  - json
  - validation
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
depends_on:
  - EG-039
related_adrs: []
---

# EG-040 — Configuration Model and JSON Loader

## Purpose

Increment 1 freezes the serialized root schema for `.sif/builder.json`, introduces a format-neutral immutable configuration model and loads JSON without leaking parser exceptions.

## Serialized root schema

Required keys:

- `schema_version`: currently `1.0`;
- `default_profile`: identifier of a declared profile;
- `profiles`: object keyed by profile identifier.

Optional key:

- `repository_policies`: object keyed by profile identifier.

Detailed profile inheritance and extension semantics remain assigned to WP-107 Increment 2 and Increment 3.

## Components

- `RepositoryConfiguration`
- `ConfigurationDiagnostic`
- `ConfigurationLoadResult`
- `RepositoryConfigurationLoaderInterface`
- `RepositoryConfigurationValidator`
- `JsonRepositoryConfigurationLoader`

## Missing configuration

When `.sif/builder.json` does not exist, the loader returns the backward-compatible built-in default configuration. Absence is not a diagnostic.

## Diagnostics implemented

- `CONFIG-101`: file cannot be read;
- `CONFIG-102`: malformed JSON;
- `CONFIG-103`: unsupported schema version;
- `CONFIG-104`: required root field missing;
- `CONFIG-105`: invalid root type or value;
- `CONFIG-106`: default profile is not declared.

## Determinism and safety

- no remote reads;
- no environment interpolation;
- no executable configuration;
- no arbitrary class names;
- diagnostics preserve their validation order;
- JSON exceptions are converted to governed diagnostics.
