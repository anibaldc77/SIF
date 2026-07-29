---
id: EG-290
title: Immutable Resource Value Model
summary: Defines the immutable identifiers, namespaces, types, relative paths, priorities, descriptors and typed validation failures used by the SIF resource foundation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-215
tags:
  - foundation
  - resources
  - value-objects
  - validation
  - security
depends_on:
  - EG-289
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-290 — Immutable Resource Value Model

## 1. Purpose

WP-215-I2 establishes the immutable vocabulary used by later registration, resolution, filesystem and publication increments.

This increment introduces no registry, resolver, filesystem access, resource loading, publication or runtime integration.

## 2. Public model

The increment SHALL provide:

- `ResourceIdentifier`;
- `ResourceNamespace`;
- `ResourceType`;
- `ResourcePath`;
- `ResourcePriority`;
- `ResourceDescriptor`;
- typed validation exceptions rooted at `ResourceException`.

All value objects SHALL be immutable.

## 3. Resource identifiers

A resource identifier SHALL:

- be non-empty after trimming;
- be at most 128 bytes;
- begin with an ASCII alphanumeric character;
- contain only ASCII alphanumeric characters, dot, underscore, colon or hyphen;
- preserve case;
- compare case-sensitively.

Identifiers are opaque. This increment SHALL NOT assign hierarchical semantics to their punctuation.

## 4. Resource namespaces

A namespace follows the same portable syntax and case-sensitive behavior as an identifier.

The canonical global namespace is `global` and is available through `ResourceNamespace::global()`.

Namespaces SHALL NOT be interpreted as filesystem paths or PHP namespaces.

## 5. Resource types

The initial canonical vocabulary is:

```text
stylesheet
script
image
font
locale
translation
generic
```

The type model SHALL remain extensible. Extension values SHALL use lowercase portable tokens beginning with a letter and containing letters, digits, dot or hyphen.

Construction canonicalizes valid type values to lowercase.

## 6. Resource paths

`ResourcePath` models a portable relative resource path, not an authorized filesystem root.

It SHALL:

- normalize backslashes to forward slashes;
- reject empty paths;
- reject null bytes;
- reject Unix and Windows absolute paths;
- reject empty, current-directory and parent-directory segments;
- expose canonical segments and basename without reading the filesystem.

Canonical-root confinement and symbolic-link verification belong to WP-215-I4.

## 7. Priorities

`ResourcePriority` models deterministic ordering intent.

Higher integer values have higher precedence. The default is zero. Values are bounded between `-1000000` and `1000000` to prevent accidental use of sentinel-scale integers.

Registration order remains the final tie-breaker and belongs to WP-215-I3.

## 8. Descriptor

`ResourceDescriptor` SHALL contain:

- identifier;
- namespace;
- type;
- relative source path;
- priority;
- optional logical version;
- optional owner;
- scalar-or-null metadata.

The descriptor SHALL expose a stable portable summary and a qualified identifier formed as:

```text
<namespace>:<identifier>
```

The descriptor SHALL NOT imply that the source exists or is readable.

## 9. Metadata safety

Metadata keys SHALL be non-empty strings.

Metadata values SHALL be limited to:

```text
null
boolean
integer
float
string
```

Objects, resources and nested arrays are forbidden in this increment. Rich provider-specific state SHALL remain outside the portable descriptor.

## 10. Typed failures

Invalid construction SHALL fail immediately through a specific exception:

- `InvalidResourceIdentifierException`;
- `InvalidResourceNamespaceException`;
- `InvalidResourceTypeException`;
- `InvalidResourcePathException`;
- `InvalidResourcePriorityException`;
- `InvalidResourceDescriptorException`.

All exceptions extend `ResourceException`.

## 11. Compatibility

The increment is additive and SHALL NOT modify existing module, configuration, logging, error-handling, container or application behavior.

## 12. Deferred scope

The following remain deferred:

- mutable and compiled registries;
- duplicate and override policies;
- deterministic resolution requests and results;
- authorized filesystem roots;
- canonical path verification;
- module contribution providers;
- locales and fallback chains;
- publication plans and manifests;
- runtime service-provider integration.
