---
id: EG-244
title: Mapper and Result-Set Contracts
summary: Defines explicit storage-neutral mapping, validated storage records, immutable result sets, typed page results, and deterministic mapping fixtures.
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
  - mapper
  - result-set
  - pagination
depends_on:
  - EG-243
  - EG-242
  - EG-241
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-244 — Mapper and Result-Set Contracts

## Purpose

This specification defines explicit, storage-neutral mapping and result representation.

The increment does not execute queries, generate SQL, open connections, define repositories, track object changes, or implement Unit of Work.

## StorageRecord

`StorageRecord` is an immutable map representing adapter-neutral stored values.

It accepts JSON-compatible values:

- null;
- booleans;
- integers;
- finite floats;
- strings;
- nested arrays.

It rejects:

- empty string keys;
- objects;
- resources;
- closures;
- non-finite floats;
- unsupported runtime values.

A storage record is not a database row abstraction and does not expose table, schema, column, or driver metadata.

## MapperInterface

`MapperInterface<T>` explicitly translates between:

- `StorageRecord`;
- application object `T`.

The contract contains:

- `hydrate`;
- `extract`.

A mapper:

- does not own connection lifecycle;
- does not execute queries;
- does not begin transactions;
- does not use reflection automatically;
- does not track dirty state.

## ResultSetInterface

`ResultSetInterface<T>` represents an ordered result collection.

It supports:

- `all`;
- `first`;
- `count`;
- iteration;
- emptiness checks.

The initial implementation is eager and immutable.

Streaming and cursor result sets may be introduced later through independent adapters or contracts.

## ResultSet

`ResultSet<T>` stores an ordered immutable list.

It normalizes numeric indexes while preserving item order and identity.

## MappedResultSetFactory

`MappedResultSetFactory<T>` hydrates an ordered list of `StorageRecord` values through an explicit mapper and returns `ResultSet<T>`.

It performs no I/O and has no storage dependency.

## PageResult

`PageResult<T>` combines:

- a typed result set;
- one-based page;
- positive per-page size;
- non-negative total item count.

It calculates:

- total pages;
- next-page availability;
- previous-page availability.

The item count cannot exceed the page size.

A total of zero produces zero total pages.

## Exclusions

This increment does not implement:

- SQL rows;
- database metadata;
- streaming cursors;
- lazy hydration;
- identity maps;
- relation mapping;
- repositories;
- query execution;
- Unit of Work;
- dirty tracking;
- persistence adapters.

## Acceptance criteria

- records are storage-neutral and validated;
- mapping is explicit and typed;
- object and record conversion are independently testable;
- result sets preserve order;
- empty result behavior is defined;
- page metadata is deterministic;
- item count cannot exceed page size;
- no SQL or driver dependency is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
