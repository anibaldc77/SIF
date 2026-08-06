---
id: EG-392
title: Cierre de producto, compatibilidad y hardening de seguridad
summary: Define los criterios finales de integración, compatibilidad, fail-closed y no exposición de datos para WP-227.
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
  - authentication
  - authorization
  - compatibility
  - hardening
depends_on:
  - EG-385
  - EG-386
  - EG-387
  - EG-388
  - EG-389
  - EG-390
  - EG-391
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-392 — Product Completion, Compatibility and Security Hardening

## Objetivo

Cerrar WP-227 como una base pública, modular y extensible de autenticación y autorización, verificando el recorrido completo entre identidad, sesión, autorización y HTTP.

## Invariantes de cierre

- El principal anónimo es un objeto válido y nunca se representa como `null`.
- La sesión conserva únicamente snapshots versionados y no sensibles.
- Todo snapshot inválido se elimina y restaura un contexto anónimo.
- La autenticación esperablemente rechazada se distingue de un fallo técnico.
- La autorización opera fail-closed cuando no existen políticas o una política falla.
- HTTP diferencia 401 de 403 y no expone credenciales, tokens ni detalles internos.
- Los contratos no dependen de BaseModel, PDO, JWT, OAuth, OIDC, LDAP ni Keycloak.
- Los componentes introducidos son opt-in y no alteran aplicaciones existentes.

## Compatibilidad pública

WP-227 agrega namespaces y contratos nuevos. No modifica firmas públicas previas de Runtime, Container, HTTP, Routing, Controller, Session, Persistence ni CLI. Los adaptadores futuros deberán depender de los contratos de Security y no invertir esta relación.

## Criterios de aceptación

- Suite completa PHPUnit sin regresiones.
- PHPStan limpio al nivel configurado por el repositorio.
- Composer válido en modo estricto.
- Builder y validación de repositorio sin diagnósticos.
- `git diff --check` limpio.
