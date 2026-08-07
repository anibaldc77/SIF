---
id: EG-429
title: Composición unificada RBAC/ABAC y adaptador al decision engine
summary: Define la composición de requisitos RBAC y ABAC y su adaptación al AuthorizationDecision existente de WP-227.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - rbac
  - abac
  - decision-engine
depends_on:
  - EG-428
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-429 — Composición RBAC/ABAC y adaptador de decisión

## Objetivo

Unificar RBAC y ABAC y conectarlos con el `AuthorizationDecision` existente de WP-227 sin redefinirlo.

## Composición

La composición default exige `RBAC && ABAC`.

Una falla en cualquiera de las dos dimensiones produce rechazo.

## Evaluación intermedia

`CompositeAuthorizationPolicyEvaluator` devuelve una evaluación interna que expresa satisfacción y motivo.

No reemplaza el contrato público de decisión.

## Adaptador

`ExistingAuthorizationDecisionAdapter` transforma la evaluación avanzada al `AuthorizationDecision` ya existente.

WP-227 mantiene el contrato público; WP-232 aporta composición y datos avanzados.

## Seguridad

El servicio no autentica, no muta sesión y no eleva `AuthenticationLevel`.

## Criterios de aceptación

- Composición RBAC + ABAC fail-closed.
- Evaluación intermedia.
- Uso del `AuthorizationDecision` existente.
- Sin segundo decision type público.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
