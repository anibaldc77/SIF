# Chapter 1 — Foundation

**Specification:** SPEC-WP-004-DI-CONTAINER

**Chapter:** 01

**Title:** Foundation

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

## 1.1 Objective

This chapter defines the normative foundation of the Dependency Injection Container specified by WP-004.

It establishes the architectural objectives, terminology, scope, design principles, dependencies, conformance rules, and compatibility commitments that govern every implementation of the SIF Dependency Injection Container.

This chapter is normative.

Every subsequent chapter SHALL conform to the rules defined herein.

---

# 2. Motivation

Large institutional software systems require a deterministic mechanism for composing application services.

The Dependency Injection Container exists to:

- reduce coupling;
- centralize service composition;
- improve maintainability;
- simplify testing;
- support modular application architecture;
- provide a stable integration point for future framework components.

The Container SHALL remain explicit, deterministic and predictable.

---

# 3. Scope

WP-004 specifies:

- service registration;
- service resolution;
- lifetime management;
- singleton services;
- transient services;
- existing instances;
- factory bindings;
- alias resolution;
- circular dependency detection;
- typed diagnostics;
- runtime integration;
- application integration;
- bootstrap integration;
- service provider integration;
- public contracts;
- implementation quality requirements.

No additional functionality SHALL be inferred.

---

# 4. Out of Scope

The following capabilities are explicitly excluded.

## 4.1 Reflection

- Reflection Autowiring
- Automatic Constructor Discovery
- Runtime Reflection Injection

## 4.2 Metadata

- PHP Attributes
- Annotations
- XML configuration
- YAML configuration

## 4.3 Runtime Features

- Lazy Proxies
- Dynamic Proxy Generation
- Compiled Containers
- Runtime Code Generation

## 4.4 Framework Services

- Event Dispatcher
- Configuration System
- Database
- ORM
- HTTP
- Console
- Scheduler
- Queue
- Cache
- Audit

These capabilities belong to future Work Packages.

---

# 5. Design Goals

The Container SHALL satisfy the following goals.

## G-001

Deterministic behavior.

## G-002

Explicit configuration.

## G-003

Minimal public API.

## G-004

Strong typing.

## G-005

Low coupling.

## G-006

High cohesion.

## G-007

Runtime safety.

## G-008

Excellent testability.

## G-009

Long-term maintainability.

## G-010

Backward compatibility.

---

# 6. Non-Goals

WP-004 SHALL NOT attempt to become:

- an application framework;
- an enterprise service bus;
- an inversion-of-control platform;
- a configuration engine;
- a module loader.

Its single responsibility is dependency composition.

---

# 7. Architectural Principles

Every implementation SHALL comply with the following principles.

## P-001 Explicit Registration

Every service SHALL be registered explicitly.

Implicit registration is prohibited.

---

## P-002 Explicit Resolution

Every dependency SHALL be resolved through the Container API.

---

## P-003 Deterministic Resolution

Given the same registrations, the same resolution SHALL always produce the same observable result.

---

## P-004 Contract First

Consumers SHALL depend on contracts rather than concrete implementations whenever practical.

---

## P-005 Single Responsibility

Every class SHALL have one primary responsibility.

---

## P-006 Testability

Every observable behavior SHALL be verifiable through automated tests.

---

## P-007 Security by Design

The Container SHALL never expose internal runtime state unintentionally.

---

## P-008 Compatibility

Public APIs defined by WP-004 become compatibility protected after the first stable release.

---

# 8. Dependencies

This specification depends on:

## WP-000

Repository Standards.

Provides:

- governance;
- repository structure;
- quality process.

---

## WP-002

Support Library.

Provides:

- collections;
- contracts;
- exceptions;
- utilities.

---

## WP-003

Runtime Foundation.

Provides:

- Framework;
- Runtime;
- Bootstrap;
- Lifecycle;
- Application;
- Service Provider infrastructure.

---

# 9. Definitions

## Service

An object managed by the Dependency Injection Container.

---

## Binding

A registration associating a Service Identifier with a concrete implementation.

---

## Service Identifier

A unique string identifying a service.

---

## Shared Service

A service instantiated once and reused.

---

## Transient Service

A service instantiated for every resolution.

---

## Existing Instance

A previously created object managed by the Container.

---

## Factory

An object or callable responsible for constructing a service.

---

## Alias

An alternative identifier referencing a canonical Service Identifier.

---

## Resolution

The process of obtaining a service instance.

---

## Resolution Context

Internal structure used to detect recursive resolution and circular dependencies.

---

# 10. Terminology

The keywords:

- MUST
- MUST NOT
- REQUIRED
- SHALL
- SHALL NOT
- SHOULD
- SHOULD NOT
- MAY

are interpreted as defined by RFC 2119.

---

# 11. Conformance

An implementation claiming compliance with SPEC-WP-004 SHALL satisfy every normative requirement defined by this specification.

Implementations MAY extend the framework only through officially documented extension points.

Behavior not described by this specification SHALL NOT be considered compliant.

---

# 12. Compatibility Policy

Beginning with the first stable release:

- every public interface becomes compatibility protected;
- public method signatures SHALL remain stable;
- observable behavior SHALL remain stable;
- breaking changes require:
  - a new Specification revision;
  - an Architecture Decision Record (ADR);
  - a new major semantic version.

---

# 13. Quality Requirements

Every implementation SHALL provide:

- PHPUnit test suite;
- PHPStan Level 8 compliance;
- PSR-12 formatting;
- Composer validation;
- complete PHPDoc;
- implementation report;
- README;
- CHANGELOG;
- metadata.

---

# 14. Traceability

Every implementation phase SHALL explicitly reference the corresponding chapter of this specification.

Implementation reports SHALL identify:

- implemented requirements;
- deferred requirements;
- deviations;
- risks;
- quality gate results.

---

# 15. Chapter Acceptance Criteria

This chapter is considered complete when:

- scope is unambiguous;
- terminology is stable;
- dependencies are identified;
- goals are measurable;
- architectural principles are normative;
- conformance rules are defined;
- compatibility policy is established.

---

# End of Chapter 1