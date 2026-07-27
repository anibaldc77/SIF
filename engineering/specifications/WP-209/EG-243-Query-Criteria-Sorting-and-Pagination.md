---
id: EG-243
title: Query, Criteria, Sorting and Pagination Values
summary: Defines immutable, storage-neutral query intent through criteria, operators, sorting, pagination, projections, and composable query values without SQL generation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-209
tags:
  - foundation
  - persistence
  - query
  - criteria
  - pagination
depends_on:
  - EG-242
  - EG-241
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-243 — Query, Criteria, Sorting and Pagination Values

## Purpose

This specification defines immutable, storage-neutral values for representing query intent.

The increment does not execute queries, generate SQL, bind parameters, open connections, map results, or implement repositories.

## Query operators

`QueryOperator` defines the initial intent vocabulary:

- equality and inequality;
- greater-than and less-than comparisons;
- membership and exclusion;
- null and non-null tests;
- contains, starts-with, and ends-with string intent.

These values do not guarantee adapter support.

Adapters must later advertise supported operators through explicit capabilities.

## QueryCriterion

A criterion contains:

- non-empty field name;
- typed operator;
- operator-compatible value.

Null operators accept no value.

Membership operators require a non-empty array.

Other operators reject array values.

The criterion does not contain SQL fragments or placeholders.

## QueryCriteria

`QueryCriteria` is an ordered immutable list of criteria.

The initial model represents conjunction by ordered composition. Logical grouping and disjunction are intentionally deferred until a later need proves stable semantics.

## Sorting

`SortDirection` defines ascending and descending intent.

`SortField` contains a non-empty field and direction.

`SortOrder` preserves insertion order and allows immutable extension.

## Pagination

`Pagination` uses one-based pages and positive page sizes.

It exposes:

- page;
- per-page size;
- calculated offset;
- immutable next-page derivation.

This value represents offset-style pagination intent. Cursor pagination may be introduced by a future independent contract.

## Projection

`Projection` is an ordered unique list of requested fields.

An empty projection means the adapter-defined default representation.

Projection values do not imply SQL column names.

## Query

`Query` composes:

- criteria;
- sort order;
- optional pagination;
- projection.

Composition methods return new query instances.

The query remains independent of connection, transaction, mapper, repository, and storage technology.

## Explicit exclusions

This increment does not implement:

- SQL generation;
- query execution;
- OR groups;
- nested boolean expressions;
- joins;
- relation loading;
- locks;
- raw expressions;
- aggregate functions;
- cursor pagination;
- repositories;
- result mapping;
- adapter capabilities.

## Acceptance criteria

- query values are immutable;
- criteria validation is deterministic;
- sorting order is stable;
- pagination is one-based and validated;
- projections are ordered and deduplicated;
- query composition preserves the original instance;
- no SQL or driver types are introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
