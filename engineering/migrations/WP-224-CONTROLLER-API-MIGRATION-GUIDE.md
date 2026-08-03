---
id: WP-224-CONTROLLER-API-MIGRATION-GUIDE
title: WP-224 Controller and API Migration Guide
summary: Guides applications from direct HTTP request handlers to explicitly registered controllers with governed arguments, validation, API responses and Problem Details.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-224
tags:
  - controller
  - api
  - migration
  - guide
depends_on:
  - EG-361
  - EG-362
  - EG-363
  - EG-364
  - EG-365
  - EG-366
  - EG-367
  - EG-368
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-224 Controller and API Migration Guide

## Starting point

An application may continue using existing `RequestHandlerInterface` implementations. WP-224 does not require an all-at-once migration.

## Recommended migration sequence

1. Register the controller object in the application Container under a stable string identifier.
2. Define a `ControllerActionDefinition` with an explicit action identifier, controller identifier, method and ordered argument definitions.
3. Register the definition in `ControllerActionRegistry`.
4. Compose `ContainerControllerResolver`, `ContainerActionServiceResolver` and `ActionArgumentResolver`.
5. Register the generated controller action handler in the existing HTTP `HandlerRegistry`.
6. Point the route handler identifier to the registered controller action identifier.
7. Add a `ValidationSchema` before action execution when the action accepts untrusted input.
8. Return `ApiResult` or `ResponseInterface`; do not emit headers or body from the controller.
9. Normalize failures through `ControllerExceptionHandler` and explicit `ExceptionMapperRegistry` mappings.

## Argument migration

Replace direct access to route attributes, query arrays, headers, cookies or parsed bodies with explicit `ActionArgumentDefinition` values. Do not merge input sources. Distinguish missing, null and invalid values.

## Validation migration

Move request-shape checks into validation rules. Keep domain invariants in the domain model. A failed `ValidationResult` must prevent controller invocation and should be represented as a safe `422` Problem Details response.

## API response migration

Replace controller-side `json_encode`, `header` and `echo` calls with `ApiResult`. Let `ApiResponseFactory` perform deterministic encoding and content negotiation. Preserve explicit status and headers where required.

## Error migration

Map expected application exceptions explicitly. Never expose the original throwable message by default. Use a generic internal-error Problem Details representation for unexpected failures and report the throwable through the existing Error Handling boundary.

## Skeleton adoption

New applications may use the WP-224 controller API example. Existing applications should copy concepts, not overwrite user-owned files. Generated controller artifacts use fail-on-conflict semantics.
