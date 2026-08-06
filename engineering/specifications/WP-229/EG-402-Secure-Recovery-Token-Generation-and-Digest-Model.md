---
id: EG-402
title: Generación segura de tokens y modelo de digest para recuperación
summary: Define tokens opacos criptográficamente seguros, digest SHA-256 persistible y comparación controlada sin exposición del secreto.
status: Draft for Review
version: 0.1.0
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
  - token
  - digest
depends_on:
  - EG-401
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-402 — Secure Recovery Token Generation and Digest Model

## Objetivo

Definir el material secreto de los desafíos de recuperación y verificación, garantizando entropía suficiente, transporte URL-safe, persistencia exclusiva de material derivado y comparación resistente a diferencias triviales de tiempo.

## Decisiones normativas

- Los tokens se generan exclusivamente mediante `random_bytes()`.
- La política predeterminada utiliza 32 bytes de entropía y admite entre 32 y 128 bytes.
- El transporte utiliza Base64 URL-safe sin padding.
- El token en claro es un objeto sensible, redactado, no clonable y no serializable.
- El token sólo se expone mediante un callback acotado.
- La persistencia usa un digest SHA-256 hexadecimal de 64 caracteres.
- La comparación entre digest se realiza mediante `hash_equals()`.
- Los snapshots, logs, excepciones y metadatos nunca incluyen el token en claro.
- I2 no almacena, consume ni revoca desafíos; esos ciclos pertenecen a I3.

## Compatibilidad

La incorporación es aditiva y no modifica los contratos de WP-226, WP-227, WP-228 ni la arquitectura I1 de WP-229.

## Criterios de aceptación

- Tokens distintos y URL-safe.
- Entropía mínima de 256 bits.
- Redacción y prohibición de serialización/clonado.
- Digest determinista y persistible.
- Comparación segura y rechazo fail-closed de formatos inválidos.
- PHPUnit, PHPStan y Builder sin diagnósticos.
