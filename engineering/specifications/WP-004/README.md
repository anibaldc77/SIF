# WP-004 — Dependency Injection Container

**Document ID:** SPEC-WP-004-DI-CONTAINER

**Status:** Drafting

**Version:** 1.0.0

**Framework:** SIF 2.0.0-alpha1

**Work Package:** WP-004

---

# Overview

WP-004 defines the official Dependency Injection Container of the SIF Framework.

The Container is one of the Core components of SIF and provides deterministic, explicit and contract-driven service registration and resolution.

This specification is the single source of truth for every implementation of the SIF Dependency Injection Container.

No implementation shall introduce behavior not explicitly described by this specification.

---

# Objectives

WP-004 establishes:

- the public contracts;
- the service registration model;
- the dependency resolution algorithm;
- lifetime management;
- runtime integration;
- service provider integration;
- exception hierarchy;
- quality requirements;
- compatibility rules.

---

# Design Philosophy

The SIF Dependency Injection Container follows the architectural principles defined by the SIF Constitution.

The container is intentionally designed around explicit configuration instead of implicit behavior.

Automatic service discovery, reflection-based autowiring and runtime magic are excluded from this Work Package.

Predictability and maintainability are considered more important than reducing the amount of configuration code.

---

# Specification Structure

The specification is intentionally modular.

Each chapter is maintained independently while the complete specification is versioned as a whole.

| Chapter | Document | Status |
|----------|----------|--------|
| 1 | 01-Foundation.md | Approved |
| 2 | 02-Architecture.md | Draft |
| 3 | 03-Contracts.md | Draft |
| 4 | 04-Binding-Model.md | Draft |
| 5 | 05-Resolution-Engine.md | Draft |
| 6 | 06-Runtime-Integration.md | Draft |
| 7 | 07-Testing-and-Quality.md | Draft |
| 8 | 08-Product-Completion.md | Draft |

---

# Directory Layout

```
WP-004/

README.md
SUMMARY.md

01-Foundation.md
02-Architecture.md
03-Contracts.md
04-Binding-Model.md
05-Resolution-Engine.md
06-Runtime-Integration.md
07-Testing-and-Quality.md
08-Product-Completion.md

appendix/
examples/
diagrams/
```

---

# Dependencies

This specification depends on:

- WP-000 Repository Standards
- WP-002 Support Library
- WP-003 Runtime Foundation

No other Work Package shall be required.

---

# Out of Scope

The following features are intentionally excluded:

- Reflection Autowiring
- Attributes
- Annotation Processing
- Compiled Containers
- Lazy Proxies
- Automatic Discovery
- Configuration Loader
- Event Dispatcher
- HTTP
- ORM
- Audit
- Scheduler
- Queue
- AI

Future Work Packages may extend the Container through public extension points without modifying the contracts defined by this specification.

---

# Conformance

Every implementation claiming compliance with WP-004 SHALL satisfy every normative requirement defined in this specification.

Partial implementations SHALL identify themselves as experimental.

---

# Compatibility

Beginning with the first stable release of SIF, every public API defined by WP-004 becomes Compatibility Protected.

Breaking changes require:

- a new Specification revision;
- an Architecture Decision Record (ADR);
- a major semantic version increment.

---

# Engineering Process

The implementation lifecycle of WP-004 is:

Specification

↓

Architecture Review

↓

Approval

↓

Implementation

↓

Quality Gate

↓

Release

Implementations SHALL NOT precede the approved specification.

---

# Related Documents

- SIF Constitution
- SIF Architecture Specification (SAS)
- ADR-0003 — Explicit Dependency Injection Container
- ADR-0004 — Modular Engineering Specifications
- WP-000
- WP-002
- WP-003

---

# Licensing

This specification is distributed under the same license as the SIF Framework repository.