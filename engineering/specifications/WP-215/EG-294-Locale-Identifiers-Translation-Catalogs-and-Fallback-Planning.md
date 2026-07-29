---
id: EG-294
title: Locale Identifiers Translation Catalogs and Fallback Planning
summary: Defines canonical locale identifiers, immutable translation catalogs, deterministic fallback chains and compiled translation plans.
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
  - locales
  - translations
  - fallback
depends_on:
  - EG-289
  - EG-290
  - EG-291
  - EG-292
  - EG-293
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-294 — Locale Identifiers, Translation Catalogs and Fallback Planning

## 1. Purpose

WP-215-I6 defines a storage-neutral localization model under the resource foundation. It SHALL canonicalize locale identifiers, represent translation catalogs immutably, derive deterministic fallback chains and compile immutable translation plans.

The increment SHALL NOT parse external catalog formats, read files, publish assets, mutate runtime state or install global translation helpers.

## 2. Locale identifiers

`LocaleIdentifier` SHALL accept portable language tags separated by hyphens or underscores and SHALL canonicalize them as follows:

- language subtags are lowercase;
- four-letter script subtags are title case;
- two-letter region subtags are uppercase;
- three-digit region subtags are preserved;
- remaining subtags are lowercase.

Locale comparison SHALL use the canonical case-sensitive value.

## 3. Fallback chains

The deterministic fallback builder SHALL append:

1. the requested locale;
2. each progressively less specific parent;
3. the optional default locale;
4. each parent of the default locale.

Duplicates SHALL be removed while preserving first occurrence. An explicit `LocaleFallbackChain` SHALL reject duplicate locales and empty chains.

## 4. Translation catalogs

A `TranslationCatalog` SHALL contain:

- one portable catalog identifier;
- one canonical locale;
- one existing `ResourceNamespace`;
- one non-empty flat map of translation keys to strings;
- one existing `ResourcePriority`.

Translation keys SHALL be bounded portable tokens. Messages SHALL remain opaque strings and SHALL not be executed or interpolated by this increment.

Catalog identity SHALL be:

```text
<namespace>:<locale>:<catalog identifier>
```

Duplicate catalog identities SHALL fail explicitly.

## 5. Deterministic translation planning

The planner SHALL resolve catalogs only for the requested namespace. Locale specificity SHALL take precedence over catalog priority. Within the same locale, catalogs SHALL be ordered by:

1. descending priority;
2. ascending input order.

For each translation key, the first matching message SHALL win. Resolution provenance SHALL retain the resolved locale, catalog identifier and original catalog order.

The compiled plan SHALL be immutable and SHALL expose messages and provenance without mutable registration operations.

## 6. Failure model

Validation and planning failures SHALL use typed exceptions for invalid locale identifiers, invalid translation keys, invalid catalogs, invalid fallback chains, duplicate catalog identity and missing translations.

## 7. Deferred scope

The following remain deferred:

- JSON, YAML, PHP or gettext parsing;
- filesystem catalog loading;
- ICU message formatting;
- pluralization and parameter interpolation;
- remote translation services;
- publication manifests;
- runtime service-provider integration.
