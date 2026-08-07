---
id: EG-412
title: Enrolamiento, activación y protección contra replay de factores TOTP
summary: Define el almacenamiento neutral del factor TOTP, su activación mediante prueba de posesión y la aceptación atómica de contadores estrictamente crecientes.
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
  - multi-factor-authentication
  - totp
  - enrollment
  - replay-protection
depends_on:
  - EG-411
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-412 — TOTP Factor Enrollment, Activation and Replay Protection

## Objetivo

Incorporar el ciclo de vida persistible del factor TOTP, separando enrolamiento, activación y uso operativo, con prevención de reutilización mediante contadores monotónicos.

## Decisiones normativas

- Un factor TOTP debe poseer identificador estable, identidad propietaria, secreto, parámetros, estado y timestamps UTC.
- Los estados mínimos son `pending`, `active` y `revoked`.
- El enrolamiento debe crear un factor `pending` y devolver el secreto sólo al límite que prepara el aprovisionamiento.
- El snapshot persistible o diagnóstico no debe incluir el secreto.
- La activación requiere una verificación TOTP exitosa y debe registrar el contador aceptado.
- Un factor activo sólo puede aceptar contadores estrictamente mayores al último contador persistido.
- La operación de aceptación del contador debe ser atómica en adaptadores persistentes.
- La verificación de un factor inexistente, pendiente o revocado debe fallar cerradamente.
- La revocación debe impedir usos posteriores.
- La implementación de referencia en memoria no representa almacenamiento distribuido.

## Compatibilidad

Los contratos no dependen de BaseModel, PDO, Redis, cifrado específico ni proveedor de secretos. Los adaptadores de producción deben cifrar el secreto en reposo según la política de la aplicación.

## Exclusiones I4

- Coordinación con `MultiFactorChallenge`.
- Códigos de recuperación.
- Elevación del principal autenticado.
- HTTP, CLI y Skeleton.
- Rotación automática de secretos.

## Criterios de aceptación I4

- Enrolamiento pending validado.
- Activación por prueba de posesión validada.
- Snapshots sin secretos.
- Replay del mismo contador rechazado.
- Contadores posteriores aceptados atómicamente.
- Revocación validada.
- PHPUnit, PHPStan y Builder sin diagnósticos.
