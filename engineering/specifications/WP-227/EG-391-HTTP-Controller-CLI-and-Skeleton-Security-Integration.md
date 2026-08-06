---
id: EG-391
title: Integración de seguridad con HTTP, Controller, CLI y Skeleton
summary: Define la integración desacoplada del principal y las decisiones de autorización con HTTP, controladores, CLI y Application Skeleton.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-227
tags:
  - security
  - http
  - controller
  - cli
  - skeleton
depends_on:
  - EG-385
  - EG-389
  - EG-390
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-391 — HTTP, Controller, CLI and Skeleton Security Integration

## Objetivo

Integrar el principal y las decisiones de autorización con las superficies públicas del framework sin introducir proveedores concretos de identidad.

## Invariantes

- El principal se transporta como atributo request-scoped.
- Una identidad anónima produce 401 y un desafío explícito cuando corresponda.
- Una identidad autenticada sin autorización produce 403.
- Las respuestas no exponen credenciales, tokens ni detalles internos de políticas.
- La metadata declarativa expresa acción y recurso, no roles persistidos.
- CLI realiza inspección segura y no autentica usuarios.
