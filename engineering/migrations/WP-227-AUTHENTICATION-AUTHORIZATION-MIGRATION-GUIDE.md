---
id: WP-227-MIGRATION-GUIDE
title: WP-227 Authentication and Authorization Migration Guide
summary: Proporciona una adopción incremental de identidad, autenticación, sesión y autorización en aplicaciones SIF existentes.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-06
updated: 2026-08-06
work_package: WP-227
tags:
  - security
  - authentication
  - authorization
  - migration
  - guide
depends_on:
  - EG-392
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-227 Authentication and Authorization Migration Guide

## 1. Conservar aplicaciones sin seguridad configurada

WP-227 es opt-in. Las aplicaciones existentes pueden continuar usando HTTP, Routing, Controller, Session y CLI sin registrar componentes de Security.

## 2. Modelar la identidad mediante contratos

Representar la identidad externa con `IdentityInterface`, `IdentityId` y `AuthenticatedPrincipal`. No extender el Core con entidades PDO o BaseModel.

## 3. Adaptar credenciales y autenticadores

Implementar `CredentialInterface` y `AuthenticatorInterface` en módulos o adaptadores. Registrar un único autenticador por tipo de credencial y no incluir secretos en resultados, errores o eventos.

## 4. Incorporar continuidad en sesión cuando corresponda

Usar `SessionAuthenticationManager` sobre `SessionState`. Regenerar el identificador al autenticar y cerrar sesión. No almacenar modelos, contraseñas ni tokens reutilizables.

## 5. Definir políticas de autorización

Implementar `AuthorizationPolicyInterface` para acciones y recursos. Las políticas deben producir decisiones explícitas y no depender de respuestas HTTP.

## 6. Integrar HTTP de manera opt-in

Agregar `SecurityContextMiddleware` y `AuthorizationMiddleware` únicamente en rutas o grupos protegidos. Mantener 401 para anonimato y 403 para falta de permiso.

## 7. Incorporar proveedores externos mediante adaptadores

JWT, OAuth 2.0, OpenID Connect, LDAP y Keycloak deben implementarse fuera del Core usando los contratos públicos de WP-227.

## 8. Validar operacionalmente

Ejecutar PHPUnit, PHPStan, Composer, SIF Builder, `sif-builder validate` y `git diff --check`. Verificar que logs, diagnósticos y respuestas no contengan credenciales o tokens.
