---
id: EG-262
title: Configuration Secret Classification Redaction and Safe Export
summary: Defines explicit secret classification, pluggable redaction policies, and structurally safe configuration export for Configuration 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
tags:
  - foundation
  - configuration
  - secrets
  - redaction
  - export
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
  - EG-259
  - EG-260
  - EG-261
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-262 — Configuration Secret Classification, Redaction, and Safe Export

## Purpose

This specification defines a source-neutral boundary for classifying configuration keys as secret and producing safe representations for diagnostics, logs, audit records, Builder artifacts, and operational inspection.

The configuration subsystem remains a consumer of secret values and SHALL NOT become a secret manager.

## Classification

Secret classification SHALL be performed through `ConfigurationSecretClassifierInterface` using a normalized `ConfigurationKey`.

Classification SHALL be explicit, deterministic, and independent from the stored value. The initial classification model SHALL distinguish:

- `public`;
- `secret`.

`ExplicitConfigurationSecretClassifier` SHALL support exact keys and complete subtrees identified by explicit prefixes. Prefix matching SHALL respect dot-segment boundaries. A prefix such as `database.auth` SHALL classify `database.auth.password` but SHALL NOT classify `database.author`.

`CompositeConfigurationSecretClassifier` SHALL classify a key as secret when any composed classifier does so.

## Redaction policy

Redaction SHALL be delegated to `ConfigurationRedactionPolicyInterface`. The policy receives the classified key and original value only for producing the replacement. The original value SHALL NOT be retained in export metadata.

The built-in `FixedMarkerConfigurationRedactionPolicy` SHALL replace every secret value with a non-empty fixed marker. Its default marker SHALL be `[REDACTED]`.

Redaction is not encryption and SHALL NOT be represented as protection suitable for later recovery.

## Safe export

`SafeConfigurationExporter` SHALL consume `ConfigurationInterface` and produce `SafeConfigurationExport` without mutating the source repository.

The export SHALL:

- preserve public scalar values;
- preserve public array structure and ordering;
- replace a classified scalar or entire classified subtree before descending into it;
- expose an ordered list of redacted key paths;
- omit original secret values from metadata;
- remain deterministic for the same repository, classifier, and policy.

The ordered redacted-key list SHALL follow repository traversal order.

## Safety boundary

Raw `all()` remains an internal configuration API and is not made safe by this increment. Code preparing configuration for display, logging, diagnostics, auditing, exception context, generated artifacts, or external serialization SHALL use safe export explicitly.

This increment does not attempt automatic secret detection by key name, regular expression, entropy, or value inspection. Such heuristics risk false negatives and SHALL NOT be the Core default.

## Compatibility

This increment is additive. It SHALL NOT modify repository read contracts, source adapters, composition, provenance, source diagnostics, schema validation, Runtime integration, or Container integration.

## Exclusions

This increment does not implement:

- encryption or secret storage;
- remote vault access;
- automatic key-name heuristics;
- secret rotation;
- access-control enforcement;
- snapshots or fingerprints;
- Runtime diagnostic wiring;
- automatic replacement of `ConfigurationInterface::all()`.

## Acceptance criteria

- exact keys can be classified as secret;
- complete subtrees can be classified without partial-segment matches;
- classifiers can be composed deterministically;
- safe export preserves public structure;
- secret scalars and subtrees are redacted;
- export metadata contains key paths but no original secret values;
- source repositories remain unchanged;
- custom fixed markers are supported;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
