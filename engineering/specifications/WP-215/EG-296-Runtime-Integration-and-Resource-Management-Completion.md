---
id: EG-296
title: Runtime Integration and Resource Management Completion
summary: Defines the immutable ResourceManagementPlan, runtime service provider, application exposure and compatibility guarantees completing WP-215.
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
  - runtime
  - service-provider
  - completion
depends_on:
  - EG-289
  - EG-290
  - EG-291
  - EG-292
  - EG-293
  - EG-294
  - EG-295
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-296 — Runtime Integration and Resource Management Completion

## 1. Purpose

This specification completes WP-215 by defining the additive runtime boundary through which a fully compiled resource graph is exposed to an application.

## 2. ResourceManagementPlan

`ResourceManagementPlan` is an immutable aggregate of:

- a compiled resource registry;
- a compiled module contribution plan;
- an immutable snapshot of authorized roots;
- keyed immutable translation plans;
- an optional compiled publication plan.

The plan performs no publication, scanning, transformation or dynamic discovery. It may create a safe resolver from its authorized-root snapshot.

Duplicate root identifiers and empty translation-plan keys are rejected during construction.

## 3. Runtime provider

`RuntimeResourceManagementServiceProvider` publishes the plan and a safe path resolver only when the application implements the mutable resource-management contract. During lifecycle boot it contributes the `resource-management` capability.

The provider must not copy files, mutate compiled plans, scan module directories or install global handlers.

## 4. Application contracts

`ResourceManagementAwareApplicationInterface` exposes nullable access to the configured plan and resolver. `MutableResourceManagementApplicationInterface` adds the controlled publication method used by the provider.

`Application` implements both contracts while preserving all existing lifecycle signatures.

## 5. Bootstrap integration

`Bootstrap` accepts an optional trailing `ResourceManagementPlan` parameter. When absent, behavior remains unchanged. When present, Bootstrap adds the runtime provider and passes the same plan identity into the application.

Logging, error handling, modules and resource management remain independently configurable.

## 6. Compatibility

The integration is additive:

- existing constructor calls remain valid;
- `boot()`, `run()` and `shutdown()` remain unchanged;
- no global helpers are introduced;
- no existing asset mechanism is automatically replaced;
- no filesystem write occurs during application creation or lifecycle execution.

## 7. Completion

WP-215 is complete when I1 through I8, their tests, governed documents and generated repository artifacts pass the project quality gate.
