---
id: WP-236-I6-REVIEW
title: WP-236 I6 Implementation Review
summary: Revisa trust de firma XML, política de documentos firmados y replay protection SAML.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-236
tags:
  - security
  - saml
  - signature
  - trust
  - replay
  - implementation-review
depends_on:
  - EG-462
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-236 I6 Implementation Review

## Alcance revisado

Se incorpora la frontera de confianza criptográfica y protección contra replay.

## Hallazgos

- Trust precede a la verificación de firma.
- Certificados embebidos no se consideran confiables automáticamente.
- Signature verification queda detrás de un contrato reemplazable.
- Política Response/Assertion firmada es explícita.
- Replay store permanece storage-neutral.
- No se introduce una implementación XMLDSig parcial o insegura dentro del core.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
