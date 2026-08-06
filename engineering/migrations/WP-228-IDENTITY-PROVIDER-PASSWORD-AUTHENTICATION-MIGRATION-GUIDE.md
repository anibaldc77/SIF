---
id: WP-228-MIGRATION-GUIDE
title: WP-228 Identity Provider and Password Authentication Migration Guide
summary: Proporciona una adopción incremental de proveedores de identidad y autenticación por contraseña en aplicaciones SIF existentes.
status: Draft for Review
version: 1.0.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - security
  - identity-provider
  - password
  - migration
  - guide
depends_on:
  - EG-400
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 Identity Provider and Password Authentication Migration Guide

## 1. Mantener aplicaciones existentes sin cambios

WP-228 es opt-in. Una aplicación puede continuar utilizando la seguridad de WP-227 sin registrar autenticación por contraseña.

## 2. Adaptar la fuente de identidades

Implementar `IdentityProviderInterface` sobre la fuente institucional. El adaptador debe devolver `IdentityProviderResult` y no exponer entidades PDO o BaseModel al Core.

## 3. Adaptar el almacenamiento de hashes

Usar `PasswordHashProviderInterface` para lectura. Usar `PasswordHashStoreInterface` cuando también se habilite rehash automático. No almacenar contraseñas en texto claro.

## 4. Seleccionar una política nativa

Configurar `PasswordHashPolicy` con el algoritmo y costos apropiados para el entorno. Verificar disponibilidad y capacidad operativa antes de adoptar Argon2id.

## 5. Registrar el autenticador

Construir `PasswordAuthenticator` con proveedor de identidad, proveedor de hash, verificador y hash de respaldo válido. Registrar un único autenticador para el tipo de credencial `password`.

## 6. Incorporar protección de intentos

La implementación en memoria es solamente una referencia. En aplicaciones distribuidas implementar `PasswordAttemptProtectorInterface` sobre almacenamiento compartido y expiración atómica.

## 7. Habilitar rehash gradual

Configurar `PasswordRehashCoordinator` con un hasher de política vigente y un `PasswordHashStoreInterface`. El rehash ocurre únicamente después de autenticación exitosa.

## 8. Integrar HTTP y sesión

Usar `PasswordSessionLoginService`, `PasswordLoginEndpoint` y `PasswordLogoutEndpoint`. Registrar rutas explícitas, aplicar TLS, cookies seguras y la política CSRF definida por la aplicación.

## 9. Evitar exposición de secretos

No registrar requests completos, credenciales, contraseñas ni hashes. Revisar logs, auditoría, excepciones, trazas y herramientas de diagnóstico.

## 10. Validar operacionalmente

Ejecutar PHPUnit, PHPStan, Composer, SIF Builder, `sif-builder validate` y `git diff --check`. Probar login correcto, rechazo genérico, bloqueo, rehash y logout.
