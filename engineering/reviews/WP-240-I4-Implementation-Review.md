---
id: WP-240-I4-REVIEW
title: WP-240 I4 Implementation Review
summary: Revisa Rich Authorization Requests, authorization_details y fronteras de validación por tipo.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - rar
  - implementation-review
depends_on:
  - EG-492
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-240 I4 Implementation Review

## Alcance revisado

Se incorpora:

- authorization detail type;
- authorization detail;
- rich authorization request;
- validator contract actualizado;
- type policy;
- normalizer;
- excepción específica.

## Hallazgos

- RAR no introduce reglas específicas de negocio en Foundation.
- Los attributes permanecen genéricos.
- La validación específica se delega a policies.
- PAR y JAR pueden transportar RAR sin duplicar responsabilidades.
- HTTP y storage permanecen fuera de Foundation.

## Decisión

El incremento es apto para continuar a I5 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
