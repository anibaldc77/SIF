---
id: EG-367
title: Skeleton Templates and Minimal API Example
summary: Specifies deterministic user-owned controller API templates and a minimal health endpoint example integrated with the application skeleton, route registry, action registry, handler registry and explicit controller services.
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
  - skeleton
  - api
  - template
  - example
  - specification
depends_on:
  - EG-361
  - EG-362
  - EG-363
  - EG-364
  - EG-365
  - EG-366
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Skeleton Templates and Minimal API Example

WP-224 I7 defines deterministic application-skeleton templates for an explicitly registered controller API and a minimal health endpoint example.

## Generated artifacts

The template factory SHALL generate the `app/Controllers` directory and the files `HealthController.php`, `ControllerServiceProvider.php`, `routes/api.php`, `config/controllers.php` and `tests/Feature/HealthApiTest.php`.

Every generated controller API artifact SHALL be declared by the project manifest as `user-owned` with overwrite policy `fail`. Existing application code SHALL NOT be replaced.

## Explicit composition

The generated example SHALL use a stable action identifier, an explicit controller service identifier, a registered route and an existing HTTP handler registration. Discovery by filesystem scanning, attributes, annotations or class-name inference is prohibited.

The health action SHALL return an `ApiResult`, leaving content negotiation and response construction to the controller API layer.

## Determinism and safety

Template rendering SHALL use UTF-8 text with LF line endings. Artifact and blueprint fingerprints SHALL be deterministic. Generation SHALL NOT execute Composer, start a server, run migrations, inspect globals or load generated application classes.

The example blueprint SHALL extend the canonical application blueprint rather than duplicate its bootstrap and configuration templates.
