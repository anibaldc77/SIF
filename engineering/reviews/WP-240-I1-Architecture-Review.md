---
id: WP-240-I1-REVIEW
title: WP-240 I1 Architecture Review
summary: Revisa la arquitectura inicial de seguridad OAuth avanzada y sus capability contracts.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - advanced-security
  - architecture-review
depends_on:
  - EG-489
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-240 I1 Architecture Review

## Alcance revisado

Se incorpora:

- capability model;
- advanced security profile;
- explicit requirements;
- PAR boundary;
- proof-of-possession boundary;
- authorization details validation boundary;
- client lifecycle boundary.

## Hallazgos

- La nueva capa no duplica WP-239.
- Los protocolos avanzados permanecen detrás de contratos.
- No existe dependencia de storage, HTTP o proveedor concreto.
- DPoP/PAR/RAR quedan preparados para implementaciones posteriores.
- El roadmap I1-I8 queda explícito en EG-489.

## Decisión

La arquitectura es apta para continuar a I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
