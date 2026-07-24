---
id: EG-208
title: Configuration Loaders
summary: Defines deterministic PHP and JSON configuration loading, source selection, multi-source precedence, and structural merge semantics for Foundation configuration.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - foundation
  - configuration
  - loaders
  - json
  - php
  - precedence
work_package: WP-203
depends_on:
  - EG-207
  - ADR-0005
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-208 — Configuration Loaders

## 1. Purpose

WP-203-I2 introduces file-based configuration sources without coupling source loading to the Runtime lifecycle.

The increment provides PHP and JSON loaders, explicit source-format selection, deterministic multi-source composition, and a documented precedence model.

## 2. Loader contract

`ConfigurationLoaderInterface` SHALL expose:

- `supports(string $source): bool`;
- `load(string $source): array`.

A loader SHALL return the complete configuration tree produced by one source.

A loader SHALL NOT mutate `ConfigurationRepository` directly.

## 3. Supported sources

`PhpConfigurationLoader` SHALL support files with the `.php` extension and SHALL require the file in an isolated static closure.

A PHP source SHALL return an array.

`JsonConfigurationLoader` SHALL support files with the `.json` extension and SHALL decode JSON using exception-based error handling.

A JSON source SHALL decode to an array. JSON scalar roots SHALL be rejected.

Extension comparison SHALL be case-insensitive.

## 4. Source failures

Missing files SHALL produce `ConfigurationSourceNotFoundException`.

Unreadable files SHALL produce `UnreadableConfigurationSourceException`.

Malformed JSON, PHP execution failures, and non-array source results SHALL produce `InvalidConfigurationSourceException` while preserving the original cause when available.

Sources without a registered supporting loader SHALL produce `UnsupportedConfigurationSourceException`.

## 5. Source selection

`ConfigurationFileLoader` SHALL resolve a source using the first registered loader whose `supports()` method returns `true`.

`withDefaultLoaders()` SHALL register PHP and JSON loaders.

Custom loader registration SHALL remain possible through constructor injection.

## 6. Precedence

`loadMany()` SHALL process sources in iteration order from lowest to highest precedence.

When two sources define the same value, the later source SHALL take precedence.

An empty source collection SHALL produce an empty configuration tree.

## 7. Merge semantics

Associative arrays SHALL be merged recursively when both existing and incoming values are associative arrays.

Lists SHALL be replaced as complete values by the later source and SHALL NOT be merged by numeric index.

Scalar values SHALL replace arrays, and arrays SHALL replace scalar values, when supplied by the later source.

An empty incoming array SHALL replace an existing associative map.

The merge operation SHALL NOT mutate any input array.

## 8. Compatibility

This increment SHALL NOT modify:

- `ConfigurationRepository`;
- `ConfigurationInterface`;
- `MutableConfigurationInterface`;
- `ApplicationInterface`;
- `Application`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- capability contracts.

Runtime integration and lifecycle freezing remain assigned to WP-203-I3.

Environment variables and `.env` processing remain outside this increment.

## 9. Acceptance criteria

- PHP array sources load successfully.
- JSON object and list sources load successfully.
- Invalid source contents fail explicitly.
- Unsupported formats fail explicitly.
- Later sources override earlier sources.
- Associative maps merge recursively.
- Lists replace earlier lists.
- Scalar/array collisions resolve by later-source replacement.
- Custom loader injection remains available.
- PHPStan level 8 reports no errors.
