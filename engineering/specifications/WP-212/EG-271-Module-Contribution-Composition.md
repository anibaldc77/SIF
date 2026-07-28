---
id: EG-271
title: Module Contribution Composition and Ownership
summary: Defines deterministic composition, ownership validation, and service provider ordering for enabled module contributions.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-212
tags:
  - foundation
  - modules
  - composition
  - ownership
  - service-providers
depends_on:
  - EG-265
  - EG-266
  - EG-267
  - EG-268
  - EG-269
  - EG-270
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-271 — Module Contribution Composition and Ownership

## 1. Purpose

Define deterministic composition of declarative module contributions after a module plan has been resolved, without executing configuration sources, service definitions, capabilities, or service providers.

## 2. Inputs

Composition MUST consume an immutable `ResolvedModulePlan` and a `ModuleRegistryInterface`. Enabled modules MUST be processed in the exact order published by the plan.

## 3. Module access

The registry MUST retain registered module instances and expose read-only lookup by `ModuleId`. This additive lookup MUST NOT weaken freeze semantics.

## 4. Contribution providers

A module implementing `ModuleContributionProviderInterface` MAY publish a `ModuleContributionSet`. A module not implementing that contract contributes an empty set.

## 5. Ownership

The following identifiers MUST have a single enabled-module owner:

- configuration namespace;
- service definition identifier;
- capability identifier;
- service provider class.

A collision MUST abort composition with a typed exception. The composed result MUST expose immutable ownership maps suitable for diagnostics and later integration.

## 6. Descriptor consistency

A contributed configuration namespace MUST equal the namespace declared by the module descriptor. Every contributed capability MUST be declared in `providedCapabilities`, and every declared provided capability MUST be contributed.

Each service provider class MUST implement `ServiceProviderInterface`. Provider classes MUST be preserved in module-plan order and descriptor declaration order.

## 7. Output

`ComposedModuleContributions` MUST preserve deterministic lists of configuration sources, service definitions, capabilities and service provider classes. It MUST NOT retain mutable registry references or execute contributions.

## 8. Security

Collision and consistency diagnostics MAY identify module IDs and stable contribution identifiers. They MUST NOT include configuration values, secret values, object dumps, factory contents or environment values.

## 9. Deferred work

Execution against Configuration 2.0, Container 2.0, Capability Registry and Runtime lifecycle belongs to WP-212-I8.
