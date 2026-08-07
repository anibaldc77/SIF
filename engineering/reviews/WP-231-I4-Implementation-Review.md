---
id: WP-231-I4-REVIEW
title: WP-231 I4 Implementation Review
summary: Revisa el ciclo de vida y revocación de concesiones de dispositivos confiables.
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
  - revocation
  - implementation-review
depends_on:
  - EG-420
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I4 Implementation Review

## Alcance revisado

Se incorporan generación criptográfica de identificadores, emisión, evaluación contextual, expiración y revocación de trusted-device grants.

## Hallazgos

- El grant continúa completamente separado de autenticación persistente.
- La confianza sólo es válida para la identidad propietaria.
- La expiración no se renueva automáticamente.
- La revocación individual y global son explícitas.
- El servicio no depende de `AuthenticatedPrincipal`, `SecurityContext`, Session ni MFA.

## Riesgo evitado

No se usa trusted-device como sustituto de una prueba de identidad. Esto evita que una concesión robada pueda crear por sí sola una sesión autenticada o elevar el nivel de autenticación.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
