---
id: EG-408
title: Cierre de producto de recuperación y verificación de cuentas
summary: Consolida compatibilidad, hardening, pruebas end-to-end y criterios de adopción de WP-229.
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - verification
  - product-completion
depends_on:
  - EG-401
  - EG-402
  - EG-403
  - EG-404
  - EG-405
  - EG-406
  - EG-407
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-408 — Account Recovery and Verification Product Completion

## Objetivo

Cerrar WP-229 como subsistema opt-in, storage-neutral y seguro para restablecimiento de contraseña y verificación de identidad.

## Decisiones normativas

- Los tokens en claro no se persisten ni aparecen en eventos, snapshots, CLI o respuestas HTTP.
- Los desafíos son de propósito estricto, expiran, se consumen una sola vez y pueden revocarse.
- Las solicitudes públicas preservan anti-enumeración.
- Los adaptadores persistentes deben garantizar transiciones atómicas de consumo y revocación.
- Los fallos posteriores a la validación consumen el desafío de forma fail-closed.
- HTTP, CLI, entrega y almacenamiento permanecen opt-in.
- Correo, SMS, BaseModel, PDO, Redis y servicios externos son adaptadores, no dependencias del Core.

## Criterios de aceptación

- Flujos end-to-end de reset y verificación.
- Rechazo de replay y uso cruzado de propósito.
- Snapshots y eventos sin secretos.
- Compatibilidad hacia atrás con WP-226, WP-227 y WP-228.
- PHPUnit, PHPStan, Composer y Builder sin diagnósticos.
