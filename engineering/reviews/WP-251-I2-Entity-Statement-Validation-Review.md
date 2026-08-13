---
id: WP-251-I2-REVIEW
title: WP-251 I2 Entity Statement Validation Review
summary: Revisa reglas semánticas y temporales para OpenID Federation Entity Configurations y Subordinate Statements.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - validation
  - architecture-review
depends_on:
  - EG-578
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-251 I2 Entity Statement Validation Review

## Alcance revisado

I2 incorpora validation context, contracts de validation y validators por tipo de Entity Statement.

## Hallazgos

- Entity Configuration y Subordinate Statement tienen policies distintas.
- `authority_hints` se restringe a Entity Configuration.
- `metadata_policy` se restringe a Subordinate Statement.
- Vigencia temporal e identidad esperada se validan explícitamente.
- Verificación criptográfica continúa desacoplada.

## Compatibilidad

I2 no modifica I1 ni WP-250.

## Decisión

Apto para I3 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
