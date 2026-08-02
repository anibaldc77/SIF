---
id: WP-221-I1-ARCHITECTURE-REVIEW
title: WP-221 I1 Architecture Review
summary: Reviews the Developer CLI boundaries, immutable command model, process input-output separation, exit-code policy, operational safety and eight-increment delivery roadmap.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-01
updated: 2026-08-01
work_package: WP-221
tags:
  - cli
  - console
  - commands
  - operations
  - installer
  - migrations
  - architecture
  - review
depends_on:
  - EG-337
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-221 I1 Architecture Review

## Scope reviewed

- position of the Developer CLI as a system-boundary adapter;
- dependency direction toward existing SIF subsystem contracts;
- immutable command identity and metadata;
- process input and terminal output separation;
- deterministic command registry and alias resolution;
- argument and option validation;
- stable process exit-code categories;
- console kernel responsibilities;
- destructive-operation and authorization policy;
- non-interactive execution requirements;
- command families for runtime, configuration, migration, Installer, modules and resources;
- observability and redaction;
- Service Provider extensibility;
- compatibility with `sif-builder`;
- Windows and Unix entry-point policy;
- eight-increment delivery sequence.

## Architectural decision

WP-221 SHALL introduce the Developer CLI as an explicit adapter over stable SIF contracts.

The CLI SHALL own parsing, command resolution, output and process exit codes. It SHALL NOT absorb domain logic from Installer, Migration, Configuration, Modules, Resources, Runtime or Persistence.

The required direction is:

```text
process adapter → CLI kernel → command adapter → subsystem contract
```

Subsystems SHALL remain independent of terminal APIs and process-global state.

## Safety decision

State-changing commands SHALL fail closed.

Interactive confirmation SHALL not constitute implicit authorization, and non-interactive execution SHALL require all mandatory inputs and authorizations explicitly.

Planning, dry-run, journaling, locking, transactions and rollback SHALL continue to be implemented by their authoritative subsystems.

## Compatibility decision

The existing `bin/sif-builder` is a specialized governed engineering executable and SHALL remain unchanged by the initial Developer CLI architecture.

WP-221 SHALL introduce separate thin launchers, with command behavior implemented in PHP and no domain logic in shell or batch files.

Command names and exit-code categories are compatibility-sensitive public surfaces.

## Readiness decision

WP-221 is correctly sequenced after WP-220.

The project already provides:

- runtime and lifecycle orchestration;
- Service Providers and Container 2.0;
- Configuration 2;
- structured logging and error handling;
- modules and resource management foundations;
- Installer planning, authorization, journaling and rollback;
- migration planning and execution;
- PDO migration adapters;
- persistence and PDO persistence;
- BaseModel 2.0.

The CLI can therefore expose stable operational capabilities rather than invent temporary command behavior.

## Increment decision

I1 is architecture-only.

No executable command, parser, entry point or runtime mutation is introduced in this increment. I2 may proceed with the immutable CLI value model once this architecture validates through the governed Builder.
