---
id: EG-414
title: Códigos de recuperación y satisfacción MFA alternativa
summary: Define generación, almacenamiento de digest, consumo único y elevación de autenticación mediante códigos de recuperación para WP-230 I6.
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
  - recovery-code
  - step-up
depends_on:
  - EG-413
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-414 — Códigos de recuperación y satisfacción MFA alternativa

## Objetivo

Incorporar códigos de recuperación de un solo uso como factor alternativo, sin mezclar su almacenamiento con TOTP y preservando el ciclo de desafío MFA.

## Invariantes

- Los códigos se generan con entropía criptográfica y sólo se entregan una vez.
- El almacenamiento conserva exclusivamente digest SHA-256.
- Reemplazar un lote invalida el lote anterior.
- El consumo es atómico y de un solo uso.
- Un desafío de código de recuperación no puede satisfacerse con otro tipo de factor.
- La identidad, vigencia y estado pendiente se validan antes del consumo.
- La elevación conserva identidad y atributos y publica evidencia `mfa.recovery_code`.
- Los snapshots no contienen código ni digest reutilizable.

## Compatibilidad

TOTP permanece sin cambios. Los códigos de recuperación usan contratos y almacenamiento independientes y comparten únicamente el ciclo de desafío MFA.

## Criterios de aceptación

- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
