---
id: EG-279
title: Logger Facade, Immutable LoggingPlan and Provider-Neutral Orchestration
summary: Defines the application-facing logger contract, immutable orchestration plan, structured result and provider-neutral logging flow.
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
  - facade
  - orchestration
  - logging-plan
depends_on:
  - EG-273
  - EG-274
  - EG-275
  - EG-276
  - EG-277
  - EG-278
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-279 — Logger Facade, Immutable LoggingPlan and Provider-Neutral Orchestration

- Work Package: WP-213
- Increment: I7
- Status: Implemented
- Date: 2026-07-28

## Objective

Provide the application-facing logging boundary without coupling the structured logging subsystem to a service container, framework bootstrap, storage provider or third-party logging library.

## Contracts

`LoggerInterface` exposes the generic `log()` operation and the eight canonical convenience methods. Every call returns a `LoggingResult`; callers can inspect the processed record and the isolated dispatch report without receiving handler failures as exceptions.

## Immutable plan

`LoggingPlan` composes exactly four runtime collaborators:

1. `LogRecordFactoryInterface`;
2. `LogRecordProcessorInterface`;
3. `LogRouter`;
4. the default `LogChannel`.

The plan is immutable. `withDefaultChannel()` and `withProcessor()` create new plans and preserve the original instance. When no processor is supplied, an empty deterministic pipeline is used.

## Orchestration

`StructuredLogger` performs one ordered flow:

1. select the explicit or default channel;
2. create and sanitize the record through the factory;
3. process the immutable record;
4. dispatch the processed record through the router;
5. return `LoggingResult`.

Factory and processor failures remain typed upstream failures. Handler failures remain isolated by `LogRouter` and are represented in the result.

## Provider neutrality

This increment does not depend on `Application`, `Bootstrap`, the service container, configuration loaders, files, streams, databases, networks or external logging packages. Integration with the runtime lifecycle is deferred to I8.
