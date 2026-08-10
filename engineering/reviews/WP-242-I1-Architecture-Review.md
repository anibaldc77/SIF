---
id: WP-242-I1-REVIEW
title: WP-242 I1 Architecture Review
summary: Revisa la arquitectura inicial del perfil FAPI 2.0 y sus fronteras de conformidad.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - architecture-review
depends_on:
  - EG-505
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-242 I1 Architecture Review

## Alcance revisado

Se incorpora:

- FAPI capability model;
- security profile;
- conformance report;
- conformance evaluator;
- client policy;
- authorization server policy;
- resource server policy;
- roadmap I1-I8.

## Hallazgos

- FAPI se modela como profile layer.
- No se duplican protocolos ya implementados.
- Client, Authorization Server y Resource Server mantienen policies separadas.
- Message Signing queda preparado pero no implementado prematuramente.
- La infraestructura concreta permanece fuera de Foundation.

## Decisión

La arquitectura es apta para continuar a I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
