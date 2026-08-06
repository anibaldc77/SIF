---
id: EG-389
title: Contexto de seguridad y ciclo de vida del principal en sesión
summary: Define la propagación request-scoped del principal y su persistencia controlada y fail-closed en sesiones SIF.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-05
updated: 2026-08-05
work_package: WP-227
tags:
  - security
  - session
  - principal
  - context
  - authentication
depends_on:
  - EG-386
  - EG-388
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-389 — Contexto de seguridad y ciclo de vida del principal en sesión

## 1. Objetivo

Integrar el principal autenticado con el ciclo request-scoped de la aplicación y con sesiones SIF, sin convertir la sesión en autoridad de identidad ni introducir acoplamiento con persistencia o HTTP.

## 2. Componentes

- `SecurityContext` mantiene exactamente un principal activo y comienza en estado anónimo.
- `SessionPrincipalSnapshot` define una representación versionada, determinista y no sensible.
- `SessionAuthenticationManager` restaura, establece y elimina autenticación en una sesión existente.

## 3. Invariantes de seguridad

- La autenticación solicita regeneración del identificador de sesión para mitigar fijación de sesión.
- El cierre de sesión elimina la instantánea, vuelve el contexto a anónimo y solicita regeneración.
- Una instantánea desconocida o malformada falla cerrada, se elimina y nunca produce un principal parcial.
- La sesión almacena datos escalares controlados, no objetos de dominio ni credenciales.
- La restauración no convierte `SessionId` en identificador de identidad.

## 4. Compatibilidad

La integración usa exclusivamente la API pública de `SessionState`, por lo que no modifica contratos existentes de WP-226. La serialización versionada permite migraciones futuras sin reinterpretar silenciosamente formatos anteriores.

## 5. Fuera de alcance

Middleware HTTP, políticas de autorización, RBAC/ABAC, repositorios de usuarios, JWT, OAuth y persistencia stateless.
