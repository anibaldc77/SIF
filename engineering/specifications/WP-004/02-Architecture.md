# Chapter 2 — Architecture

**Specification:** SPEC-WP-004-DI-CONTAINER

**Chapter:** 02

**Title:** Architecture

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This chapter defines the architectural model of the SIF Dependency Injection Container.

It specifies ownership, lifecycle, component boundaries, architectural invariants and integration with Runtime Foundation.

Every implementation SHALL conform to this architecture.

---

# 2. Architectural Vision

The Dependency Injection Container is the official service composition mechanism of the SIF Framework.

It is intentionally small.

Its responsibility is limited to:

- service registration;
- service resolution;
- lifetime management.

It SHALL NOT become an application framework.

---

# 3. Architectural Position

The Container belongs to the Foundation layer.

```
                Framework
                     │
                     ▼
               Application
                     │
                     ▼
               Container
                     │
          ┌──────────┴──────────┐
          ▼                     ▼
    Service Providers      Application Services
```

The Container SHALL NOT depend on higher framework layers.

Higher layers MAY depend on the Container.

---

# 4. Ownership Model

## 4.1 Application Ownership

Every Application SHALL own exactly one Container.

```
Application
    │
    └──── owns ───► Container
```

Ownership SHALL be exclusive.

Container instances SHALL NOT be shared between Applications.

---

## 4.2 Framework Ownership

Framework owns Runtime.

Framework creates Application.

Application creates Container.

Framework SHALL NOT directly manipulate Container registrations.

---

## 4.3 Lifetime

Container lifetime equals Application lifetime.

Container creation:

```
Application Constructor
        │
        ▼
Container Created
```

Container destruction:

```
Application Shutdown
        │
        ▼
Container Released
```

---

# 5. Component Responsibilities

## Framework

Responsible for:

- Runtime creation
- Bootstrap
- Lifecycle orchestration

Responsible for NOT:

- service registration
- dependency resolution

---

## Application

Responsible for:

- owning the Container
- exposing the Container
- coordinating Service Providers

---

## Container

Responsible for:

- registrations
- bindings
- aliases
- factories
- instances
- singleton cache
- dependency resolution

---

## Service Providers

Responsible for:

- registering services
- configuring bindings

Responsible for NOT:

- resolving unrelated services during registration
- modifying Runtime state

---

# 6. Dependency Direction

Dependencies SHALL flow downward.

```
Framework
      │
      ▼
Application
      │
      ▼
Container
      │
      ▼
Bindings
```

Reverse dependencies are prohibited.

---

# 7. Service Resolution Model

Resolution SHALL always begin from a Service Identifier.

```
Identifier

↓

Alias Resolution

↓

Binding Lookup

↓

Instance?

↓

Factory?

↓

Class?

↓

Object
```

Every successful resolution SHALL produce exactly one object.

---

# 8. Container Visibility

The Container SHALL remain private to the owning Application.

External components SHALL access it only through the Application public API.

Global containers are prohibited.

Static containers are prohibited.

---

# 9. Registration Model

Registrations SHALL occur during the Service Provider Register phase.

```
Provider

↓

register()

↓

Container Bindings
```

Registration after boot MAY be allowed by implementation but SHALL preserve deterministic behavior.

---

# 10. Boot Integration

Container creation SHALL occur before provider registration.

```
Application

↓

Container

↓

Providers Register

↓

Providers Boot
```

Providers SHALL always observe a valid Container.

---

# 11. Shutdown Integration

Shutdown SHALL execute in reverse provider order.

Container destruction SHALL occur after every provider shutdown.

```
Shutdown Providers

↓

Container Released

↓

Application Destroyed
```

---

# 12. Circular Dependency Detection

The Container SHALL detect recursive resolution.

Implementations SHALL NOT rely on stack overflow.

Resolution SHALL terminate deterministically.

---

# 13. Architectural Boundaries

The Container SHALL NOT know:

- HTTP
- Console
- ORM
- Database
- Queue
- Scheduler

The Container MAY expose extension points used by future Work Packages.

---

# 14. Extension Model

WP-004 officially supports:

- Service Providers
- Factory bindings
- Alias bindings

Future Work Packages MAY extend through public APIs only.

Internal implementation details SHALL remain hidden.

---

# 15. Architectural Invariants

The following invariants SHALL never be violated.

## AI-001

Exactly one Container per Application.

---

## AI-002

Container lifetime equals Application lifetime.

---

## AI-003

Service resolution is deterministic.

---

## AI-004

Bindings are explicit.

---

## AI-005

No automatic discovery.

---

## AI-006

No reflection-based autowiring.

---

## AI-007

Public APIs remain compatibility protected.

---

## AI-008

Ownership remains unidirectional.

---

# 16. Error Model

Architecture violations SHALL produce typed exceptions.

Silent failures are prohibited.

Undefined behavior is prohibited.

---

# 17. Thread Safety

WP-004 makes no guarantees regarding concurrent access.

Future Work Packages MAY define concurrency requirements.

---

# 18. Security

The Container SHALL NOT expose:

- Runtime internals
- Provider implementation details
- Internal resolution stack

Diagnostics SHALL reveal only safe information.

---

# 19. Acceptance Criteria

This chapter is complete when:

- ownership is defined;
- lifecycle is defined;
- dependency direction is defined;
- architectural boundaries are defined;
- invariants are defined;
- extension model is defined.

---

# End of Chapter 2