---
id: EG-413
title: Satisfacción de desafíos MFA TOTP y elevación de autenticación
summary: Define la emisión, satisfacción atómica y elevación de nivel mediante factores TOTP activos para WP-230 I5.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - mfa
  - totp
  - step-up
depends_on:
  - EG-412
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-413 — Satisfacción de desafíos MFA TOTP y elevación de autenticación

## Objetivo

Integrar desafíos MFA con factores TOTP activos, preservando expiración, pertenencia de identidad, consumo único y protección contra replay.

## Invariantes

- Sólo se emiten desafíos cuando existe un factor TOTP activo y el nivel actual no satisface el requerido.
- La emisión revoca desafíos pendientes anteriores del mismo sujeto y propósito.
- La satisfacción requiere desafío pendiente, vigente, perteneciente a la identidad y de tipo TOTP.
- La verificación del factor consume atómicamente el contador TOTP.
- La transición del desafío a `satisfied` es atómica y no admite replay.
- El principal resultante conserva identidad y atributos y eleva su nivel al máximo entre el actual y el requerido.
- El método resultante es `mfa.totp` y no contiene secretos.

## Compatibilidad

El contrato base de almacenamiento se conserva. Las operaciones de ciclo de vida se publican en una interfaz especializada que lo extiende, evitando romper adaptadores de sólo lectura/escritura.

## Criterios de aceptación

- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
