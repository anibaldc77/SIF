---
id: EG-407
title: Integración HTTP, CLI y Skeleton para recuperación
summary: Define endpoints anti-enumeración, comandos operativos sanitizados y wiring opt-in para recuperación y verificación.
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
  - http
  - cli
depends_on:
  - EG-401
  - EG-402
  - EG-403
  - EG-404
  - EG-405
  - EG-406
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-407 — HTTP, CLI and Skeleton Recovery Integration

## Objetivo

Exponer los flujos de recuperación mediante componentes HTTP y CLI opt-in sin revelar existencia de cuentas, tokens, digest ni identificadores directos.

## Decisiones normativas

- Las solicitudes HTTP responden de forma genérica y usan `202 Accepted`.
- Las respuestas incluyen `Cache-Control: no-store` y `Pragma: no-cache`.
- Las confirmaciones no reflejan tokens ni información interna.
- Los comandos CLI de inspección y revocación requieren un identificador de desafío explícito.
- La inspección CLI sólo devuelve snapshots sanitizados.
- La revocación CLI es destructiva y debe ser autorizada por la aplicación.
- El contributor y los endpoints son opt-in; SIF no registra rutas ni comandos silenciosamente.
- CSRF, autorización administrativa, routing y entrega de tokens siguen bajo control de la aplicación.

## Criterios de aceptación

- Payloads JSON validados.
- Respuestas anti-enumeración.
- Inspección sin token ni digest.
- Revocación explícita.
- PHPUnit, PHPStan y Builder sin diagnósticos.
