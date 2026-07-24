---
id: DOMAIN-GLOSSARY
title: Domain Glossary
summary: **Specification:** SPEC-WP-004-DI-CONTAINER.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - domain
  - glossary
work_package: WP-004
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# WP-004 — Domain Glossary

**Specification:** SPEC-WP-004-DI-CONTAINER

**Document:** Domain Glossary

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This glossary defines the canonical terminology of the Dependency Injection Container domain.

Every term defined herein SHALL have exactly one meaning throughout WP-004.

Other specifications MAY reference these definitions but SHALL NOT redefine them.

---

# 2. Core Concepts

## Application

The runtime owner of a single Dependency Injection Container.

An Application SHALL own exactly one Container.

---

## Container

The Aggregate Root of the Dependency Injection domain.

The Container is responsible for coordinating service registration and service resolution.

The Container SHALL NOT perform unrelated framework responsibilities.

---

## Service

An object managed by the Container.

A Service is identified by a canonical Service Identifier.

---

## Service Identifier

A unique string identifying a Service within one Container.

Identifiers are case-sensitive.

Identifiers are immutable.

Identifiers SHALL be unique.

---

## Binding

A registration associating one Service Identifier with an implementation strategy.

Bindings are immutable after registration.

Replacing a Binding creates a new Binding.

---

## Binding Type

The registration strategy associated with a Binding.

Supported values are:

- Transient
- Singleton
- Instance
- Factory
- Alias

---

## Lifetime

The lifetime policy governing the reuse of resolved objects.

Lifetime is independent from implementation details.

---

## Alias

An alternate Service Identifier referencing another canonical Service Identifier.

Aliases SHALL NOT own implementations.

---

## Factory

A strategy responsible for creating service instances.

Factories SHALL NOT register services.

Factories SHOULD be stateless.

---

## Resolution

The process of obtaining one service instance from the Container.

---

## Resolution Context

The internal state associated with one resolution operation.

It tracks recursion and diagnostic information.

---

## Resolution Path

The ordered sequence of Service Identifiers visited during one resolution.

It exists solely for diagnostics and circular dependency detection.

---

## Resolution Engine

The domain service responsible for executing the dependency resolution algorithm.

The Resolution Engine SHALL NOT own registration state.

---

## Binding Repository

Internal repository responsible for storing Bindings.

---

## Alias Repository

Internal repository responsible for storing Aliases.

---

## Singleton Repository

Internal repository responsible for storing instantiated shared services.

---

## Circular Dependency

A dependency graph containing one or more cycles.

Circular dependencies SHALL be rejected deterministically.

---

## Registration

The act of creating or replacing a Binding.

---

## Resolution Failure

A deterministic failure occurring during service resolution.

Every Resolution Failure SHALL produce a typed exception.

---

# 3. Domain Relationships

The following conceptual relationships exist.

```
Application
    owns
        Container

Container
    owns
        Binding Repository
        Alias Repository
        Singleton Repository

Container
    coordinates
        Resolution Engine

Resolution Engine
    resolves
        Binding

Binding
    references
        Service Identifier

Alias
    references
        Service Identifier
```

---

# 4. Reserved Terms

The following terms are reserved by WP-004.

- Container
- Binding
- Alias
- Factory
- Resolution
- Lifetime
- Singleton
- Transient
- Service Identifier

Future Work Packages SHALL NOT redefine these terms.

---

# 5. Naming Rules

Every domain concept SHALL:

- have exactly one canonical name;
- use singular form;
- avoid abbreviations;
- remain implementation-independent.

---

# 6. Non-Domain Terms

The following concepts belong to infrastructure and are intentionally excluded from the domain vocabulary.

- Reflection
- Autowiring
- Attributes
- PHP Namespace
- Composer
- Filesystem
- HTTP
- SQL
- JSON

---

# 7. Acceptance Criteria

This glossary is complete when:

- every core domain concept has one canonical definition;
- no duplicate terminology exists;
- every future chapter can reference these definitions without redefining them.

---

# End of Document