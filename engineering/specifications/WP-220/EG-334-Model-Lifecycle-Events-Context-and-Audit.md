---
id: EG-334
title: Model Lifecycle Events Context and Audit
summary: Defines explicit BaseModel lifecycle coordination with ordered hooks, synchronous events, execution context and audit records.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - foundation
  - basemodel
  - lifecycle
  - event
  - context
  - audit
depends_on:
  - EG-333
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-334 — Model Lifecycle, Events, Context and Audit

## 1. Purpose

WP-220 I6 adds an explicit lifecycle service around BaseModel persistence. Model objects remain storage-neutral and do not resolve event, context or audit services by themselves.

## 2. Lifecycle operations

The governed operations are create, update, delete, soft delete and restore. The coordinator SHALL determine create versus update from the persisted state before saving.

## 3. Ordering

For each operation the order SHALL be: before hooks, before event, persistence operation, after hooks, after event and audit emission. Exceptions from hooks or before-event listeners SHALL prevent persistence. Exceptions after persistence SHALL propagate and SHALL NOT imply that the storage mutation was rolled back.

## 4. Context

Every lifecycle invocation SHALL receive an explicit `ExecutionContextInterface`. Global context lookup and implicit thread-local state are prohibited.

## 5. Events

`ModelLifecycleEvent` SHALL include the model, operation, phase, context, before snapshot, after snapshot and computed changes. Event dispatch SHALL remain synchronous and use the existing event dispatcher contract.

## 6. Audit

Successful operations SHALL emit one audit record through `AuditServiceInterface`. Audit subjects SHALL identify the model type and, when available, its ordered identity. Payloads SHALL be normalized and SHALL NOT expose infrastructure credentials or SQL.

## 7. Hooks

Hooks SHALL implement an explicit interface and SHALL be registered in deterministic order. Reflection-based method discovery is prohibited.

## 8. Deferred scope

Relations and Unit of Work integration remain assigned to I7. Runtime composition and compatibility closure remain assigned to I8.
