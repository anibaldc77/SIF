---
id: WP-238-I2-REVIEW
title: WP-238 I2 Implementation Review
summary: Revisa catálogo de entitlements, asignaciones efectivas y modelo de campañas de revisión.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - entitlement
  - campaign
  - implementation-review
depends_on:
  - EG-474
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-238 I2 Implementation Review

## Alcance revisado

Se incorpora:

- resolución de assignments efectivos;
- campaign id/status/scope;
- campaña temporal;
- repository contract.

## Hallazgos

- Assignments expirados quedan excluidos.
- Entitlements desconocidos no se inventan.
- Campaign status y período son independientes y ambos gobiernan actividad.
- Scope permite subjects y entitlements.
- Resolver no ejecuta authorization ni provisioning.
- Persistencia queda detrás de contrato.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
