---
id: EG-365
title: Controller Resolver, Action Registry and Container Integration
summary: Specifies explicit controller-action registration, bounded reflection, container-backed controller and service resolution, handler registration and safe action result normalization.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-224
tags:
  - controller
  - action
  - registry
  - container
  - dispatch
  - specification
depends_on:
  - EG-361
  - EG-362
  - EG-363
  - EG-364
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Controller Resolver, Action Registry and Container Integration

WP-224 I5 defines the explicit runtime boundary that connects registered controller actions to the HTTP handler layer and to the existing SIF service container.

## Action definitions and registry

A controller action SHALL be represented by an immutable definition containing a stable action identifier, a controller-service identifier, a public non-static method name and an ordered list of argument definitions. The registry SHALL reject duplicate action identifiers and SHALL preserve deterministic ordering.

The registry SHALL NOT discover controllers through filesystem scanning, annotations, attributes or namespace conventions.

## Resolution

Controllers SHALL be resolved through `ControllerResolverInterface`. `ContainerControllerResolver` SHALL adapt `StringServiceContainerInterface` without exposing the entire container to controller actions.

Service-sourced action arguments SHALL use `ActionServiceResolverInterface`. The container adapter SHALL resolve only the explicit service identifier declared by the registered argument definition.

## Dispatch

Reflection MAY be used only after an action has been explicitly registered. Reflection SHALL verify that the selected method exists, is public, is non-static and has the same parameter count as the registered argument definition.

Argument resolution SHALL complete successfully before action invocation. Supported action results are `ResponseInterface` and explicit `ApiResult` values. Arbitrary values SHALL be rejected rather than serialized implicitly.

## HTTP integration

Each registered action MAY be exposed as a `RequestHandlerInterface` through an explicit registrar. The existing route handler identifier SHALL therefore reference the stable controller-action identifier without changing the router or middleware pipeline.

## Security and exclusions

I5 SHALL NOT perform automatic dependency injection based on parameter type names, mass assignment, controller discovery or request-scoped mutation of singleton services. Validation-to-dispatch integration and Problem Details mapping remain assigned to later increments.
