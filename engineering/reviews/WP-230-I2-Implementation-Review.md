---
id: WP-230-I2-REVIEW
title: Revisión de implementación WP-230 I2
summary: Revisa el modelo sensible de semillas y códigos TOTP, parámetros interoperables y contratos neutrales de generación y verificación.
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
  - multi-factor-authentication
  - totp
  - review
depends_on:
  - EG-410
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-230 I2 — Implementation Review

## Alcance revisado

I2 incorpora los objetos sensibles y contratos necesarios para TOTP. No implementa todavía generación criptográfica, Base32, HMAC, enrolamiento, almacenamiento ni coordinación MFA.

## Decisiones confirmadas

- Las semillas se normalizan como Base32 sin padding y nunca se representan en texto claro.
- Los códigos se modelan como cadenas para preservar ceros iniciales.
- `SensitiveParameter`, redacción, prohibición de clonación y prohibición de serialización reducen exposición accidental.
- Los parámetros de algoritmo, dígitos, período y ventanas son explícitos e inmutables.
- Las ventanas pasada y futura se modelan por separado para evitar una tolerancia implícita y simétrica.
- El resultado de verificación no transporta secretos y sólo conserva el contador coincidente cuando hay éxito.
- Los contratos permanecen neutrales respecto de almacenamiento y bibliotecas TOTP.

## Riesgos y observaciones

PHP no puede garantizar el borrado físico de cadenas sensibles en memoria. El diseño limita su alcance y evita copias accidentales, pero los adaptadores deberán seguir evitando almacenamiento prolongado y trazas.

La compatibilidad con SHA-1 se mantiene por interoperabilidad RFC 6238; las políticas de aplicación podrán preferir SHA-256 o SHA-512 cuando sus autenticadores lo soporten.

## Resultado

La implementación es aditiva y prepara I3 para adaptadores nativos de generación, Base32 y verificación sin modificar la arquitectura pública de I1.
