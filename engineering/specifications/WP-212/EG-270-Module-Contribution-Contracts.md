---
id: EG-270
title: Module Configuration, Container and Capability Contribution Contracts
summary: Defines immutable and declarative module contribution boundaries for Configuration 2.0, Container 2.0, and framework capabilities.
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
  - configuration
  - container
  - capabilities
depends_on:
  - EG-265
  - EG-266
  - EG-267
  - EG-268
  - EG-269
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-270 — Module Contribution Contracts

## 1. Purpose

WP-212-I6 defines the declarative contribution boundary used by enabled modules to describe configuration sources, container service definitions, and framework capabilities without receiving mutable infrastructure objects.

## 2. Scope

The increment includes an explicit configuration namespace value, contribution-specific contracts, an immutable aggregate contribution set, duplicate detection, and safe structural validation.

The increment excludes contribution execution, cross-module ownership validation, capability requirement resolution, Service Provider activation, Runtime integration, diagnostics aggregation, fingerprints, and caching.

## 3. Configuration contribution

Configuration sources SHALL use existing Configuration 2.0 source contracts and SHALL require an explicit module configuration namespace.

A namespace SHALL be lowercase, portable, dot-delimited, and independent from filesystem paths or PHP namespaces.

Contribution contracts SHALL NOT expose configuration values through diagnostics and SHALL NOT mutate an already frozen configuration repository.

## 4. Container contribution

Modules SHALL contribute existing immutable `ServiceDefinition` values.

The module boundary SHALL NOT receive the mutable container or service definition registry. Registration and compilation SHALL be performed later by a governed composer in resolved module order.

Duplicate service identifiers inside one contribution set SHALL fail before application.

## 5. Capability contribution

Modules SHALL publish existing `CapabilityInterface` values through a declarative contract.

Duplicate capability identifiers inside one contribution set SHALL fail before publication. Publication timing remains deferred until module activation.

## 6. Aggregate contribution set

`ModuleContributionSet` SHALL preserve declaration order and expose immutable lists of configuration sources, service definitions, and capabilities.

An empty contribution set SHALL be valid.

## 7. Safety and determinism

Validation errors SHALL identify only stable contribution identifiers. They SHALL NOT include configuration values, secrets, service instances, serialized objects, stack traces, or host-specific paths.

## 8. Deferred work

WP-212-I7 will compose contributions in resolved module order, validate namespace and capability ownership across modules, and integrate Service Provider ordering without yet completing full Runtime lifecycle integration.
