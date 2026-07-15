# WP-004 — Dependency Injection Container

**Specification Summary**

Document ID: **SPEC-WP-004-DI-CONTAINER**

Version: **1.0.0**

Status: **Drafting**

---

# Table of Contents

## Chapter 1 — Foundation

Defines the purpose, scope, terminology, architectural principles and conformance rules of the Dependency Injection Container.

Document:

```
01-Foundation.md
```

---

## Chapter 2 — Architecture

Defines the architectural model of the Container and its relationship with the Runtime Foundation.

Topics include:

- Architectural Vision
- Core Principles
- Ownership Model
- Runtime Integration
- Service Provider Integration
- Architectural Invariants
- High-Level Architecture

Document:

```
02-Architecture.md
```

---

## Chapter 3 — Contracts

Defines every public contract exposed by WP-004.

Topics include:

- ContainerInterface
- FactoryInterface
- Binding
- BindingType
- ResolutionContext
- Public API
- Internal API
- Exception Hierarchy
- Compatibility Rules

Document:

```
03-Contracts.md
```

---

## Chapter 4 — Binding Model

Defines the registration model used by the Container.

Topics include:

- Canonical Identifier
- Bindings
- Singleton
- Transient
- Existing Instance
- Factory Binding
- Alias
- Binding Metadata
- Validation Rules
- Lifetime
- Binding State Machine

Document:

```
04-Binding-Model.md
```

---

## Chapter 5 — Resolution Engine

Defines the complete dependency resolution algorithm.

Topics include:

- Resolution Pipeline
- Alias Resolution
- Circular Dependency Detection
- Factory Execution
- Shared Service Creation
- Instance Resolution
- Error Handling
- Diagnostics

Document:

```
05-Resolution-Engine.md
```

---

## Chapter 6 — Runtime Integration

Defines the integration between the Dependency Injection Container and Runtime Foundation.

Topics include:

- Framework
- Runtime
- Bootstrap
- Lifecycle
- Application
- Service Providers
- Container Ownership
- Lifetime

Document:

```
06-Runtime-Integration.md
```

---

## Chapter 7 — Testing and Quality

Defines every quality requirement of WP-004.

Topics include:

- PHPUnit
- PHPStan
- PHP-CS-Fixer
- Composer
- Metadata
- Documentation
- Examples
- Quality Gates
- Acceptance Criteria

Document:

```
07-Testing-and-Quality.md
```

---

## Chapter 8 — Product Completion

Defines every deliverable required before WP-004 can be considered complete.

Topics include:

- README
- CHANGELOG
- component.json
- component.lock
- Examples
- Diagrams
- Implementation Report
- Release Checklist

Document:

```
08-Product-Completion.md
```

---

# Appendix

Additional normative information.

```
appendix/
```

Contains:

- glossary.md
- terminology.md
- compatibility.md

---

# Examples

Complete implementation examples.

```
examples/
```

Contains:

- basic-registration.md
- singleton.md
- factories.md
- aliases.md

---

# Diagrams

Official PlantUML diagrams.

```
diagrams/
```

Contains:

- container-class.puml
- resolution-sequence.puml
- binding-state.puml
- runtime-integration.puml
- service-provider-flow.puml
- exception-hierarchy.puml

---

# Architecture Decision Records

Related ADRs.

```
engineering/adr/
```

Required:

- ADR-0003 — Explicit Dependency Injection Container
- ADR-0004 — Modular Engineering Specifications

---

# Review Documents

Implementation reports are maintained under:

```
engineering/reviews/
```

The implementation of every phase SHALL reference the corresponding chapter of this specification.

---

# Navigation Rules

This document SHALL be treated as the official entry point of SPEC-WP-004-DI-CONTAINER.

Every chapter is normative unless explicitly marked as informative.

The specification SHALL be versioned as a whole while individual chapters MAY evolve through independent commits during development.

---

# Specification Status

| Chapter | Status |
|----------|--------|
| 01 Foundation | Approved |
| 02 Architecture | Draft |
| 03 Contracts | Draft |
| 04 Binding Model | Draft |
| 05 Resolution Engine | Draft |
| 06 Runtime Integration | Draft |
| 07 Testing and Quality | Draft |
| 08 Product Completion | Draft |

---

End of Summary