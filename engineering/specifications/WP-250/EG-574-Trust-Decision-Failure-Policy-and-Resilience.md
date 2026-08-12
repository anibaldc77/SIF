---
id: EG-574
title: Trust Decision Failure Policy and Resilience
summary: Define deterministic fail-closed credential trust decisions and controlled stale-evidence reuse when trust resolution is unavailable.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - failure-policy
  - resilience
depends_on:
  - EG-573
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-574 — Trust Decision, Failure Policy and Resilience

## Objetivo

Definir comportamiento determinista cuando la resolución live de credential trust no se encuentra disponible.

## Failure Modes

`CredentialTrustResolutionFailureMode` distingue:

- fail closed;
- allow usable stale.

Fail-closed es el default.

## Trust Decision

`CredentialTrustDecision` conserva:

- resolution evidence;
- trusted state derivado del assessment;
- cache origin;
- stale flag;
- refresh recommendation.

## Default Failure Policy

`DefaultCredentialTrustResolutionFailurePolicy` únicamente reutiliza evidencia stale cuando:

- el modo lo permite explícitamente;
- existe un cache entry;
- el entry permanece dentro de `staleUntil`.

Fuera de esas condiciones se lanza `CredentialTrustResolutionUnavailableException`.

## Seguridad

Un fallo de infraestructura nunca genera synthetic trusted evidence.

La causa original del failure se preserva.

Los profiles high-assurance pueden prohibir completamente el uso de stale trust evidence.

## Neutralidad

Foundation no implementa retry loops, HTTP, Redis, Memcached, scheduler ni transport concreto.

## Compatibilidad

I6 agrega decision/failure-policy sin modificar contratos de I1-I5.
