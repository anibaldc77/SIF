---
id: EG-280
title: Runtime Integration, Service Provider and Completion
summary: Defines optional Structured Logging 2.0 runtime composition, provider registration, lifecycle records and compatibility closure.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-213
tags:
  - logging
  - runtime
  - service-provider
  - lifecycle
depends_on:
  - EG-273
  - EG-279
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# Runtime Integration, Service Provider and Completion

## 1. Purpose

This specification completes Structured Logging 2.0 by adding an optional composition-root integration with the Foundation runtime.

## 2. Compatibility rule

Runtime logging is opt-in. `Bootstrap` without a `LoggingPlan` SHALL preserve the existing provider graph, capabilities and application behavior. No global or static logger is introduced.

## 3. Application contract

`LoggingAwareApplicationInterface` exposes a nullable `LoggerInterface`. `MutableLoggingApplicationInterface` is the restricted publication boundary used by the runtime provider. The concrete `Application` stores the configured logger without resolving handlers or rebuilding the plan.

## 4. Bootstrap composition

`Bootstrap` MAY receive an immutable `LoggingPlan`. When present it SHALL:

1. create one `StructuredLogger`;
2. add one `RuntimeLoggingServiceProvider` before module integration;
3. publish the same logger instance in `Application`;
4. preserve all previous constructor defaults.

Adding the provider before module contributions ensures deterministic early registration and reverse-order late shutdown.

## 5. Runtime provider

`RuntimeLoggingServiceProvider` SHALL:

- publish the logger through the mutable application boundary;
- contribute the `logging` capability;
- emit bounded records for register, boot and shutdown phases;
- use only `LoggerInterface`;
- avoid handler, container, module and configuration discovery.

Lifecycle records complement but do not replace `BootResult`, diagnostics, events or audit records.

## 6. Failure semantics

Factory and processor failures retain the strict semantics established in I7. Handler failures remain isolated in `LoggingResult`. The provider does not catch strict orchestration failures because bootstrap composition errors must remain observable and authoritative.

## 7. Completion criteria

WP-213 is complete when:

- opt-in and no-plan compatibility are characterized;
- the logger is accessible through the application contract;
- capability publication is deterministic;
- lifecycle records are emitted in provider order;
- shutdown reaches logging after later providers;
- PHPUnit, PHPStan, Builder and repository checks pass.
