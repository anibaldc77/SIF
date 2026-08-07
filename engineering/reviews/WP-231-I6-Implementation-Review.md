---
id: WP-231-I6-REVIEW
title: WP-231 I6 Implementation Review
summary: Revisa la política de trusted-device y su interacción explícita y no mutante con MFA.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - trusted-device
  - policy
  - implementation-review
depends_on:
  - EG-422
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I6 Implementation Review

## Alcance revisado

Se incorpora un contrato de política para evaluar trusted-device grants sobre principals ya autenticados.

## Hallazgos

- La política default nunca autoriza bypass MFA implícito.
- La evaluación requiere identidad propietaria y grant utilizable.
- No existe mutación de principal, sesión ni SecurityContext.
- Una aplicación puede reemplazar la policy por una implementación explícita.
- El Core no interpreta trusted-device como nivel de autenticación.

## Riesgo evitado

Se evita que la sola presencia de una cookie o grant confiable degrade el requisito MFA de una operación sensible.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
