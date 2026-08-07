---
id: WP-231-I5-REVIEW
title: WP-231 I5 Implementation Review
summary: Revisa restauración de sesión, resolución desacoplada de principal y evidencia de autenticación persistente.
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
  - persistent-authentication
  - session
  - implementation-review
depends_on:
  - EG-421
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I5 Implementation Review

## Alcance revisado

Se incorpora restauración de sesión desde una credencial persistente validada y rotada.

## Hallazgos

- La resolución del principal queda detrás de un contrato.
- La sesión usa el mecanismo canónico `SessionAuthenticationManager`.
- Una sesión existente no es sobrescrita.
- La identidad desaparecida provoca revocación fail-closed.
- El nivel restaurado es configuración explícita.
- No existe dependencia con trusted-device ni MFA.

## Riesgo evitado

No se replica el estado de sesión dentro de la credencial persistente. La cookie sólo permite reautenticar; la sesión sigue siendo una sesión normal gobernada por WP-226/WP-227.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
