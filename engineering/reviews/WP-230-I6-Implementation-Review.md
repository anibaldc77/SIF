---
id: WP-230-I6-REVIEW
title: WP-230 I6 Implementation Review
summary: Revisa la incorporación de códigos de recuperación de un solo uso como factor MFA alternativo.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - mfa
  - recovery-code
  - implementation-review
depends_on:
  - EG-414
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-230 I6 Implementation Review

## Alcance revisado

Se incorpora generación, enrolamiento, almacenamiento por digest, consumo único y satisfacción MFA mediante códigos de recuperación.

## Hallazgos

- El código en claro queda limitado al lote entregado al usuario.
- El almacén recibe sólo digest y separa completamente este factor de TOTP.
- El consumo y la satisfacción del desafío son límites atómicos independientes.
- La evidencia elevada identifica el método `mfa.recovery_code`.
- Los snapshots y métodos de depuración permanecen redactados.

## Riesgos

Las implementaciones persistentes deben garantizar consumo atómico del digest. Si el consumo del código tiene éxito y la satisfacción del desafío falla, el código queda consumido de manera fail-closed.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
