# WP-004 — Ubiquitous Language

**Specification:** SPEC-WP-004-DI-CONTAINER

**Document:** Ubiquitous Language

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This document establishes the canonical ubiquitous language of the Dependency Injection Container domain.

Its purpose is to ensure that every stakeholder—including architects, developers, reviewers, documentation authors, automated tools and the SIF Builder—uses the same terminology to describe the same concepts.

No synonym SHALL be introduced for an existing domain concept.

---

# 2. Principles

The ubiquitous language SHALL satisfy the following principles.

## UL-001 — One Concept, One Name

Every domain concept SHALL have exactly one canonical name.

---

## UL-002 — No Synonyms

Different words SHALL NOT describe the same concept.

For example:

❌ Service Name

❌ Key

❌ Token

✔ Service Identifier

---

## UL-003 — Technology Independence

Domain terminology SHALL remain independent from PHP implementation details.

Terms such as:

- array
- namespace
- reflection
- attribute
- closure

are implementation concepts and SHALL NOT appear in the ubiquitous language unless explicitly required.

---

## UL-004 — Stable Vocabulary

Domain terminology SHALL remain stable across framework versions.

Changing terminology requires:

- Specification revision;
- Architecture review;
- ADR approval.

---

# 3. Canonical Vocabulary

## Aggregate Root

Container

---

## Entities

Container

Binding

Alias

---

## Value Objects

ServiceIdentifier

BindingType

Lifetime

BindingMetadata

ResolutionContext

ResolutionPath

ResolutionPolicy

---

## Domain Services

ResolutionEngine

BindingValidator

AliasResolver

FactoryResolver

---

## External Actors

Application

Framework

Runtime

Bootstrap

Lifecycle

ServiceProvider

---

# 4. Relationships

The following relationships define the domain language.

Application owns a Container.

Container contains Bindings.

Bindings identify Services through Service Identifiers.

Aliases reference canonical Service Identifiers.

ResolutionEngine resolves Bindings.

Factories create Services.

ResolutionContext tracks one Resolution.

ResolutionPath records traversal history.

---

# 5. Canonical Verbs

The following verbs SHALL be used consistently.

## register

Create or replace a Binding.

---

## resolve

Obtain one Service instance.

---

## bind

Associate a Service Identifier with an implementation.

---

## alias

Associate one Service Identifier with another.

---

## instantiate

Create one object instance.

---

## validate

Verify domain rules.

---

## boot

Initialize runtime services.

---

## shutdown

Terminate runtime services.

---

## clear

Remove every registration.

---

## remove

Delete one registration.

---

## replace

Substitute an existing Binding.

---

# 6. Forbidden Vocabulary

The following terms SHALL NOT be used as synonyms.

| Forbidden | Canonical |
|-----------|-----------|
| Key | ServiceIdentifier |
| Name | ServiceIdentifier |
| Token | ServiceIdentifier |
| Locator | Container |
| Registry | Container |
| Object Manager | Container |
| Creator | Factory |
| Resolver | ResolutionEngine |
| Dependency Graph | ResolutionPath |

---

# 7. Naming Rules

Identifiers SHALL use PascalCase.

Methods SHALL use camelCase.

Documentation SHALL use the canonical names exactly as defined herein.

Abbreviations are prohibited unless explicitly standardized.

---

# 8. Domain Sentences

The following statements define the preferred language.

✔ "Application owns a Container."

✔ "Container registers a Binding."

✔ "ResolutionEngine resolves a Service."

✔ "Alias references a canonical Service Identifier."

✔ "Factory creates a Service."

The following statements are discouraged.

✘ "Registry stores objects."

✘ "Locator finds services."

✘ "Key resolves dependencies."

---

# 9. Consistency Rules

Every future Work Package SHALL reuse these terms whenever the concepts are identical.

New terminology SHALL only be introduced when representing genuinely new domain concepts.

---

# 10. Acceptance Criteria

This document is complete when:

- every core concept has one canonical name;
- forbidden synonyms are identified;
- canonical verbs are defined;
- domain relationships are consistently expressed;
- future specifications can reference this vocabulary without redefining it.

---

# End of Document