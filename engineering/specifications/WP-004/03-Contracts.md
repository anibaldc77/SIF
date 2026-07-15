# Chapter 3 — Contracts

**Specification:** SPEC-WP-004-DI-CONTAINER

**Chapter:** 03

**Title:** Public Contracts

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This chapter defines every public contract exposed by the Dependency Injection Container.

Public contracts constitute the official API of WP-004.

Beginning with the first stable SIF release, every contract defined herein SHALL become Compatibility Protected.

---

# 2. Design Principles

Every public contract SHALL satisfy the following principles.

## C-001

Minimal public surface.

---

## C-002

Strong typing.

---

## C-003

Deterministic behavior.

---

## C-004

Implementation independence.

---

## C-005

Forward compatibility.

---

# 3. Public Contracts

WP-004 defines the following public contracts.

```
ContainerInterface
FactoryInterface

Binding
BindingType

ResolutionContext
```

No additional public types SHALL be introduced without revising this specification.

---

# 4. ContainerInterface

## Responsibility

ContainerInterface represents the official dependency resolution service.

Every Container implementation SHALL implement this interface.

---

## Required Operations

### bind()

Registers a transient binding.

---

### singleton()

Registers a shared service.

---

### instance()

Registers an existing instance.

---

### factory()

Registers a factory binding.

---

### alias()

Creates an alias.

---

### has()

Determines whether a service identifier exists.

---

### get()

Resolves a service.

---

### remove()

Removes a registration.

---

### clear()

Removes every registration.

---

### registered()

Returns every registered identifier.

---

# 5. Behavioral Requirements

## bind()

MUST replace any existing binding using the same canonical identifier.

---

## singleton()

MUST always return the same instance.

---

## instance()

MUST preserve the supplied object instance.

---

## factory()

MUST invoke the registered factory according to its declared lifetime.

---

## alias()

MUST reference a canonical identifier.

Alias chains MAY exist.

Circular aliases MUST be rejected.

---

## get()

MUST either:

- return one object;

or

- throw a typed exception.

Returning null is prohibited.

---

## has()

MUST NOT trigger resolution.

---

## remove()

MUST remove aliases belonging to the removed canonical identifier.

---

## clear()

MUST completely reset the Container.

---

# 6. FactoryInterface

## Responsibility

FactoryInterface defines objects responsible for constructing services.

Factories SHALL contain no registration logic.

Factories SHALL NOT mutate Runtime.

Factories SHOULD remain stateless.

---

# 7. Binding

Binding represents one registration.

A Binding SHALL contain:

- canonical identifier
- lifetime
- implementation
- metadata

Bindings are immutable after registration.

Changes require replacement.

---

# 8. BindingType

BindingType SHALL be represented as an enum.

Supported values:

```
Transient

Singleton

Instance

Factory

Alias
```

No additional binding types exist in WP-004.

---

# 9. ResolutionContext

ResolutionContext is an internal diagnostic object.

Responsibilities:

- detect recursion
- detect circular dependencies
- retain resolution path

ResolutionContext SHALL NOT become part of the public resolution API.

---

# 10. Exception Hierarchy

WP-004 defines typed exceptions.

```
ContainerException

├── BindingException
├── ResolutionException
├── CircularDependencyException
├── UnknownServiceException
├── InvalidAliasException
├── FactoryException
└── InvalidBindingException
```

Every observable error SHALL produce one typed exception.

---

# 11. Identifier Rules

Service identifiers SHALL:

- be strings;
- be case-sensitive;
- be unique.

Identifiers SHALL NOT be automatically normalized.

---

# 12. Lifetime Rules

Supported lifetimes:

Singleton

Transient

Instance

Factory

Alias

No additional lifetime exists.

---

# 13. Mutability Rules

Container state is mutable.

Bindings are immutable.

Resolved singleton instances remain immutable from the Container perspective.

---

# 14. Compatibility Rules

Public interfaces SHALL NOT remove methods.

Public methods SHALL NOT change signatures.

Behavior SHALL remain deterministic.

Breaking changes require:

- Specification revision;
- ADR;
- major semantic version.

---

# 15. Visibility Rules

Only contracts described in this chapter constitute the public API.

Implementation classes remain internal.

Internal helper classes SHALL NOT be referenced by applications.

---

# 16. Extension Rules

Future Work Packages MAY extend functionality by:

- new interfaces;
- decorators;
- adapters.

Existing contracts SHALL remain valid.

---

# 17. Acceptance Criteria

This chapter is complete when:

- every public contract is identified;
- responsibilities are defined;
- behavioral rules are normative;
- exception hierarchy is complete;
- compatibility policy is established.

---

# End of Chapter 3