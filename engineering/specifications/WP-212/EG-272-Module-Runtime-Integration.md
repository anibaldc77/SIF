---
id: EG-272
title: Module Runtime Integration and Closure
summary: Defines governed application of resolved module contributions to Configuration, Container, capabilities, Service Providers, and Bootstrap.
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
  - runtime
  - bootstrap
  - diagnostics
depends_on:
  - EG-265
  - EG-266
  - EG-267
  - EG-268
  - EG-269
  - EG-270
  - EG-271
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-272 — Module Runtime Integration and Closure

## 1. Purpose

Define the final governed integration of a resolved module plan with Foundation runtime services while preserving the historical non-module bootstrap path.

## 2. Optional bootstrap integration

`Bootstrap` MAY receive a `ModuleRuntimeBootstrapper` as a final optional constructor argument. When absent, application creation MUST preserve existing configuration loading, capability publication, provider collection and lifecycle behavior.

## 3. Resolution and composition

Runtime integration MUST resolve the module plan and compose contributions before applying them. The mutable registry MUST freeze according to the successful planning rules established by EG-269.

## 4. Capability validation

Required capabilities MUST be validated against the union of capabilities already available in Foundation and capabilities contributed by all enabled modules. Validation MUST complete before runtime contribution mutation begins.

## 5. Contribution application

Contributions MUST be applied in deterministic composed order:

1. module configuration sources are composed and merged over existing bootstrap configuration;
2. service definitions are registered in Container 2.0's definition registry;
3. capabilities are registered in the capability registry;
4. Service Providers are instantiated and appended to the existing ordered provider collection.

Service Provider classes MUST implement `ServiceProviderInterface` and MUST have no required constructor arguments for this increment.

## 6. Runtime publication

The created `Application` MUST expose the service definition registry and, when modules are configured, the immutable module runtime integration result. Existing constructor calls MUST remain valid through final optional parameters.

## 7. Fingerprint

A deterministic SHA-256 fingerprint MUST represent the enabled module identities and versions, disabled module reason codes, and contribution ownership maps. The fingerprint MUST NOT include configuration values, secrets, object state, factories or environment values.

## 8. Diagnostics

Successful integration MUST publish a stable diagnostic code and safe scalar context. Diagnostic context MAY include counts and the plan fingerprint. Exceptions MAY identify stable module, capability or provider identifiers but MUST NOT expose secret values or object dumps.

## 9. Failure behavior

Missing required capabilities MUST abort before configuration, service, capability or provider mutation. Provider instantiation failures MUST be wrapped in a typed integration exception preserving the original cause.

## 10. Closure

EG-272 completes WP-212 Module Registry 2.0. Future enhancements such as provider factories, transactional rollback across external registries, hot reload or asynchronous module activation require separate governed work packages.
