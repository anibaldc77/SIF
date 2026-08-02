---
id: EG-343
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
title: WP-221 I7 Module Resource Maintenance and CLI Extensibility Commands
summary: Defines module and resource inspection commands, non-mutating maintenance reporting, and explicit command contribution contracts.
authors:
  - SIF Engineering
created: 2026-08-02
updated: 2026-08-02
tags:
  - cli
  - modules
  - resources
  - maintenance
depends_on:
  - EG-337
  - EG-338
  - EG-339
  - EG-340
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-221 I7 Module Resource Maintenance and CLI Extensibility Commands

## Purpose

This increment exposes read-only operational commands for module and resource registries and defines an explicit extension boundary for third-party CLI commands.

## Commands

- `module:list` returns deterministic module summaries.
- `resource:inspect <namespace> <identifier>` returns a safe resource descriptor summary.
- `maintenance:summary` returns explicitly supplied maintenance state without mutating the application.

## Extensibility

`CliCommandContributorInterface` returns explicit command instances. Contributors are composed through `CliCommandContributorCollection` and registered into the existing deterministic registry. Reflection and filesystem scanning are not used.

## Safety

I7 performs no deletion, cache clearing, publication, module activation, resource mutation, SQL execution, or implicit maintenance. Mutating maintenance commands require future explicit authorization and dedicated services.
