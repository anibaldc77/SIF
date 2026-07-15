# WP-004 — Dependency Injection Container Specification

**Document ID:** SPEC-WP-004-DI-CONTAINER  
**Version:** 1.0.0  
**Status:** Draft (Chapter 1 Approved)  
**Framework:** SIF 2.0.0-alpha1  
**Work Package:** WP-004  
**Depends On:** WP-000, WP-002, WP-003

---

# Chapter 1 — Foundation

## 1. Purpose

The Dependency Injection Container is the official service composition mechanism of the SIF Framework.

Its purpose is to provide deterministic, explicit, testable and maintainable service registration and resolution while preserving the architectural principles established by SIF.

The implementation intentionally avoids hidden conventions, automatic discovery and runtime magic.

## 2. Scope

This Work Package specifies:

- Container contracts
- Explicit service registration
- Explicit service resolution
- Shared (singleton) services
- Transient services
- Existing instance registration
- Factory bindings
- Alias resolution
- Circular dependency detection
- Typed exception hierarchy
- Runtime integration
- Bootstrap integration
- Application integration
- Service Provider integration
- Documentation
- Tests
- Metadata

## 3. Out of Scope

The following features are intentionally excluded from WP-004:

- Reflection-based autowiring
- Attributes
- Annotations
- Lazy proxies
- Compiled containers
- Runtime code generation
- Automatic module discovery
- Configuration loading
- Event dispatching
- ORM integration
- HTTP Kernel
- Console services
- Scheduler
- Queue
- Cache persistence

These capabilities belong to future Work Packages.

## 4. Goals

The Container SHALL:

- remain deterministic;
- expose a minimal public API;
- be fully testable;
- integrate with Runtime Foundation;
- integrate with Service Providers;
- preserve backward compatibility of public contracts.

## 5. Non-Goals

The Container is not intended to become a full inversion-of-control platform.

Application architecture remains the responsibility of developers.

## 6. Architectural Principles

- Explicit Registration
- Deterministic Resolution
- Contract First
- Low Coupling
- High Cohesion
- Security by Design
- Testability
- Backward Compatibility

## 7. Dependencies

This specification depends on:

- WP-000 Repository Standards
- WP-002 Support Library
- WP-003 Runtime Foundation

## 8. Definitions

**Service** — Object managed by the Container.

**Binding** — Association between a service identifier and its implementation.

**Shared Service** — One instance reused for every resolution.

**Transient Service** — New instance returned on every resolution.

**Factory** — Callable or object responsible for creating a service.

**Alias** — Alternate identifier pointing to a canonical identifier.

**Resolution** — Process of obtaining a service instance.

**Resolution Context** — Internal stack used to detect circular dependencies.

## 9. Terminology

The key words MUST, SHALL, SHOULD, MAY and MUST NOT are interpreted according to RFC 2119.

## 10. Conformance

Any implementation claiming compliance with SPEC-WP-004 SHALL satisfy every normative requirement defined by this specification.

Partial implementations SHALL be marked as experimental.

---

**Chapter Status:** Approved

**Next Chapter:** Chapter 2 — Architecture
